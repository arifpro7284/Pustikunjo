<?php
/**
 * Porto AI — Worker proxy client.
 *
 * The license check happens HERE, on the WordPress side, before any request is
 * sent: if the site is not a registered Porto install (ThemeForest purchase
 * code or Freemius license), the Worker is never called. The Worker does NOT
 * re-verify the license — it trusts authenticated Porto installs (a baked-in
 * app key lets it drop traffic that isn't a Porto install) and caps cost with
 * per-install rate limits. No purchase code / license key is ever sent off-site.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Porto_AI_Proxy_Client' ) ) {

	class Porto_AI_Proxy_Client {

		const CONTRACT     = '2';
		const HTTP_TIMEOUT = 25; // Slightly longer than the Worker's 20s upstream deadline.

		/**
		 * Production Worker URL, baked into the theme — ships to every customer so
		 * the feature works with zero configuration. Set to the deployed Worker
		 * URL to launch; leave empty to keep the feature hidden (is_configured()
		 * false). The PORTO_AI_WORKER_URL constant (wp-config) overrides this — for
		 * Porto developers' local/staging only, never something customers set.
		 */
		const DEFAULT_WORKER_URL = PORTO_API_URL . 'ai/demo-assistant/'; // e.g. 'https://ai.portotheme.com'

		/**
		 * Baked-in app identifier sent with every request. NOT a secret (it ships
		 * in the theme); it only lets the Worker drop traffic that isn't a Porto
		 * install. Override via the `porto_ai_app_key` filter.
		 */
		const APP_KEY = 'porto-ai';

		/** @var self|null */
		private static $instance = null;

		/** @return self */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/* ----------------------------------------------------------------- *
		 * Configuration.
		 * ----------------------------------------------------------------- */

		/**
		 * Worker base URL, no trailing slash. Override constant → baked-in default.
		 *
		 * @return string
		 */
		public function worker_url() {
			$url = defined( 'PORTO_AI_WORKER_URL' ) ? PORTO_AI_WORKER_URL : self::DEFAULT_WORKER_URL;
			$url = apply_filters( 'porto_ai_worker_url', $url );
			return untrailingslashit( trim( (string) $url ) );
		}

		/** @return string Customer-supplied OpenAI key (BYO-key mode), or ''. */
		public function byok_key() {
			global $porto_settings;
			$key = defined( 'PORTO_AI_OPENAI_KEY' ) ? PORTO_AI_OPENAI_KEY : ( empty( $porto_settings['ai-gpt-key'] ) ? '' : $porto_settings['ai-gpt-key'] );
			return trim( (string) apply_filters( 'porto_ai_openai_key', $key ) );
		}

		/** @return bool */
		public function is_byok() {
			return '' !== $this->byok_key();
		}

		/** @return bool Whether the AI service can be reached at all. */
		public function is_configured() {
			return '' !== $this->worker_url();
		}

		/**
		 * Whether this site holds a valid Porto license — checked LOCALLY, before
		 * any Worker call. The license (ThemeForest purchase code or PortoTheme.com
		 * license key) is validated by the Porto API; Porto()->is_registered()
		 * reflects the result.
		 *
		 * @return bool
		 */
		public function is_licensed() {
			if ( function_exists( 'Porto' ) ) {
				$porto = Porto();
				if ( is_object( $porto ) && method_exists( $porto, 'is_registered' ) ) {
					return (bool) $porto->is_registered();
				}
			}
			// Fallback when Porto()/admin isn't loaded: any stored license marker.
			return (bool) ( get_option( 'porto_registered' )
				|| get_option( 'envato_purchase_code_9207399' ) );
		}

		/* ----------------------------------------------------------------- *
		 * Public endpoint calls. Each returns the normalized envelope (below).
		 * ----------------------------------------------------------------- */

		/**
		 * @param array $payload Recommendation payload (without request_id).
		 * @return array Normalized envelope.
		 */
		public function recommend( $payload ) {
			return $this->call( '/v1/demo-recommendation', $payload );
		}

		/**
		 * @param array $payload Title payload (without request_id).
		 * @return array Normalized envelope.
		 */
		public function titles( $payload ) {
			return $this->call( '/v1/title-suggestions', $payload );
		}

		/**
		 * Logo generation. Image generation is slow, so a longer timeout is used.
		 *
		 * @param array $payload Logo payload (without request_id).
		 * @return array Normalized envelope.
		 */
		public function logos( $payload ) {
			return $this->call( '/v1/logo-generation', $payload, 60 );
		}

		/* ----------------------------------------------------------------- *
		 * Request pipeline.
		 * ----------------------------------------------------------------- */

		/**
		 * @param string $path    Worker path.
		 * @param array  $payload Body (request_id added if absent).
		 * @return array Normalized envelope.
		 */
		private function call( $path, $payload, $timeout = self::HTTP_TIMEOUT ) {
			if ( ! $this->is_configured() ) {
				return $this->err( 'not_configured', __( 'The AI service is not configured.', 'porto' ), 503 );
			}

			if ( empty( $payload['request_id'] ) ) {
				$payload['request_id'] = wp_generate_uuid4();
			}
			$headers = $this->base_headers( $payload['request_id'] );

			// BYO-key: the customer's own OpenAI key — no license required.
			if ( $this->is_byok() ) {
				$headers['Authorization'] = 'Bearer byok';
				$headers['X-OpenAI-Key']  = $this->byok_key();
				return $this->raw_post( $path, $payload, $headers, $timeout );
			}

			// License gate — verified HERE, before the request leaves the site.
			if ( ! $this->is_licensed() ) {
				return $this->err( 'license_required', __( 'Activate your Porto license to use the AI assistant.', 'porto' ), 401 );
			}

			// Shared-key (no BYO) licensed call: if the shared key hits an OpenAI
			// usage limit, point the user to their own key instead of leaking the
			// raw provider error.
			return $this->maybe_byok_hint( $this->raw_post( $path, $payload, $headers, $timeout ) );
		}

		/**
		 * When a SHARED-key call fails because the provider's usage cap was hit
		 * (rate limit / daily quota / billing limit), replace the raw error with a
		 * friendly prompt to add a personal OpenAI key in Theme Options. BYO-key
		 * users are never rewritten — their limit is their own key's, so they see
		 * the real error.
		 *
		 * @param array $result Normalized envelope from raw_post().
		 * @return array
		 */
		private function maybe_byok_hint( $result ) {
			if ( $this->is_byok() ) {
				return $result; // Customer's own key — show the real error.
			}
			if ( empty( $result ) || ! empty( $result['ok'] ) || empty( $result['error'] ) || ! is_array( $result['error'] ) ) {
				return $result;
			}

			$error = $result['error'];
			$code  = isset( $error['code'] ) ? (string) $error['code'] : '';
			$blob  = mb_strtolower( trim(
				( isset( $error['message'] ) ? $error['message'] : '' ) . ' ' .
				( isset( $error['detail'] ) ? $error['detail'] : '' )
			) );

			// Our Worker's own daily cap, or an OpenAI rate/quota/billing limit
			// surfaced as an upstream error.
			$is_limit = in_array( $code, array( 'quota_exceeded', 'rate_limited' ), true )
				|| ( in_array( $code, array( 'upstream_error', 'upstream_timeout' ), true )
					&& preg_match( '/rate limit|ratelimit|quota|insufficient_quota|billing|hard limit|exceeded|too many requests|\b429\b|\btpm\b|\brpm\b/i', $blob ) );

			if ( ! $is_limit ) {
				return $result;
			}

			$result['error'] = array(
				'code'         => 'byok_required',
				'message'      => __( 'The shared Porto AI service has reached its usage limit. Add your own OpenAI API key in Porto → Theme Options → AI Features to keep using AI features without limits.', 'porto' ),
				'action_url'   => $this->theme_options_url(),
				'action_label' => __( 'Open Theme Options', 'porto' ),
			);
			return $result;
		}

		/** @return string Admin URL of the Porto Theme Options page. */
		private function theme_options_url() {
			return admin_url( 'admin.php?page=porto_settings' );
		}

		/**
		 * @param string $request_id Request id.
		 * @return array
		 */
		private function base_headers( $request_id ) {
			return array(
				'Content-Type'        => 'application/json',
				'X-Porto-Version'     => defined( 'PORTO_VERSION' ) ? PORTO_VERSION : '',
				'X-Porto-AI-Contract' => self::CONTRACT,
				'X-Porto-App-Key'     => (string) apply_filters( 'porto_ai_app_key', self::APP_KEY ),
				'X-Porto-Install'     => $this->install_id(),
				'Idempotency-Key'     => $request_id,
			);
		}

		/**
		 * Stable per-site install id used for rate-limiting: a deterministic id
		 * derived from the site URL.
		 *
		 * @return string
		 */
		private function install_id() {
			return 'site_' . substr( md5( home_url( '/' ) ), 0, 16 );
		}

		/**
		 * Low-level POST → normalized envelope:
		 * { ok:bool, status:int, data:array|null, usage:array|null, quota:array|null, error:array|null }
		 *
		 * @param string $path    Worker path.
		 * @param array  $body    JSON body.
		 * @param array  $headers Headers.
		 * @return array
		 */
		private function raw_post( $path, $body, $headers, $timeout = self::HTTP_TIMEOUT ) {
			$resp = wp_remote_post(
				$this->worker_url() . $path,
				array(
					'timeout' => $timeout,
					'headers' => $headers,
					'body'    => wp_json_encode( $body ),
				)
			);

			if ( is_wp_error( $resp ) ) {
				return $this->err( 'upstream_error', $resp->get_error_message(), 502 );
			}

			$code = (int) wp_remote_retrieve_response_code( $resp );
			$raw  = wp_remote_retrieve_body( $resp );
			$json = json_decode( $raw, true );

			if ( ! is_array( $json ) ) {
				// The endpoint returned non-JSON (404 page, redirect, wrong route…).
				// Surface the status and log a snippet so the URL/route can be fixed.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( sprintf( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
						'Porto AI: non-JSON from %s (HTTP %d): %s',
						$this->worker_url() . $path,
						$code,
						substr( trim( (string) $raw ), 0, 500 )
					) );
				}
				return $this->err(
					'internal_error',
					sprintf( __( 'Invalid response from the AI service (HTTP %d). Check the Worker URL/route.', 'porto' ), $code ),
					$code ? $code : 500
				);
			}

			if ( ! empty( $json['ok'] ) ) {
				return array(
					'ok'     => true,
					'status' => $code ? $code : 200,
					'data'   => isset( $json['data'] ) ? $json['data'] : array(),
					'usage'  => isset( $json['usage'] ) ? $json['usage'] : null,
					'quota'  => isset( $json['quota'] ) ? $json['quota'] : null,
					'error'  => null,
				);
			}

			$error = ( isset( $json['error'] ) && is_array( $json['error'] ) )
				? $json['error']
				: array( 'code' => 'internal_error', 'message' => __( 'AI service error.', 'porto' ) );

			return array(
				'ok'     => false,
				'status' => $code ? $code : 500,
				'data'   => null,
				'usage'  => null,
				'quota'  => isset( $json['quota'] ) ? $json['quota'] : null,
				'error'  => $error,
			);
		}

		/**
		 * @return array Normalized error envelope.
		 */
		private function err( $code, $message, $status, $retry_after = null ) {
			$error = array(
				'code'    => $code,
				'message' => $message,
			);
			if ( null !== $retry_after ) {
				$error['retry_after'] = (int) $retry_after;
			}
			return array(
				'ok'     => false,
				'status' => (int) $status,
				'data'   => null,
				'usage'  => null,
				'quota'  => null,
				'error'  => $error,
			);
		}
	}
}