<?php
/**
 * Porto AI — REST API (browser ↔ WordPress).
 *
 * Admin-only endpoints under `porto/v1/ai/*`. This is the SEPARATE contract from
 * the Worker (the browser never sees the Worker). Every route requires
 * `manage_options`; cookie-authenticated requests are additionally CSRF-protected
 * by WordPress core via the `X-WP-Nonce` (wp_rest) header the JS must send.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Porto_AI_REST_API' ) ) {

	class Porto_AI_REST_API {

		const NS = 'porto/v1';

		/** @var self|null */
		private static $instance = null;

		/** @return self */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/** Register all routes. Hooked on rest_api_init. */
		public function register_routes() {
			register_rest_route(
				self::NS,
				'/ai/demo-recommendation',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'demo_recommendation' ),
					'permission_callback' => array( $this, 'permission' ),
				)
			);

			register_rest_route(
				self::NS,
				'/ai/title-suggestions',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'title_suggestions' ),
					'permission_callback' => array( $this, 'permission' ),
				)
			);

			register_rest_route(
				self::NS,
				'/ai/logo-generation',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'logo_generation' ),
					'permission_callback' => array( $this, 'permission' ),
				)
			);

			register_rest_route(
				self::NS,
				'/ai/save-logo',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_logo' ),
					'permission_callback' => array( $this, 'permission' ),
				)
			);

			register_rest_route(
				self::NS,
				'/ai/site-title',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_site_title' ),
					'permission_callback' => array( $this, 'permission' ),
				)
			);

			register_rest_route(
				self::NS,
				'/ai/status',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'status' ),
					'permission_callback' => array( $this, 'permission' ),
				)
			);
		}

		/**
		 * Capability gate. (CSRF nonce is enforced by core for cookie auth.)
		 *
		 * @return true|WP_Error
		 */
		public function permission() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return new WP_Error( 'rest_forbidden', __( 'You are not allowed to use this.', 'porto' ), array( 'status' => 403 ) );
			}
			return true;
		}

		/**
		 * POST /ai/demo-recommendation
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function demo_recommendation( $request ) {
			$params       = (array) $request->get_json_params();
			$conversation = isset( $params['conversation'] ) ? $params['conversation'] : array();

			// Accept a bare `message` as shorthand for a single user turn.
			if ( empty( $conversation ) && ! empty( $params['message'] ) ) {
				$conversation = array(
					array( 'role' => 'user', 'content' => (string) $params['message'] ),
				);
			}

			$answers = isset( $params['answers'] ) ? $params['answers'] : array();
			$max     = isset( $params['max_recommendations'] ) ? (int) $params['max_recommendations'] : 3;

			$result = Porto_AI_Demo_Assistant::instance()->recommend( $conversation, $answers, $max );

			return $this->respond( $result );
		}

		/**
		 * POST /ai/title-suggestions
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function title_suggestions( $request ) {
			$params = (array) $request->get_json_params();
			$result = Porto_AI_Title_Generator::instance()->generate(
				array(
					'business_type' => isset( $params['business_type'] ) ? $params['business_type'] : '',
					'keywords'      => isset( $params['keywords'] ) ? $params['keywords'] : array(),
					'count'         => isset( $params['count'] ) ? $params['count'] : 10,
				)
			);

			return $this->respond( $result );
		}

		/**
		 * POST /ai/logo-generation
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function logo_generation( $request ) {
			$params = (array) $request->get_json_params();
			$result = Porto_AI_Logo_Generator::instance()->generate(
				array(
					'business_name' => isset( $params['business_name'] ) ? $params['business_name'] : '',
					'industry'      => isset( $params['industry'] ) ? $params['industry'] : '',
					'keywords'      => isset( $params['keywords'] ) ? $params['keywords'] : '',
					'style'         => isset( $params['style'] ) ? $params['style'] : '',
				)
			);

			return $this->respond( $result );
		}

		/**
		 * POST /ai/save-logo — sideload a generated logo into the media library.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function save_logo( $request ) {
			$params = (array) $request->get_json_params();
			$image  = isset( $params['image'] ) ? (string) $params['image'] : '';

			if ( '' === $image ) {
				return new WP_REST_Response(
					array( 'ok' => false, 'error' => array( 'code' => 'empty_image', 'message' => __( 'No image provided.', 'porto' ) ) ),
					400
				);
			}

			$result = Porto_AI_Logo_Generator::instance()->save_to_media( $image );
			if ( is_wp_error( $result ) ) {
				return new WP_REST_Response(
					array( 'ok' => false, 'error' => array( 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ) ),
					400
				);
			}

			return new WP_REST_Response( array( 'ok' => true, 'data' => $result ), 200 );
		}

		/**
		 * POST /ai/site-title — persist the chosen website title (blogname).
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function save_site_title( $request ) {
			$params = (array) $request->get_json_params();
			$title  = isset( $params['title'] ) ? sanitize_text_field( wp_unslash( $params['title'] ) ) : '';

			if ( '' === $title ) {
				return new WP_REST_Response(
					array( 'ok' => false, 'error' => array( 'code' => 'empty_title', 'message' => __( 'Title cannot be empty.', 'porto' ) ) ),
					400
				);
			}

			update_option( 'blogname', $title );

			return new WP_REST_Response( array( 'ok' => true, 'data' => array( 'title' => get_bloginfo( 'name' ) ) ), 200 );
		}

		/**
		 * GET /ai/status — lets the UI know if AI is usable before showing it.
		 *
		 * @return WP_REST_Response
		 */
		public function status() {
			$client = Porto_AI_Proxy_Client::instance();
			return new WP_REST_Response(
				array(
					'ok'   => true,
					'data' => array(
						'configured' => $client->is_configured(),
						'byok'       => $client->is_byok(),
					),
				),
				200
			);
		}

		/* ----------------------------------------------------------------- */

		/**
		 * Convert an orchestrator result (WP_Error | proxy error envelope | success
		 * array) into a uniform browser response.
		 *
		 * @param mixed $result Result.
		 * @return WP_REST_Response
		 */
		private function respond( $result ) {
			if ( is_wp_error( $result ) ) {
				return new WP_REST_Response(
					array(
						'ok'    => false,
						'error' => array(
							'code'    => $result->get_error_code(),
							'message' => $result->get_error_message(),
						),
					),
					400
				);
			}

			// Proxy error envelope (has explicit ok=false + status).
			if ( is_array( $result ) && isset( $result['ok'] ) && false === $result['ok'] ) {
				$error = isset( $result['error'] ) ? $result['error'] : array( 'code' => 'error', 'message' => __( 'AI request failed.', 'porto' ) );
				$body  = array( 'ok' => false, 'error' => $error );
				if ( ! empty( $result['quota'] ) ) {
					$body['quota'] = $result['quota'];
				}
				return new WP_REST_Response( $body, $this->client_status( isset( $result['status'] ) ? (int) $result['status'] : 502 ) );
			}

			// Success: a shaped data array.
			return new WP_REST_Response( array( 'ok' => true, 'data' => $result ), 200 );
		}

		/**
		 * Clamp upstream status to a sane browser-facing status.
		 *
		 * @param int $status Status.
		 * @return int
		 */
		private function client_status( $status ) {
			$allowed = array( 400, 401, 402, 403, 422, 429, 500, 502, 503, 504 );
			return in_array( $status, $allowed, true ) ? $status : 502;
		}
	}
}