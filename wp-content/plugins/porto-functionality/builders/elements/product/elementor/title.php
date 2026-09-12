<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Porto Elementor Custom Product Title Widget
 *
 * Porto Elementor widget to display product title on the single product page when using custom product layout
 *
 * @since 1.7.1
 */

use Elementor\Controls_Manager;

class Porto_Elementor_CP_Title_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'porto_cp_title';
	}

	public function get_title() {
		return __( 'Product Title', 'porto-functionality' );
	}

	public function get_categories() {
		return array( 'custom-product' );
	}

	public function get_keywords() {
		return array( 'heading', 'single', 'name' );
	}

	public function get_icon() {
		return 'eicon-product-title porto-elementor-widget-icon';
	}

	public function get_custom_help_url() {
		return 'https://www.portotheme.com/wordpress/porto/documentation/single-product-builder-elements/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}
	
	protected function register_controls() {

		$this->start_controls_section(
			'section_cp_title',
			array(
				'label' => __( 'Product Title', 'porto-functionality' ),
			)
		);

		$this->add_control(
			'heading_tag',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => __( 'Title Tag', 'porto-functionality' ),
				'options'     => array(
					'h2'  => __( 'Default', 'porto-functionality' ),
					'h1'  => __( 'H1', 'porto-functionality' ),
					'h3'  => __( 'H3', 'porto-functionality' ),
					'h4'  => __( 'H4', 'porto-functionality' ),
					'h5'  => __( 'H5', 'porto-functionality' ),
					'h6'  => __( 'H6', 'porto-functionality' ),
					'div' => __( 'DIV', 'porto-functionality' ),
					'p'   => __( 'P tag', 'porto-functionality' ),
				),
				'default'     => 'h2',
				'description' => __( 'Default is H2', 'porto-functionality' ),
			)
		);

		$this->add_group_control(
			Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_font',
				'label'    => __( 'Typography', 'porto-functionality' ),
				'selector' => '{{WRAPPER}} .product_title',
			)
		);

		$this->add_control(
			'title_font_color',
			array(
				'type'      => Controls_Manager::COLOR,
				'label'     => __( 'Color', 'porto-functionality' ),
				'selectors' => array(
					'{{WRAPPER}} .product_title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( class_exists( 'PortoCustomProduct' ) ) {
			$settings['page_builder'] = 'elementor';
			echo PortoCustomProduct::get_instance()->shortcode_single_product_title( $settings );
		}
	}
}
