<?php

use WBCR\Factory_Templates_135\ImpressiveLite;

/**
 * Class of plugin page. Must be registered in file admin/class-prefix-page.php
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2021, Webcraftic
 * @see           ImpressiveLite
 *
 * @version       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIS_Page_Logger extends Wbcr_FactoryLogger150_Lite {

	/**
	 * Name of the template to get content of. It will be based on plugins /admin/views/ dir.
	 * /admin/views/tab-{$template_name}.php
	 *
	 * @var string
	 */
	public $template_name = "log";

	/**
	 * @var string
	 */
	public $custom_target = "admin.php";

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * {@inheritdoc}
	 */
	public $show_search_options_form = false;

	/**
	 * Show this page in tabs?
	 * default: true
	 */
	public $show_menu_tab = true;

	/**
	 * Title for tab in menu
	 */
	public $menu_tab_title;

	public function __construct( $plugin ) {
		$this->menu_tab_title = $this->menu_tab_title ?? $this->menu_title;

		parent::__construct( $plugin );
	}

	/**
	 * Render and return content of the template.
	 * /admin/views/tab-{$template_name}.php
	 *
	 * @return mixed Content of the page
	 */
	public function render( $name = '' ) {
		if ( '' == $name ) {
			$name = $this->template_name;
		}
		ob_start();
		if ( is_callable( $name ) ) {
			echo call_user_func( $name );
		} elseif ( strpos( $name, DIRECTORY_SEPARATOR ) !== false && ( is_file( $name ) || is_file( $name . '.php' ) ) ) {
			if ( is_file( $name ) ) {
				$path = $name;
			} else {
				$path = $name . '.php';
			}
		} else {
			$path = WAPT_PLUGIN_DIR . "/admin/views/tab-{$name}.php";
		}
		if ( ! is_file( $path ) ) {
			return '';
		}
		include $path;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->scripts->request( [
			'control.checkbox',
			'control.dropdown',
			'control.integer',
			'plugin.nouislider',
			'bootstrap.dropdown'
		], 'bootstrap' );

		$this->styles->request( [
			'bootstrap.core',
			'bootstrap.form-group',
			'bootstrap.separator',
			'control.dropdown',
			'control.checkbox',
			'control.integer',
			'plugin.nouislider',
		], 'bootstrap' );
	}

	/**
	 * Show rendered template - $template_name
	 */
	public function showPageContent() {
		parent::showPageContent();
	}

	public function getPluginSlug() {
		$plugin_slug = $this->plugin->getPluginInfoAttr( 'updates_settings' );

		return $plugin_slug['slug'] ?? WAPT_PLUGIN_SLUG;
	}

	/**
	 * @param string $position
	 *
	 * @return mixed|void
	 */
	protected function getPageWidgets( $position = 'bottom' ) {
		$widgets = [];

		if ( 'bottom' == $position ) {
			$widgets['rating_widget']  = $this->getRatingWidget( [] );
		}

		/**
		 * @since 3.8.2 - добавлен
		 */
		$widgets = apply_filters( 'wbcr/factory/pages/impressive_lite/widgets', $widgets, $position, $this->plugin, $this );

		return $widgets;
	}

	/**
	 * Создает html разметку виджета рейтинга
	 *
	 * @param array $args
	 *
	 * @author Artem Prihodko <webtemyk@yandex.ru>
	 */
	public function showRatingWidget( array $args ) {
		$plugin_slug = $this->getPluginSlug();

		if ( ! isset( $args[0] ) || empty( $args[0] ) ) {
			$page_url = "https://wordpress.org/support/plugin/{$plugin_slug}/reviews/#new-post";
		} else {
			$page_url = $args[0];
		}

		$page_url = apply_filters( 'wbcr_factory_pages_481_implite_rating_widget_url', $page_url, $this->plugin->getPluginName(), $this->getResultId() );

		?>
		<div class="wbcr-factory-sidebar-widget">
			<strong><?php esc_html_e( 'Leave a review:', 'instagram-slider-widget' ); ?></strong>
			<?php esc_html_e( 'Liking the plugin? A quick review would mean a lot and helps us make it even better.', 'instagram-slider-widget' ); ?>
			<span>
				<i class="dashicons dashicons-star-filled"></i>
				<a class="wbcr-leave-review-link" href="<?php echo $page_url; ?>" title="Go rate us" target="_blank">
					<?php esc_html_e( 'Leave a Review', 'instagram-slider-widget' ); ?>
				</a>
			</span>
		</div>
		<?php
	}

	/**
	 * @return string
	 */
	public function getMenuSubTitle() {
		$menu_title = $this->menu_tab_title ?? $this->menu_title ?? $this->page_title;

		return apply_filters( 'wbcr/factory/pages/impressive_lite/menu_title', $menu_title, $this->plugin->getPluginName(), $this->id );
	}

	public static function instruction( $title, $html ) {
		return "<div class='form-group form-group-textbox'>
					<label for='' class='col-sm-4 control-label'>" . esc_html( $title ) . "</label>
					<div class='control-group col-sm-8'>
						<div class='help-block'>
							" . esc_html( $html ) . "
						</div>
					</div>
				</div>";
	}

}


