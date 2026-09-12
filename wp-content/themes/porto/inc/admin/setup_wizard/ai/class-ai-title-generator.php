<?php
/**
 * Porto AI — Website title generator orchestrator.
 *
 * Thin wrapper: sanitize inputs → call the Worker → sanitize the returned
 * titles. Used by the "Generate" popup beside the logo/title field.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Porto_AI_Title_Generator' ) ) {

	class Porto_AI_Title_Generator {

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
		 * @param array $args { business_type, keywords[], count }. A legacy
		 *                     `industry` arg, if present, is folded into keywords.
		 * @return array|WP_Error|array Shaped result { titles[] }, WP_Error, or proxy error envelope.
		 */
		public function generate( $args ) {
			$args = is_array( $args ) ? $args : array();

			// keywords now carries industries + descriptive terms in one list.
			$raw_inputs = array();
			if ( isset( $args['keywords'] ) ) {
				$raw_inputs = array_merge( $raw_inputs, is_array( $args['keywords'] ) ? $args['keywords'] : preg_split( '/[,\n]+/', (string) $args['keywords'] ) );
			}
			if ( isset( $args['industry'] ) ) { // legacy / merged input
				$raw_inputs = array_merge( $raw_inputs, is_array( $args['industry'] ) ? $args['industry'] : preg_split( '/[,\n]+/', (string) $args['industry'] ) );
			}

			$keywords = array();
			foreach ( $raw_inputs as $k ) {
				$k = sanitize_text_field( (string) $k );
				if ( '' !== $k ) {
					$keywords[] = $k;
				}
			}

			$payload = array(
				'locale'        => get_locale(),
				'business_type' => sanitize_text_field( isset( $args['business_type'] ) ? $args['business_type'] : '' ),
				'keywords'      => array_slice( array_values( array_unique( $keywords ) ), 0, 12 ),
				'count'         => max( 1, min( 20, (int) ( isset( $args['count'] ) ? $args['count'] : 10 ) ) ),
			);

			if ( '' === $payload['business_type'] && empty( $payload['keywords'] ) ) {
				return new WP_Error( 'empty_request', __( 'Add a business type or a few keywords first.', 'porto' ) );
			}

			$res = Porto_AI_Proxy_Client::instance()->titles( $payload );
			if ( empty( $res['ok'] ) ) {
				return $res;
			}

			$titles = array();
			$raw    = isset( $res['data']['titles'] ) && is_array( $res['data']['titles'] ) ? $res['data']['titles'] : array();
			foreach ( $raw as $t ) {
				$t = sanitize_text_field( wp_strip_all_tags( (string) $t ) );
				if ( '' !== $t ) {
					$titles[] = $t;
				}
			}

			return array( 'titles' => array_slice( array_values( array_unique( $titles ) ), 0, $payload['count'] ) );
		}
	}
}