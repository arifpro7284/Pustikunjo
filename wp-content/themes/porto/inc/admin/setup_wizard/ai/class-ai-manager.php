<?php
/**
 * Porto AI — Manager (bootstrap + wiring).
 *
 * Single entry point: registers the REST routes and exposes small config
 * helpers the UI layer (step 6) will use. Asset enqueueing and the Setup-Wizard
 * UI hooks are intentionally NOT here yet — they arrive with the front-end step.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Porto_AI_Manager' ) ) {

	class Porto_AI_Manager {

		/** @var self|null */
		private static $instance = null;

		/** @return self */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'rest_api_init', array( Porto_AI_REST_API::instance(), 'register_routes' ) );

			// Setup Wizard UI (enqueue + render). Self-gates to admin + wizard page.
			Porto_AI_UI::instance();
		}

		/**
		 * REST base URL for a given AI route, for localizing into JS.
		 *
		 * @param string $path E.g. 'demo-recommendation'.
		 * @return string
		 */
		public static function rest_url( $path = '' ) {
			return rest_url( trailingslashit( Porto_AI_REST_API::NS ) . 'ai/' . ltrim( $path, '/' ) );
		}

		/**
		 * Whether the AI feature should be offered in the UI.
		 *
		 * @return bool
		 */
		public static function is_enabled() {
			$enabled = Porto_AI_Proxy_Client::instance()->is_configured();
			return (bool) apply_filters( 'porto_ai_enabled', $enabled );
		}
	}
}