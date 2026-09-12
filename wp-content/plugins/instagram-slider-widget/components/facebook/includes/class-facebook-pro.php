<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIS_Facebook_Pro {

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
		    add_filter( 'wis/facebook/mob_settings', [ $this, 'settings_facebook' ], 10, 6 );
        }
    }

    public function settings_facebook( $content, $_this, $instance, $sliders, $options_linkto, $w_id ) {
		return WIS_Page::instance()->render( WFB_COMPONENT_VIEWS_DIR . '/form-feed-mobile', [
			'_this'          => $_this,
			'instance'       => $instance,
			'sliders'        => $sliders,
			'options_linkto' => $options_linkto,
			'w_id'           => $w_id,
		] );
	}
}