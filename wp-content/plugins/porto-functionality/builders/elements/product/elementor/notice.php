<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Porto Elementor Custom Product Notice Widget
 *
 * @since 3.5.0
 */

use Elementor\Controls_Manager;

class Porto_Elementor_CP_Notice_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'porto_cp_notice';
	}

	public function get_title() {
		return __( 'Product Notice', 'porto-functionality' );
	}

	public function get_categories() {
		return array( 'custom-product' );
	}

	public function get_keywords() {
		return array( 'woocommerce notices', 'woocommerce alert' );
	}

	public function get_icon() {
		return 'eicon-woocommerce-notices porto-elementor-widget-icon';
	}

	public function get_custom_help_url() {
		return 'https://www.portotheme.com/wordpress/porto/documentation/single-product-builder-elements/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}
	
	protected function register_controls() {
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( class_exists( 'PortoCustomProduct' ) ) {
			$settings['page_builder'] = 'elementor';
			echo PortoCustomProduct::get_instance()->shortcode_single_product_notice( $settings );
		}
	}
}
