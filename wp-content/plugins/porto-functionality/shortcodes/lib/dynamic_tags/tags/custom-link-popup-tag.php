<?php
/**
 * Porto Dynamic Popup Builder Link Tags class
 *
 * @author     P-THEMES
 * @version    3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Plugin;

class Porto_El_Custom_Link_Popup_Tag extends Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'porto-custom-link-popup';
	}

	public function get_title() {
		return esc_html__( 'Popup', 'porto-functionality' );
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

	protected function register_advanced_section() {}

	protected function register_controls() {
		$this->add_control(
			'dynamic_link_popup',
			array(
				'label'       => esc_html__( 'Popup ID', 'porto-functionality' ),
				'type'        => 'porto_ajaxselect2',
				'options'     => 'porto_builder_popup',
				'label_block' => true,
			)
		);
	}

	public function render() {
		$atts = $this->get_settings();

		if ( ! empty( $atts['dynamic_link_popup'] ) ) {
			global $porto_popup_template_ids;
			if ( ! isset( $porto_popup_template_ids ) ) {
				$porto_popup_template_ids = array();
				add_action( 'wp_footer', array( $this, 'print_script_template' ) );
			}
			$href = '#porto-action_popup-id-' . $atts['dynamic_link_popup'];
			$porto_popup_template_ids[] = $atts['dynamic_link_popup'];

			echo porto_filter_output( $href );
		}
	}

	public function print_script_template() {

		global $porto_popup_template_ids;

		foreach ( $porto_popup_template_ids as $popup ) {
			echo '<script type="text/template" id="popup-id-'. $popup . '">';

			$popup_options = get_post_meta( $popup, 'popup_options', true );
			$style = '';
			if ( ! ( empty( $popup_options ) && empty( get_post_meta( $popup, 'popup_animation', true ) ) ) ) {
				if ( empty( $popup_options ) ) {
					$popup_options = array(
						'horizontal' => 50,
						'vertical'   => 50,
					);
					if ( ! empty( get_post_meta( $popup, 'popup_animation', true ) ) ) {
						$popup_options['animation'] = get_post_meta( $popup, 'popup_animation', true );
					}
					if ( ! empty( get_post_meta( $popup, 'popup_width', true ) ) ) {
						$popup_options['width'] = (int) get_post_meta( $popup, 'popup_width', true );
					}
					if ( ! empty( get_post_meta( $popup, 'disable_overlay', true ) ) ) {
						$popup_options['disable_overlay'] = get_post_meta( $popup, 'disable_overlay', true );
					}
					if ( ! empty( get_post_meta( $popup, 'popup_type', true ) ) ) {
						$popup_options['popup_type'] = get_post_meta( $popup, 'popup_type', true );
					}
					if ( ! empty( get_post_meta( $popup, 'popup_offcanvas_pos', true ) ) ) {
						$popup_options['popup_offcanvas_pos'] = get_post_meta( $popup, 'popup_offcanvas_pos', true );
					}
				}

				if ( empty( $popup_options['builder'] ) ) {

					$style .= 'width: calc(100% - ' . ( empty( $porto_settings['grid-gutter-width'] ) ? '30' : (int) $porto_settings['grid-gutter-width'] ) . 'px); max-width: ' . (int) $popup_options['width'] . 'px; ';

					if ( is_rtl() ) {
						$left  = 'right';
						$right = 'left';
					} else {
						$left  = 'left';
						$right = 'right';
					}

					if ( ! empty( $popup_options['popup_type'] ) ) { // Off-Canvas
						if ( ! empty( $popup_options['popup_offcanvas_pos'] ) ) { // right
							$style .= $right . ': 0; top: 0; bottom: 0;';
						} else {
							$style .= $left . ': 0; top: 0; bottom: 0;';
						}
					} else {
						if ( 50 === (int) $popup_options['horizontal'] ) {
							if ( 50 === (int) $popup_options['vertical'] ) {
								$style .= 'left: 50%;top: 50%;transform: translate(-50%, -50%);';
							} else {
								$style .= 'left: 50%;transform: translateX(-50%);';
							}
						} elseif ( 50 > (int) $popup_options['horizontal'] ) {
							$style .= $left . ':' . $popup_options['horizontal'] . '%;';
						} else {
							$style .= $right . ':' . ( 100 - $popup_options['horizontal'] ) . '%;';
						}
						if ( 50 === (int) $popup_options['vertical'] ) {
							if ( 50 !== (int) $popup_options['horizontal'] ) {
								$style .= 'top: 50%;transform: translateY(-50%);';
							}
						} elseif ( 50 > (int) $popup_options['vertical'] ) {
							$style .= 'top:' . $popup_options['vertical'] . '%;';
						} else {
							$style .= 'bottom:' . ( 100 - $popup_options['vertical'] ) . '%;';
						}
					}
				}
			}

			$popupClass = ( empty( $popup_options['builder'] ) ? 'position-absolute' : '' );
			if ( ! empty( $popup_options['disable_overlay'] ) )  {
				$popupClass = 'position-fixed';
			}
			$overlayClass = $popup_options['animation'];
			if ( ! empty( $popup_options['popup_type'] ) ) {
				if ( ! empty( $popup_options['popup_offcanvas_pos'] ) ) { // right
					$overlayClass = 'my-mfp-slide-' . $left;
				} else {
					$overlayClass = 'my-mfp-slide-' . $right;
				}
			}

			echo '<div class="' . ( ! empty( $popup_options['popup_type'] ) ? 'zoom-anim-dialog ' : '' ) . $popupClass . '"' . ( ! empty( $popup_options['disable_overlay' ] ) ? ' data-disable-overlay="true"' : '' ) . ' data-popup-id="' . esc_attr( $popup ) . '" data-overlay-class="' . esc_attr( $overlayClass ) . '"' . ' style="' . $style . '" >';
			echo do_shortcode( '[porto_block ' . ( ! empty( $popup_options['popup_type'] ) ? '' : 'el_class="zoom-anim-dialog"' ) . ' id="' . intval( $popup ) . '" tracking="layout-popup-' . intval( $popup ) . '"]' );
			echo '</div>';
			echo '</script>';
			
		}
		unset( $GLOBALS['porto_popup_template_ids'] );
	}
}
