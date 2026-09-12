<?php
/**
 * Porto AI — Setup Wizard UI integration.
 *
 * Enqueues the assistant + title-generator assets on the wizard's Demo Content
 * step, localizes the REST config (url + nonce), and renders the markup at the
 * hook points added to setup_wizard.php. Everything is gated behind
 * Porto_AI_Manager::is_enabled() so the UI stays invisible until a Worker URL
 * is configured.
 *
 * @package Porto
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Porto_AI_UI' ) ) {

	class Porto_AI_UI {

		const PAGE_SLUG = 'porto-setup-wizard';

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
			if ( ! is_admin() ) {
				return;
			}
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 31 );
			add_action( 'porto_ai_after_logo_section', array( $this, 'render_title_field' ) );
			add_action( 'porto_ai_logo_actions', array( $this, 'render_logo_button' ) );
			add_action( 'porto_ai_before_demo_grid', array( $this, 'render_assistant_intro' ) );
			add_action( 'admin_footer', array( $this, 'render_footer_markup' ) );
		}

		/** @return bool Whether we're on the wizard's Demo Content step. */
		private function is_demo_step() {
			if ( empty( $_GET['page'] ) || self::PAGE_SLUG !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return false;
			}
			$step = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return '' === $step || 'demo_content' === $step;
		}

		/** @return bool */
		private function active() {
			return Porto_AI_Manager::is_enabled() && $this->is_demo_step();
		}

		/**
		 * Enqueue assets + localize. Depends on `porto-setup` so jQuery and
		 * Magnific Popup are already present and our scripts load after.
		 */
		public function enqueue() {
			if ( ! $this->active() ) {
				return;
			}

			$base    = PORTO_URI . '/inc/admin/setup_wizard/ai/assets/';
			$version = defined( 'PORTO_VERSION' ) ? PORTO_VERSION : '1.0';

			wp_enqueue_style( 'porto-ai-assistant', $base . 'css/ai-assistant.css', array( 'porto-setup' ), $version );
			wp_enqueue_style( 'porto-ai-title-generator', $base . 'css/ai-title-generator.css', array( 'porto-setup' ), $version );
			wp_enqueue_style( 'porto-ai-logo-generator', $base . 'css/ai-logo-generator.css', array( 'porto-setup', 'porto-ai-title-generator' ), $version );

			wp_register_script( 'porto-ai-api', $base . 'js/ai-api.js', array( 'jquery-core', 'wp-i18n' ), $version, true );
			wp_register_script( 'porto-ai-assistant', $base . 'js/ai-assistant.js', array( 'porto-ai-api', 'porto-setup' ), $version, true );
			wp_register_script( 'porto-ai-title-generator', $base . 'js/ai-title-generator.js', array( 'porto-ai-api', 'porto-setup' ), $version, true );
			wp_register_script( 'porto-ai-logo-generator', $base . 'js/ai-logo-generator.js', array( 'porto-ai-api', 'porto-setup' ), $version, true );

			wp_localize_script(
				'porto-ai-api',
				'portoAI',
				array(
					'nonce'       => wp_create_nonce( 'wp_rest' ),
					'rest'        => array(
						'recommend' => Porto_AI_Manager::rest_url( 'demo-recommendation' ),
						'titles'    => Porto_AI_Manager::rest_url( 'title-suggestions' ),
						'logos'     => Porto_AI_Manager::rest_url( 'logo-generation' ),
						'saveLogo'  => Porto_AI_Manager::rest_url( 'save-logo' ),
						'siteTitle' => Porto_AI_Manager::rest_url( 'site-title' ),
						'status'    => Porto_AI_Manager::rest_url( 'status' ),
					),
					'suggestions' => array(
						__( 'Online store', 'porto' ),
						__( 'Restaurant', 'porto' ),
						__( 'Agency', 'porto' ),
						__( 'Portfolio', 'porto' ),
						__( 'Business', 'porto' ),
						__( 'Blog', 'porto' ),
					),
				)
			);

			wp_set_script_translations( 'porto-ai-assistant', 'porto' );
			wp_set_script_translations( 'porto-ai-title-generator', 'porto' );
			wp_set_script_translations( 'porto-ai-logo-generator', 'porto' );

			wp_enqueue_script( 'porto-ai-assistant' );
			wp_enqueue_script( 'porto-ai-title-generator' );
			wp_enqueue_script( 'porto-ai-logo-generator' );
		}

		/** Website Title field + Generate trigger, under the logo section. */
		public function render_title_field() {
			if ( ! $this->active() ) {
				return;
			}
			include __DIR__ . '/views/site-title-field.php';
		}

		/** "Generate Logo" button inside the logo card. */
		public function render_logo_button() {
			if ( ! $this->active() ) {
				return;
			}
			?>
			<button type="button" class="btn btn-primary btn-sm porto-ai-logo-generate" data-porto-ai-logo-open>
				<span aria-hidden="true">✨</span> <?php esc_html_e( 'Generate Logo', 'porto' ); ?>
			</button>
			<?php
		}

		/** Small intro banner above the demo grid. */
		public function render_assistant_intro() {
			if ( ! $this->active() ) {
				return;
			}
			?>
			<div class="porto-ai-intro">
				<div class="porto-ai-intro__icon" aria-hidden="true">✨</div>
				<div class="porto-ai-intro__content">
					<h4 class="porto-ai-intro__title"><?php esc_html_e( 'Not sure which demo to pick?', 'porto' ); ?></h4>
					<p class="porto-ai-intro__text"><?php esc_html_e( 'Skip scrolling through 100+ demos. Just describe your business and the Porto AI Assistant instantly recommends the best-matching demos — tailored to your industry, style, and the exact features you need.', 'porto' ); ?></p>
					<ul class="porto-ai-intro__benefits">
						<li><?php esc_html_e( 'Personalized picks in seconds', 'porto' ); ?></li>
						<li><?php esc_html_e( 'Matched to your industry & style', 'porto' ); ?></li>
						<li><?php esc_html_e( 'Saves hours of browsing', 'porto' ); ?></li>
					</ul>
				</div>
				<button type="button" class="btn btn-sm porto-ai-open" data-porto-ai-open><?php esc_html_e( 'Ask AI Assistant', 'porto' ); ?></button>
			</div>
			<?php
		}

		/** Floating button + Magnific inline popups (panel + title generator). */
		public function render_footer_markup() {
			if ( ! $this->active() ) {
				return;
			}
			include __DIR__ . '/views/assistant-panel.php';
			include __DIR__ . '/views/title-generator-popup.php';
			include __DIR__ . '/views/logo-generator-popup.php';
		}
	}
}