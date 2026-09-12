<?php
/**
 * Porto Elementor Single Builder Post Content Widget
 *
 * Renders the viewed post's own content inside a Single ( Post / Portfolio ) builder template.
 * When the post is built with Elementor it outputs the post's Elementor layout via
 * get_builder_content() ( which bypasses the_content and its kses filtering, so inline builder
 * <style> tags survive ), otherwise it falls back to the_content().
 *
 * @author     P-THEMES
 * @since      3.9.0
 */
defined( 'ABSPATH' ) || die;

use Elementor\Controls_Manager;

class Porto_Elementor_Single_Post_Content_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'porto_single_post_content';
	}

	public function get_title() {
		return esc_html__( 'Post Content', 'porto-functionality' );
	}

	public function get_icon() {
		return 'fas fa-align-left porto-elementor-widget-icon';
	}

	public function get_categories() {
		return array( 'porto-single' );
	}

	public function get_keywords() {
		return array( 'single', 'post', 'content', 'the content', 'body', 'text', 'portfolio' );
	}

	public function get_custom_help_url() {
		return 'https://www.portotheme.com/wordpress/porto/documentation/single-builder-elements/';
	}

	public function has_widget_inner_wrapper(): bool {
		return ! Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_single_post_content',
			array(
				'label' => __( 'Post Content', 'porto-functionality' ),
				'tab'   => Controls_Manager::TAB_LAYOUT,
			)
		);

		$this->add_control(
			'description_content',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'This widget outputs the post content and is only meaningful on a Single Post / Portfolio. It has no options.', 'porto-functionality' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$single = PortoBuildersSingle::get_instance();

		// Set up the previewed / current post: a no-op on the real single-post frontend ( the loop
		// already provides $post ), and a sample post inside the builder editor. Matches the other
		// Porto single-builder elements.
		$single->restore_global_single_variable();

		$post_id = get_the_ID();
		// Never render a porto_builder post's own content here ( it would recurse into the very
		// template that is being rendered ).
		if ( $post_id && ( ! class_exists( 'PortoBuilders' ) || PortoBuilders::BUILDER_SLUG !== get_post_type( $post_id ) ) ) {
			$rendered = false;

			if ( class_exists( '\Elementor\Plugin' ) ) {
				$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( $post_id );
				if ( $document && $document->is_built_with_elementor() ) {
					// Render the post's own Elementor layout via get_builder_content(), which bypasses
					// the_content ( and its kses filtering, so inline builder <style> survives ). Force
					// edit mode off so the builder editor does not recurse into itself.
					$editor       = \Elementor\Plugin::$instance->editor;
					$is_edit_mode = $editor->is_edit_mode();
					$editor->set_edit_mode( false );
					$content = \Elementor\Plugin::$instance->frontend->get_builder_content( $post_id, false );
					$editor->set_edit_mode( $is_edit_mode );
					if ( $content ) {
						echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$rendered = true;
					}
				}
			}

			if ( ! $rendered ) {
				the_content();
			}

			wp_link_pages(
				array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'porto-functionality' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
				)
			);
		}

		$single->reset_global_single_variable();
	}
}
