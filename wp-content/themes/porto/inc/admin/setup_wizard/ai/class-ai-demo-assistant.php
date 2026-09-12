<?php
/**
 * Porto AI — Demo recommendation orchestrator.
 *
 * Ties the pieces together: pre-filter candidates → call the Worker → validate
 * every returned id against the catalog (SECURITY: never trust model output) →
 * hydrate each pick with the card fields the UI needs.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Porto_AI_Demo_Assistant' ) ) {

	class Porto_AI_Demo_Assistant {

		const MAX_TURNS = 12;

		/** @var self|null */
		private static $instance = null;

		/** @return self */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * @param array $conversation [{ role, content }] turns.
		 * @param array $answers      Structured follow-up answers.
		 * @param int   $max          Max recommendations.
		 * @return array|WP_Error|array Normalized proxy error envelope on upstream failure,
		 *                              WP_Error on local failure, or the shaped result array.
		 */
		public function recommend( $conversation, $answers = array(), $max = 3 ) {
			$conversation = $this->sanitize_conversation( $conversation );
			$answers      = $this->sanitize_answers( $answers );
			$message      = $this->last_user_message( $conversation );

			if ( '' === $message && empty( $answers ) ) {
				return new WP_Error( 'empty_request', __( 'Tell me what kind of website you want to build.', 'porto' ) );
			}

			$candidates = Porto_AI_Prompt_Builder::instance()->build_candidates( $message, $answers, 15 );
			if ( empty( $candidates ) ) {
				return new WP_Error( 'no_candidates', __( 'No demos matched that. Try describing your site a different way.', 'porto' ) );
			}

			$payload = array(
				'locale'              => get_locale(),
				'conversation'        => $conversation,
				'answers'             => $answers,
				'candidates'          => $candidates,
				'max_recommendations' => max( 1, min( 10, (int) $max ) ),
			);

			$res = Porto_AI_Proxy_Client::instance()->recommend( $payload );
			if ( empty( $res['ok'] ) ) {
				return $res; // Pass the normalized error envelope up; REST maps status.
			}

			return $this->shape( $res['data'], (int) $payload['max_recommendations'] );
		}

		/* ----------------------------------------------------------------- */

		/**
		 * Validate + hydrate the model's response into UI-ready cards.
		 *
		 * @param array $data Model data { message, recommendations[], followup }.
		 * @param int   $max  Cap.
		 * @return array
		 */
		private function shape( $data, $max ) {
			$repo  = Porto_AI_Demo_Repository::instance();
			$cards = array();

			$recs = isset( $data['recommendations'] ) && is_array( $data['recommendations'] ) ? $data['recommendations'] : array();
			foreach ( $recs as $r ) {
				$id = isset( $r['id'] ) ? sanitize_text_field( $r['id'] ) : '';

				// SECURITY: only ids that exist in the real catalog survive.
				if ( ! $repo->is_valid_demo_id( $id ) ) {
					continue;
				}
				$demo = $repo->get( $id );

				$cards[] = array(
					'id'         => $id,
					'reason'     => isset( $r['reason'] ) ? wp_strip_all_tags( (string) $r['reason'] ) : '',
					'confidence' => isset( $r['confidence'] ) ? round( (float) $r['confidence'], 2 ) : null,
					'demo'       => array(
						'name'        => $demo['name'],
						'thumbnail'   => $demo['thumbnail'],
						'preview_url' => $demo['preview_url'],
						'builder'     => $demo['primary_builder'],
						'builders'    => $demo['builders'],
						'woocommerce' => (bool) $demo['woocommerce'],
						'is_new'      => (bool) $demo['is_new'],
					),
				);

				if ( count( $cards ) >= $max ) {
					break;
				}
			}

			return array(
				'message'         => isset( $data['message'] ) ? wp_strip_all_tags( (string) $data['message'] ) : '',
				'recommendations' => $cards,
				'followup'        => $this->sanitize_followup( isset( $data['followup'] ) ? $data['followup'] : null ),
			);
		}

		/**
		 * @param mixed $conversation Raw.
		 * @return array Sanitized, capped to the last MAX_TURNS turns.
		 */
		private function sanitize_conversation( $conversation ) {
			if ( ! is_array( $conversation ) ) {
				return array();
			}
			$out = array();
			foreach ( $conversation as $turn ) {
				if ( ! is_array( $turn ) || empty( $turn['content'] ) ) {
					continue;
				}
				$role = ( isset( $turn['role'] ) && 'assistant' === $turn['role'] ) ? 'assistant' : 'user';
				$out[] = array(
					'role'    => $role,
					'content' => sanitize_textarea_field( (string) $turn['content'] ),
				);
			}
			return array_slice( $out, -self::MAX_TURNS );
		}

		/**
		 * @param mixed $answers Raw.
		 * @return array Flat string map.
		 */
		private function sanitize_answers( $answers ) {
			if ( ! is_array( $answers ) ) {
				return array();
			}
			$out = array();
			foreach ( $answers as $k => $v ) {
				if ( is_scalar( $v ) ) {
					$out[ sanitize_key( $k ) ] = sanitize_text_field( (string) $v );
				}
			}
			return $out;
		}

		/**
		 * @param array $conversation Sanitized turns.
		 * @return string Last user message content.
		 */
		private function last_user_message( $conversation ) {
			for ( $i = count( $conversation ) - 1; $i >= 0; $i-- ) {
				if ( 'user' === $conversation[ $i ]['role'] ) {
					return $conversation[ $i ]['content'];
				}
			}
			return '';
		}

		/**
		 * @param mixed $followup Raw followup.
		 * @return array|null { question, options[] } or null.
		 */
		private function sanitize_followup( $followup ) {
			if ( ! is_array( $followup ) || empty( $followup['question'] ) ) {
				return null;
			}
			$options = array();
			if ( isset( $followup['options'] ) && is_array( $followup['options'] ) ) {
				foreach ( $followup['options'] as $o ) {
					if ( is_scalar( $o ) ) {
						$options[] = sanitize_text_field( (string) $o );
					}
				}
			}
			return array(
				'question' => sanitize_text_field( (string) $followup['question'] ),
				'options'  => array_slice( $options, 0, 6 ),
			);
		}
	}
}