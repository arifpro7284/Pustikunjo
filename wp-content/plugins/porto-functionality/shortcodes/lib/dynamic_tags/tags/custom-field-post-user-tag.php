<?php
/**
 * Porto Dynamic Post Author Field Tags class
 *
 * @author     P-THEMES
 * @version    2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Plugin;
class Porto_El_Custom_Field_Post_User_Tag extends Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'porto-custom-field-post-user';
	}

	public function get_title() {
		return esc_html__( 'Post / Author', 'porto-functionality' );
	}

	public function get_group() {
		return Porto_El_Dynamic_Tags::PORTO_GROUP;
	}

	public function get_categories() {
		return array(
			Porto_El_Dynamic_Tags::TEXT_CATEGORY,
			Porto_El_Dynamic_Tags::NUMBER_CATEGORY,
			Porto_El_Dynamic_Tags::POST_META_CATEGORY,
			Porto_El_Dynamic_Tags::COLOR_CATEGORY,
		);
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls() {
		$this->add_control(
			'dynamic_field_post_object',
			array(
				'label'   => esc_html__( 'Object Field', 'porto-functionality' ),
				'type'    => Elementor\Controls_Manager::SELECT,
				'default' => 'post_title',
				'groups'  => Porto_Func_Dynamic_Tags_Content::get_instance()->get_dynamic_post_object_fields(),
			)
		);
		$this->add_control(
			'date_format',
			array(
				'label'       => esc_html__( 'Date Format', 'porto-functionality' ),
				'description' => esc_html__( 'j = 1-31, F = January-December, M = Jan-Dec, m = 01-12, n = 1-12', 'porto-functionality' ),
				'type'        => Elementor\Controls_Manager::TEXT,
				'condition'   => array(
					'dynamic_field_post_object' => array( 'post_date', 'post_modified' ),
				),
			)
		);
	}

	public function render() {

		do_action( 'porto_dynamic_before_render' );
		$post_id     = get_the_ID();
		$atts        = $this->get_settings();
		$property    = $atts['dynamic_field_post_object'];
		$date_format = ! empty( $atts['date_format'] ) ? $atts['date_format'] : '';
		$ret         = (string) Porto_Func_Dynamic_Tags_Content::get_instance()->get_dynamic_post_field_prop( $property, $date_format );
		if ( 'post_content' === $property ) {
			add_filter( 'porto_already_start_before', '__return_true' );
			if ( ! empty( $post_id ) && Plugin::$instance->documents->get_doc_for_frontend( $post_id )->is_built_with_elementor() ) {
				static $did_posts = [];
				static $level = 0;
				// Avoid recursion
				if ( isset( $did_posts[ $post_id ] ) ) {
					remove_filter( 'porto_already_start_before', '__return_true' );
					do_action( 'porto_dynamic_after_render' );
					return;
				}

				$level++;
				$did_posts[ $post_id ] = true;
				// End avoid recursion

				$editor       = Plugin::$instance->editor;
				$is_edit_mode = $editor->is_edit_mode();
				$editor->set_edit_mode( false );
				$ret          = apply_filters( 'the_content', Plugin::$instance->frontend->get_builder_content( $post_id, $is_edit_mode ) );
				$editor->set_edit_mode( $is_edit_mode );
				echo balanceTags( $ret, true );
				remove_filter( 'porto_already_start_before', '__return_true' );
				do_action( 'porto_dynamic_after_render' );

				$level--;

				if ( 0 === $level ) {
					$did_posts = [];
				}
				return;
			} else {
				$ret = apply_filters( 'the_content', $ret );
			}
			remove_filter( 'porto_already_start_before', '__return_true' );
		}
		$ret = Porto_Func_Dynamic_Tags_Content::get_instance()->get_dynamic_post_field( $ret );
		echo porto_filter_output( $ret );
		do_action( 'porto_dynamic_after_render' );
	}
}
