<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIS_Youtube_Pro {

    /**
	 * @see self::app()
	 * @var self
	 */
	private static $instance;

	/**
	 * @return self
	 */
	public static function instance() {
		return self::$instance;
	}

    public function __construct() {
        self::$instance = $this;

        if ( is_admin() ) {
			add_filter( 'wis/youtube/options/link_to', array( $this, 'youtube_links' ), 10, 1 );
			add_filter( 'wis/youtube/mob_settings', [ $this, 'settings_youtube' ], 10, 6 );
        } else {
			add_filter( 'wyt/pro/display', array( $this, 'display_popup' ), 10, 2 );
		}
    }

	public function youtube_links( $links ) {
		$links2          = $links;
		$links2["ypopup"] = 'Pop up';
		return $links2;
	}

	public function settings_youtube( $content, $_this, $instance, $sliders, $options_linkto, $w_id ) {
		return WIS_Page::instance()->render( WYT_COMPONENT_VIEWS_DIR . '/form-feed-mobile', [
			'_this'          => $_this,
			'instance'       => $instance,
			'sliders'        => $sliders,
			'options_linkto' => $options_linkto,
			'w_id'           => $w_id,
		] );
	}

	/**
	 * Pro version of display_images
	 *
	 * @param array $args
	 * @param array $videos
	 *
	 * @return string
	 */
	public function display_popup( $args, $videos, $account_data ) {

		$output = '';

		if ( $args['yimages_link'] == 'ypopup' ) {
			$this->include_remodal_assets();
			$output .= $this->render_template( 'popup_template', array(
				'account' => $account_data,
				'videos' => $videos
			) );
		}

		return $output;
	}

	private function include_remodal_assets(){
		wp_enqueue_script(WIS_Plugin::app()->getPrefix() . 'youtube-remodal-js', WYT_COMPONENT_URL . '/assets/js/yt.remodal.min.js', array(), WIS_Plugin::app()->getPluginVersion(), true);
		wp_enqueue_script(WIS_Plugin::app()->getPrefix() . 'youtube-remodal-view-js', WYT_COMPONENT_URL . '/assets/js/yt.remodal-view.js', array(), WIS_Plugin::app()->getPluginVersion(), true);

		wp_enqueue_style(WIS_Plugin::app()->getPrefix() . 'youtube-remodal-css', WYT_COMPONENT_URL . '/assets/css/remodal.css');
		wp_enqueue_style(WIS_Plugin::app()->getPrefix() . 'youtube-remodal-theme-css', WYT_COMPONENT_URL . '/assets/css/remodal-default-theme.css');
		wp_enqueue_style(WIS_Plugin::app()->getPrefix() . 'youtube-remodal-view-css', WYT_COMPONENT_URL . '/assets/css/remodal-view.css');
		wp_enqueue_style(WIS_Plugin::app()->getPrefix() . 'youtube-icons-css', WYT_COMPONENT_URL . '/assets/css/icons.css');
		//wp_enqueue_style(WIS_Plugin::app()->getPrefix() . 'youtube-icons-css', WYT_COMPONENT_URL . '/assets/css/fontawesome.min.css');
	}

	/**
	 * Method renders layout template
	 *
	 * @param string $template_name Template name.
	 *
	 * @param array $args Template arguments.
	 *
	 * @return false|string
	 */
	private function render_template( $name, $args ) {
		ob_start();
		if ( strpos( $name, DIRECTORY_SEPARATOR ) !== false && ( is_file( $name ) || is_file( $name . '.php' ) ) ) {
			if ( is_file( $name ) ) {
				$path = $name;
			} else {
				$path = $name . '.php';
			}
		} else {
			$path = WYT_COMPONENT_VIEWS_DIR . "/$name.php";
		}
		if ( ! is_file( $path ) ) {
			ob_end_clean();

			return __( 'Template not found', 'instagram-slider-widget' );
		}

		extract( $args );
		include $path;

		return ob_get_clean();
	}
}