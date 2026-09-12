<?php

/**
 * Porto Content Generator - generate content, excerpts and SEO meta with OpenAI.
 *
 * Requests are proxied server-side (wp_ajax_porto_ai_generate) so the API key
 * is never exposed to the browser. Uses the OpenAI Chat Completions API.
 *
 * @author     Porto Themes
 * @category   Porto Ai Engine
 * @since      2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Porto_Content_Generator' ) ) :
	class Porto_Content_Generator {

		// Admin Post types that AI panel isn't showing
		public $exclude_types = array( 'porto_builder', 'ptu', 'ptu_tax', 'acf-post-type', 'acf-taxonomy', 'cptm', 'yith-wcbm-badge', 'acf-field-group' );

		/**
		 * Default OpenAI model. Porto's current default (see the theme AI worker
		 * contract). Override with the PORTO_AI_MODEL constant or `porto_ai_model` filter.
		 *
		 * @since 3.9.1
		 */
		const DEFAULT_MODEL = 'gpt-5.4-mini';

		/**
		 * OpenAI Chat Completions endpoint.
		 *
		 * @since 3.9.1
		 */
		const OPENAI_ENDPOINT = 'https://api.openai.com/v1/chat/completions';

		public function __construct() {
			add_action( 'admin_footer', array( $this, 'add_dialog' ), 99 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ), 20 );
			add_filter( 'porto_js_admin_vars', array( $this, 'add_generator_vars' ) );
			add_action( 'add_meta_boxes', array( $this, 'porto_add_ai_meta_boxes' ) );
			// Server-side proxy: the OpenAI key never reaches the browser.
			add_action( 'wp_ajax_porto_ai_generate', array( $this, 'ajax_generate' ) );
		}

		/**
		 * The OpenAI API key, read server-side only.
		 * Prefers the PORTO_AI_OPENAI_KEY constant, then the theme option.
		 *
		 * @since 3.9.1
		 * @return string
		 */
		public function get_api_key() {
			if ( defined( 'PORTO_AI_OPENAI_KEY' ) && PORTO_AI_OPENAI_KEY ) {
				return trim( (string) PORTO_AI_OPENAI_KEY );
			}
			global $porto_settings;
			$key = empty( $porto_settings['ai-gpt-key'] ) ? '' : $porto_settings['ai-gpt-key'];
			return trim( (string) apply_filters( 'porto_ai_openai_key', $key ) );
		}

		/**
		 * The model id to use. Filterable / constant-overridable.
		 *
		 * @since 3.9.1
		 * @return string
		 */
		public function get_model() {
			$model = defined( 'PORTO_AI_MODEL' ) && PORTO_AI_MODEL ? PORTO_AI_MODEL : self::DEFAULT_MODEL;
			return (string) apply_filters( 'porto_ai_model', $model );
		}

		/**
		 * Whether the AI engine has a usable key.
		 *
		 * @since 3.9.1
		 * @return bool
		 */
		public function has_key() {
			return '' !== $this->get_api_key();
		}

		/**
		 * Add AI meta Fields
		 * Except page of plugin which adds custom post type - Post Type Unlimted, Custom Post Type Maker
		 * 
		 * @since 2.8.0
		 */
		public function porto_add_ai_meta_boxes() {
			$screen = get_current_screen();
			if ( function_exists( 'add_meta_box' ) && $screen && 'post' == $screen->base && ! in_array( $screen->id, $this->exclude_types ) ) {
				add_meta_box( 'porto-api-engine', __( 'Porto AI Engine', 'porto-functionality' ), 'porto_add_ai_meta_box', $screen->id, 'side', 'high' );
			}
		}

		/**
		 * Add AI Dialog
		 * 
		 * @since 2.8.0
		 */
		public function add_dialog() {
			$screen = get_current_screen();
			if ( $screen && 'post' == $screen->base && ! in_array( $screen->id, $this->exclude_types ) ) {
				ob_start();
				$this->add_style();
				echo ob_get_clean();
				if ( $this->has_key() ) :?>
					<div class="porto-dialog-wrapper porto-ai-dialog hide">
						<div class="porto-dialog-overlay">
						</div>
						<div class="porto-admin-dialog">
							<div class="porto-dialog-header">
								<h3 class="porto-dialog-title"></h3>
							</div>
							<div class="porto-dialog-content">
								<textarea class="output" id="ai-output"></textarea>
								<i class="porto-ajax-loader"></i>
							</div>
							<div class="porto-dialog-footer">
								<button class="button button-primary porto-dialog-btn btn-insert" name="Insert"><?php esc_html_e( 'Insert into Editor', 'porto-functionality' ); ?></button>
								<button class="button button-primary porto-dialog-btn btn-copy" name="Copy"><?php esc_html_e( 'Copy to Clipboard', 'porto-functionality' ); ?></button>
								<button class="button button-primary porto-dialog-btn btn-close" name="Close"><?php esc_html_e( 'Close', 'porto-functionality' ); ?></button>
							</div>
						</div>
					</div>
				<?php
				endif;
			}
		}

		/**
		 * Enqueue js for Ai engine
		 * 
		 * @since 2.8.0
		 */
		public function enqueue_script() {
			$screen = get_current_screen();

			if ( $this->has_key() && $screen && 'post' == $screen->base && ! in_array( $screen->id, $this->exclude_types ) ) {
				wp_enqueue_script( 'porto-ai-engine', plugin_dir_url( __FILE__ ) . 'ai-generator.min.js', array( 'jquery-core' ), PORTO_FUNC_VERSION, true );
			}
		}

		/**
		 * Add vars to js_porto_admin_vars
		 * 
		 * @since 2.8.0
		 */
		public function add_generator_vars( $vars ) {
			$screen = get_current_screen();
			if ( $this->has_key() && $screen && 'post' == $screen->base && ! in_array( $screen->id, $this->exclude_types ) ) {
				// The key is NEVER sent to the browser. The JS calls the server-side
				// proxy (wp_ajax_porto_ai_generate) with this nonce instead.
				$vars['ai_enabled'] = true;
				$vars['ai_nonce']   = wp_create_nonce( 'porto_ai_generate' );
				$vars['ajaxurl']    = admin_url( 'admin-ajax.php' );
				if ( 'post' == $screen->id ) {
					$vars['post_type'] = 'blog';
				} else {
					$vars['post_type'] = $screen->id;
				}
			}
			return $vars;
		}

		/**
		 * Server-side proxy to OpenAI. The browser sends the assembled prompt and
		 * generation parameters; the secret API key is read here and never exposed.
		 *
		 * @since 3.9.1
		 */
		public function ajax_generate() {
			check_ajax_referer( 'porto_ai_generate', 'nonce' );

			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'porto-functionality' ) ), 403 );
			}

			$key = $this->get_api_key();
			if ( '' === $key ) {
				wp_send_json_error( array( 'message' => __( 'OpenAI API key is not configured.', 'porto-functionality' ) ), 400 );
			}

			$prompt = isset( $_POST['prompt'] ) ? trim( (string) wp_unslash( $_POST['prompt'] ) ) : '';
			if ( '' === $prompt ) {
				wp_send_json_error( array( 'message' => __( 'The prompt is empty.', 'porto-functionality' ) ), 400 );
			}

			// Floor of 256: on GPT-5-class models `max_completion_tokens` also
			// covers internal reasoning tokens, so a tiny cap can yield empty output.
			// The cap is an upper bound only — the prompt itself constrains length.
			$max_tokens  = isset( $_POST['max_tokens'] ) ? absint( $_POST['max_tokens'] ) : 1024;
			$max_tokens  = min( max( $max_tokens, 256 ), 4096 );
			$temperature = isset( $_POST['temperature'] ) ? (float) $_POST['temperature'] : 0.7;
			$temperature = min( max( $temperature, 0 ), 2 );

			$body = array(
				'model'                 => $this->get_model(),
				'messages'              => array(
					array(
						'role'    => 'system',
						'content' => __( 'You are a professional copywriter. Return only the requested text, with no preamble, labels, or surrounding quotation marks.', 'porto-functionality' ),
					),
					array(
						'role'    => 'user',
						'content' => $prompt,
					),
				),
				'max_completion_tokens' => $max_tokens,
				'temperature'           => $temperature,
			);

			/**
			 * Filter the OpenAI request body before it is sent. Lets developers
			 * adapt parameters (e.g. drop `temperature` for models that only
			 * support the default) without editing core.
			 *
			 * @since 3.9.1
			 */
			$body = apply_filters( 'porto_ai_request_body', $body, $prompt );

			$response = wp_remote_post(
				self::OPENAI_ENDPOINT,
				array(
					'timeout' => 30,
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $key,
					),
					'body'    => wp_json_encode( $body ),
				)
			);

			if ( is_wp_error( $response ) ) {
				wp_send_json_error( array( 'message' => $response->get_error_message() ), 502 );
			}

			$code = (int) wp_remote_retrieve_response_code( $response );
			$data = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( 200 !== $code || ! is_array( $data ) ) {
				$message = isset( $data['error']['message'] ) ? $data['error']['message'] : __( 'The AI service returned an error.', 'porto-functionality' );
				if ( 401 === $code ) {
					$message = __( 'Incorrect API key provided.', 'porto-functionality' );
				}
				wp_send_json_error( array( 'message' => $message ), $code ? $code : 500 );
			}

			$text = isset( $data['choices'][0]['message']['content'] ) ? trim( $data['choices'][0]['message']['content'] ) : '';
			if ( '' === $text ) {
				wp_send_json_error( array( 'message' => __( 'No content was generated. Please refine your prompt and try again.', 'porto-functionality' ) ), 200 );
			}

			wp_send_json_success( array( 'text' => $text ) );
		}

		/**
		 * Add styles for metabox and dialog
		 * 
		 * @since 2.8.0
		 */
		public function add_style() {
			?>
				<style>
					#porto-api-engine h3 { padding-top: 0 !important; margin: 0 0 10px 0 !important; font-size: 13px; text-transform: capitalize; }
					#porto-api-engine .inside .metabox .box-option {
						margin-right: 0;
						padding-top: 0;
					}
					#porto-api-engine .postbox-header { background-color: #2271b1 !important; }
					#porto-api-engine .postbox-header > *,
					#porto-api-engine .postbox-header .button,
					#porto-api-engine .postbox-header button,
					#porto-api-engine .postbox-header span{color: #fff !important;}
			<?php
			if ( $this->has_key() ) {
				?>
					.porto-ai-dialog.loading i.porto-ajax-loader {
						display: block;
					}
					.porto-ai-dialog.loading .btn-copy,
					.porto-ai-dialog.loading .btn-insert,
					.porto-ai-dialog.loading .output {
						visibility: hidden;
						opacity: 0;
					}
					.porto-ai-dialog.loading .btn-close {
						cursor: no-drop;
						background-color: #222529;
					}
					.porto-ai-dialog .output {
						width: 100%;
						height: 250px;
						display: block;
						max-height: 300px;
					}
					.porto-ai-dialog.hide {
						display: none;
					}
					.porto-ai-dialog .button {
						line-height: 1.2 !important;
					}
					.porto-ai-dialog .porto-dialog-title { 
						font-size: 20px;
						line-height: 1.2;
					}
					#porto-api-engine .ai_generate { width: 100%; }
					#porto-api-engine .porto-meta-tab .metabox:first-child { display: none; }
					#porto-api-engine .porto-meta-tab select { 
						max-width: calc( 100% - 2px );
					}
					#porto-api-engine .porto-meta-tab .metabox { padding-left: 0; padding-right: 0; }
					#porto-api-engine .porto-meta-tab .metabox .metainner {
						padding: 0;
						width: 100%;
					}
					#user_word, #ai_topic { height: 100px; margin: 1px; width: calc( 100% - 2px ); }
					#ai_topic { height: 70px; }
					#user_word:focus { outline: 1px solid #08c; }
					
					/* Seo Plugin */
					.button-plugin-gen { margin: 0 0 5px 10px !important; height: 100% !important; }
					#aioseo-post-settings-meta-description-row .button-plugin-gen { margin: 0 0 0 auto !important; }
					/* Rank Math Seo */
					.rank-math-editor-general [for="rank-math-editor-description"] {
						display: inline-flex !important;
					}
					.aioseo-post-settings-modal #aioseo-post-settings-meta-description-row .add-tags {
						position: static;
						margin-bottom: 10px;
					}
					.rank-math-editor-general .is-primary { height: 24px !important; vertical-align: middle; }
				<?php
			} else {
				?>
					#porto-api-engine .porto-meta-tab .metabox:not(:first-child) { display: none; }
				<?php
			}
			echo '</style>';
		}
	}

	new Porto_Content_Generator();
endif;