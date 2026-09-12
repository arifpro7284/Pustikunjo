<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Porto_Gutenberg_Blocks' ) ) :

	/**
	 * Porto Gutenberg-only blocks.
	 *
	 * Blocks registered here have no WPBakery shortcode / Elementor widget equivalent. The block
	 * "edit" ( JS ) lives in shortcodes/assets/blocks/blocks.js ( "porto_blocks" script handle ),
	 * while the server render is provided by the render callbacks below.
	 *
	 * @since 7.1.0
	 */
	class Porto_Gutenberg_Blocks {

		/**
		 * @var Porto_Gutenberg_Blocks|null
		 */
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			// register_block_type() should run on init; this file is included on plugins_loaded, so
			// hooking init here fires at the right time.
			add_action( 'init', array( $this, 'register_blocks' ) );

			// Load the appear-animation stylesheet inside the block-editor iframe so Style Options >
			// Animation previews can play there ( the theme only enqueues it on the frontend ).
			add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_iframe_css' ) );
		}

		/**
		 * Enqueue frontend stylesheets into the block-editor iframe canvas.
		 *
		 * enqueue_block_assets fires for both the frontend and the editor iframe; the is_admin()
		 * guard limits this to the editor. Registered without the theme 'porto-theme' dependency so
		 * they load standalone inside the iframe.
		 *
		 * @since 7.1.0
		 */
		public function enqueue_block_iframe_css() {
			if ( ! is_admin() || ! defined( 'PORTO_URI' ) ) {
				return;
			}
			$ver = defined( 'PORTO_VERSION' ) ? PORTO_VERSION : false;

			// Appear-animation CSS so Style Options > Animation previews can play.
			wp_enqueue_style( 'porto-animate-editor', PORTO_URI . '/css/part/animate.css', array(), $ver );

			// Post Type Builder CSS ( styles the porto-tb/* blocks that builder layouts are made of ) so
			// server-rendered builder layouts ( e.g. the Porto Posts Grid block ) look right in the
			// editor iframe - it is otherwise only enqueued on the frontend / builder-edit screen.
			if ( defined( 'PORTO_FUNC_URL' ) ) {
				wp_enqueue_style( 'porto-type-builder-editor', PORTO_FUNC_URL . 'builders/assets/type-builder.css', array( 'porto-blocks-editor' ), defined( 'PORTO_FUNC_VERSION' ) ? PORTO_FUNC_VERSION : $ver );
			}
		}

		/**
		 * Register the Gutenberg blocks.
		 *
		 * @since 7.1.0
		 */
		public function register_blocks() {
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			// Porto Shipping progress bar ( WooCommerce free shipping progress bar ).
			$args = array(
				'editor_script'   => 'porto_blocks',
				'render_callback' => array( $this, 'render_shipping_progress_bar' ),
				'attributes'      => array(
					'style_options' => array( 'type' => 'object' ),
					'className'     => array( 'type' => 'string' ),
				),
			);

			// The theme only enqueues the bar stylesheet on the frontend; register it here too and use
			// it as the block editor style so the ServerSideRender preview is styled.
			if ( defined( 'PORTO_LIB_URI' ) && ! wp_style_is( 'porto-fs-progress-bar', 'registered' ) ) {
				wp_register_style( 'porto-fs-progress-bar', PORTO_LIB_URI . '/woocommerce-shipping-progress-bar/shipping-progress-bar.css', array(), defined( 'PORTO_VERSION' ) ? PORTO_VERSION : false );
			}
			if ( wp_style_is( 'porto-fs-progress-bar', 'registered' ) ) {
				$args['editor_style'] = 'porto-fs-progress-bar';
			}

			register_block_type( 'porto/porto-shipping-progress-bar', $args );
		}

		/**
		 * Render the WooCommerce free shipping progress bar on the frontend.
		 *
		 * The bar markup is produced by Porto_Woocommerce_shipping_pbar
		 * ( porto/inc/lib/woocommerce-shipping-progress-bar/woocommerce-shipping-progress-bar.php ).
		 * The block uses ServerSideRender, so this callback also runs for the block-editor preview
		 * ( via the block-renderer REST route ); the cart is loaded below so real content can render
		 * there. When the cart is empty / below the threshold the bar is empty and the editor falls
		 * back to the static preview image ( ServerSideRender EmptyResponsePlaceholder ).
		 *
		 * @param array       $atts    Block attributes.
		 * @param string      $content Inner content ( unused ).
		 * @param WP_Block    $block   Block instance ( unused ).
		 * @return string
		 * @since 7.1.0
		 */
		public function render_shipping_progress_bar( $atts, $content = '', $block = null ) {
			if ( ! class_exists( 'Porto_Woocommerce_shipping_pbar' ) || ! function_exists( 'WC' ) || ! WC() ) {
				return '';
			}

			// ServerSideRender requests the block through the block-renderer REST route with
			// context=edit. There the admin cart is normally empty, so the real bar returns nothing
			// ( needs_shipping() / show_shipping() / threshold all bail ); render a sample bar with a
			// fake remaining amount for the editor preview only.
			$is_editor_preview = ( defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_GET['context'] ) && 'edit' === sanitize_key( wp_unslash( $_GET['context'] ) ) );

			if ( ! $is_editor_preview && ! WC()->cart ) {
				return '';
			}

			// Build the extra wrapper classes: porto-gb-{hashcode} ( + responsive / alignment ) so the
			// Style Options CSS generated from `style_options` at save time targets the bar, plus any
			// custom class. The tag must match include_style()'s
			// str_replace( 'porto/porto-', '', block_name ).
			$extra_class = apply_filters( 'porto_elements_wrap_css_class', '', is_array( $atts ) ? $atts : array(), 'shipping-progress-bar' );
			if ( ! empty( $atts['className'] ) ) {
				$extra_class .= ' ' . $atts['className'];
			}
			$extra_class = trim( $extra_class );

			// Inject those classes onto the bar's own wrapper ( no extra element ) via the render
			// file's porto_free_shipping_wrap_cls filter, scoped to this render only.
			$add_cls = null;
			if ( '' !== $extra_class ) {
				$add_cls = function( $classes ) use ( $extra_class ) {
					return trim( $classes . ' ' . $extra_class );
				};
				add_filter( 'porto_free_shipping_wrap_cls', $add_cls );
			}

			if ( $is_editor_preview ) {
				$output = $this->render_preview_bar();
			} else {
				ob_start();
				// 'init' has already fired by render time, so instantiating here does NOT re-register the
				// cart / checkout hooks ( the constructor only hooks 'init' ) - it just gives access to
				// the render method.
				$pbar = new Porto_Woocommerce_shipping_pbar();
				$pbar->shipping_progress_bar();
				$output = ob_get_clean();
			}

			if ( $add_cls ) {
				remove_filter( 'porto_free_shipping_wrap_cls', $add_cls );
			}

			return $output;
		}

		/**
		 * Render a sample free-shipping progress bar with a fake remaining amount, for the block-editor
		 * preview only ( where the cart is normally empty ). Mirrors the "in progress" markup of
		 * Porto_Woocommerce_shipping_pbar::shipping_progress_bar().
		 *
		 * @return string
		 * @since 7.1.0
		 */
		private function render_preview_bar() {
			$classes   = apply_filters( 'porto_free_shipping_wrap_cls', 'porto-free-shipping' );
			$percent   = 65;
			$remaining = function_exists( 'wc_price' ) ? wc_price( 50 ) : '50';

			ob_start();
			?>
			<div class="<?php echo esc_attr( $classes ); ?>">
				<div class="porto-free-shipping-notice">
					<i class="porto-icon-package"></i>
					<label>
						<?php
						printf(
						/* translators: %s: remaining amount to reach free shipping */
							esc_html__( 'Add %s to cart and get free shipping!', 'porto' ),
							$remaining // phpcs:ignore WordPress.Security.EscapeOutput
						);
						?>
					</label>
				</div>
				<progress class="porto-free-shipping-bar porto-scroll-progress" max="100" value="<?php echo esc_attr( $percent ); ?>"></progress>
			</div>
			<?php
			return ob_get_clean();
		}
	}

	Porto_Gutenberg_Blocks::get_instance();

endif;
