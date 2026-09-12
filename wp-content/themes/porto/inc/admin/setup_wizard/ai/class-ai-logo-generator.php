<?php
/**
 * Porto AI — Logo generator orchestrator.
 *
 * generate() asks the Worker (OpenAI Images API) for a logo and returns the
 * image(s). save_to_media() sideloads a chosen image into the media library and
 * returns its attachment id, so the wizard's existing logo-save flow (the hidden
 * #new_logo_id field) can pick it up on "Save & Continue".
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Porto_AI_Logo_Generator' ) ) {

	class Porto_AI_Logo_Generator {

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
		 * @param array $args { business_name, industry, keywords, style }.
		 * @return array|WP_Error|array Shaped { images[] }, WP_Error, or proxy error envelope.
		 */
		public function generate( $args ) {
			$args = is_array( $args ) ? $args : array();

			$keywords = array();
			foreach ( array( 'keywords', 'industry' ) as $k ) {
				if ( isset( $args[ $k ] ) ) {
					$raw = is_array( $args[ $k ] ) ? $args[ $k ] : preg_split( '/[,\n]+/', (string) $args[ $k ] );
					foreach ( (array) $raw as $v ) {
						$v = sanitize_text_field( (string) $v );
						if ( '' !== $v ) {
							$keywords[] = $v;
						}
					}
				}
			}

			$payload = array(
				'business_name' => sanitize_text_field( isset( $args['business_name'] ) ? $args['business_name'] : '' ),
				'keywords'      => array_slice( array_values( array_unique( $keywords ) ), 0, 8 ),
				'style'         => sanitize_text_field( isset( $args['style'] ) ? $args['style'] : '' ),
			);

			if ( '' === $payload['business_name'] && empty( $payload['keywords'] ) && '' === $payload['style'] ) {
				return new WP_Error( 'empty_request', __( 'Add a business name or a few keywords first.', 'porto' ) );
			}

			$res = Porto_AI_Proxy_Client::instance()->logos( $payload );
			if ( empty( $res['ok'] ) ) {
				return $res;
			}

			$images = array();
			$raw    = isset( $res['data']['images'] ) && is_array( $res['data']['images'] ) ? $res['data']['images'] : array();
			foreach ( $raw as $img ) {
				$img = (string) $img;
				if ( preg_match( '#^https?://#i', $img ) ) {
					$images[] = esc_url_raw( $img );
				} elseif ( 0 === strpos( $img, 'data:image/' ) ) {
					$images[] = $img; // data URI, validated again on save
				}
			}

			return array( 'images' => $images );
		}

		/**
		 * Sideload a generated logo (remote URL or data URI) into the media library.
		 *
		 * @param string $image http(s) URL or `data:image/...;base64,...`.
		 * @return array|WP_Error { id, url } or WP_Error.
		 */
		public function save_to_media( $image ) {
			if ( ! current_user_can( 'upload_files' ) ) {
				return new WP_Error( 'forbidden', __( 'You are not allowed to upload files.', 'porto' ) );
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$image = (string) $image;

			if ( 0 === strpos( $image, 'data:' ) ) {
				if ( ! preg_match( '#^data:image/([a-z0-9.+-]+);base64,#i', $image, $m ) ) {
					return new WP_Error( 'bad_image', __( 'Invalid image data.', 'porto' ) );
				}
				$ext  = 'jpeg' === strtolower( $m[1] ) ? 'jpg' : preg_replace( '/[^a-z0-9]/', '', strtolower( $m[1] ) );
				$data = base64_decode( substr( $image, strlen( $m[0] ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				if ( false === $data || '' === $data ) {
					return new WP_Error( 'bad_image', __( 'Could not decode the image.', 'porto' ) );
				}
				$filename = 'porto-ai-logo-' . wp_generate_password( 8, false ) . '.' . ( $ext ? $ext : 'png' );
				$tmp      = wp_tempnam( $filename );
				if ( ! $tmp ) {
					return new WP_Error( 'tmp', __( 'Could not create a temporary file.', 'porto' ) );
				}
				if ( false === file_put_contents( $tmp, $data ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
					@unlink( $tmp ); // phpcs:ignore
					return new WP_Error( 'write', __( 'Could not write the image.', 'porto' ) );
				}
			} else {
				if ( ! preg_match( '#^https?://#i', $image ) || ! wp_http_validate_url( $image ) ) {
					return new WP_Error( 'bad_url', __( 'Invalid image URL.', 'porto' ) );
				}
				$tmp = download_url( $image, 60 );
				if ( is_wp_error( $tmp ) ) {
					return $tmp;
				}
				$filename = 'porto-ai-logo-' . wp_generate_password( 8, false ) . '.png';
			}

			$file_array = array(
				'name'     => $filename,
				'tmp_name' => $tmp,
			);

			$id = media_handle_sideload( $file_array, 0, __( 'Porto AI generated logo', 'porto' ) );
			if ( is_wp_error( $id ) ) {
				if ( file_exists( $tmp ) ) {
					@unlink( $tmp ); // phpcs:ignore
				}
				return $id;
			}

			return array(
				'id'  => (int) $id,
				'url' => wp_get_attachment_image_url( $id, 'full' ),
			);
		}
	}
}