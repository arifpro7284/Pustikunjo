<?php
/**
 * Porto Dynamic Woo Link Tags class
 *
 * @author     P-THEMES
 * @version    3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Porto_El_Custom_Link_Woo_Tag extends Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'porto-custom-link-woo';
	}

	public function get_title() {
		return esc_html__( 'WooCommerce', 'porto-functionality' );
	}

	public function get_group() {
		return Porto_El_Dynamic_Tags::PORTO_GROUP;
	}

	public function get_categories() {
		return array(
			Porto_El_Dynamic_Tags::URL_CATEGORY,
		);
	}

	public function is_settings_required() {
		return true;
	}

	/**
	 * remove Fallback function
	 *
	 */
	public function register_advanced_section() {
		parent::register_advanced_section();

		$this->remove_control( 'before' );
		$this->remove_control( 'after' );
	}

	protected function register_controls() {
		$this->add_control(
			'dynamic_link_woo_object',
			array(
				'label'   => esc_html__( 'Object Link', 'porto-functionality' ),
				'type'    => Elementor\Controls_Manager::SELECT,
				'groups'  => Porto_Func_Dynamic_Tags_Content::get_instance()->get_dynamic_woo_object_links(),
			)
		);
	}

	public function render() {

		do_action( 'porto_dynamic_before_render' );

		$atts     = $this->get_settings();
		$property = $atts['dynamic_link_woo_object'];
		$ret      = (string) Porto_Func_Dynamic_Tags_Content::get_instance()->get_dynamic_woo_link( $property );
		echo porto_strip_script_tags( $ret );
		do_action( 'porto_dynamic_after_render' );
	}

}
