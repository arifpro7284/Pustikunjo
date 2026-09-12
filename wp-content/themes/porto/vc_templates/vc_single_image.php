<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $source
 * @var $image
 * @var $custom_src
 * @var $onclick
 * @var $img_size
 * @var $external_img_size
 * @var $caption
 * @var $img_link_large
 * @var $link
 * @var $img_link_target
 * @var $alignment
 * @var $el_class
 * @var $css_animation
 * @var $style
 * @var $external_style
 * @var $border_color
 * @var $css
 *
 * Extra Params
 * @var $lightbox
 * @var $image_gallery
 * @var $container_class
 * @var $zoom_icon
 * @var $hover_effect
 *
 * Shortcode class
 * @var $this WPBakeryShortCode_VC_Single_image
 */
$init_atts = $atts;
$source = $add_caption = $css_animation = $animation_type = $animation_delay = $animation_duration = '';
/**
 * WPBakery 9.0 dropped the `img_link_target` param ( the target moved into the `link` param )
 * and turned the border colors into colorpickers, so both are initialized for older versions.
 */
$img_link_target = $external_border_color = $border_color = '';
$to_slide_item = false;
$slider_selector = $item_order = '';
// dynamic image
$enable_image_dynamic   = false;
$image_dynamic_source   = '';
$image_dynamic_content  = '';
$image_dynamic_fallback = '';

$original_atts = $atts;
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
//dynamic text
$dynamic_image = false;
if ( $enable_image_dynamic ) {
	if ( ! empty( $image_dynamic_content ) ) {
		$image = Porto_Func_Dynamic_Tags_Content::get_instance()->dynamic_get_data( $image_dynamic_source, $image_dynamic_content, 'image' );
		if ( is_string( $image ) ) {
			$image = array(
				'id' => attachment_url_to_postid( $image ),
			);
		}
	}
	if ( empty( $image['id'] ) && ! empty( $image_dynamic_fallback ) ) {
		$image = array( 'id' => $image_dynamic_fallback );
	}
	$dynamic_image = true;
}

$default_src = vc_asset_url( 'vc/no_image.png' );
if ( isset( $image['id'] ) && 'dynamic_url' == $image['id'] ) {
	$default_src = $image['url'];
	$image['id'] = '';
}
// backward compatibility. since 4.6
if ( empty( $onclick ) && isset( $img_link_large ) && 'yes' === $img_link_large ) {
	$onclick = 'img_link_large';
} elseif ( empty( $atts['onclick'] ) && ( ! isset( $atts['img_link_large'] ) || 'yes' !== $atts['img_link_large'] ) ) {
	$onclick = 'custom_link';
}

if ( 'external_link' === $source ) {
	$style        = $external_style;
	$border_color = $external_border_color;
}

/**
 * Border color.
 *
 * Up to WPBakery 8.x this was a dropdown of named colors ( -> vc_box_border_{name} class ), since
 * 9.0 it is a colorpicker, so a css color is applied inline instead - on the border for the
 * outline styles and on the background for the box border ones.
 */
$border_color_style = '';
$is_outline_style   = 0 === strpos( $style, 'vc_box_outline' );
$is_border_style    = $is_outline_style || 0 === strpos( $style, 'vc_box_border' );
if ( ! $is_border_style ) {
	$border_color = '';
} elseif ( '' !== $border_color ) {
	if ( preg_match( '/^(#|rgb|hsl)/i', $border_color ) ) {
		$border_color_style = ( $is_outline_style ? 'border-color:' : 'background-color:' ) . $border_color . ';';
		$border_color       = '';
	} else {
		$border_color = ' vc_box_border_' . $border_color;
	}
}

$img = false;

switch ( $source ) {
	case 'media_library':
		/**
		 * Since WPBakery 9.0 the attach_image param can store the image together with its link
		 * as json ( {"{attach_id}":{"url":"...","target":"_blank"}} ), older versions store the
		 * attachment id only.
		 */
		if ( is_string( $image ) && 0 === strpos( ltrim( $image ), '{' ) ) {
			$image_data = json_decode( $image, true );
			if ( is_array( $image_data ) && ! empty( $image_data ) ) {
				foreach ( $image_data as $attach_id => $link_data ) {
					$image = $attach_id;
					if ( is_array( $link_data ) && ! empty( $link_data ) ) {
						$parts = array();
						foreach ( $link_data as $link_key => $link_value ) {
							$parts[] = $link_key . ':' . rawurlencode( $link_value );
						}
						$link = implode( '|', $parts );
					}
					break;
				}
			}
		}

		// no break here, the featured image is processed the same way.
	case 'featured_image':
		if ( 'featured_image' === $source ) {
			$post_id = get_the_ID();
			if ( $post_id && has_post_thumbnail( $post_id ) ) {
				$img_id = get_post_thumbnail_id( $post_id );
			} else {
				$img_id = 0;
			}
		} else {
			if ( $dynamic_image && ! empty( $image ) && isset( $image['id'] ) ) {
				$img_id = $image['id'];
				$upload_dir = wp_upload_dir();
				if ( ! empty( $upload_dir ) && isset( $upload_dir['baseurl'] ) ) {
					$upload_dir = $upload_dir['baseurl'];
					$img_id = str_replace( 'https://sw-themes.com/porto_dummy/wp-content/uploads', $upload_dir, $img_id );
					$img_id = str_replace( 'http://sw-themes.com/porto_dummy/wp-content/uploads', $upload_dir, $img_id );
				}

				// If id is image link
				if ( false !== strpos( $img_id, 'http' ) ) {
					$img_id = attachment_url_to_postid( $img_id );
				}
			} else {
				$img_id = preg_replace( '/[^\d]/', '', $image );
			}
		}

		// set rectangular
		if ( preg_match( '/_circle_2$/', $style ) ) {
			$style    = preg_replace( '/_circle_2$/', '_circle', $style );
			$img_size = $this->getImageSquareSize( $img_id, $img_size );
		}

		if ( ! $img_size ) {
			$img_size = 'medium';
		}

		if ( get_post( apply_filters( 'wpml_object_id', $img_id, 'attachment', true ) ) ) {
			$img = wpb_getImageBySize(
				array(
					'attach_id'  => $img_id,
					'thumb_size' => strtolower( $img_size ),
					'class'      => 'vc_single_image-img' . ( false !== strpos( $el_class, 'porto-skip-lz' ) ? ' porto-skip-lz' : '' ),
				)
			);
		}

		if ( isset( $img['thumbnail'] ) && false !== strpos( $img['thumbnail'], 'width="0" height="0"' ) ) {
			$img_size_arr = explode( 'x', $img_size );
			if ( 2 === count( $img_size_arr ) && is_numeric( $img_size_arr[0] ) && is_numeric( $img_size_arr[1] ) ) {
				$img['thumbnail'] = str_replace( 'width="0" height="0"', 'width="' . absint( $img_size_arr[0] ) . '" height="' . absint( $img_size_arr[1] ) . '"', $img['thumbnail'] );
			}
		}

		// don't show placeholder in public version if post doesn't have featured image
		if ( 'featured_image' === $source ) {
			if ( ! $img && 'page' === vc_manager()->mode() ) {
				return;
			}
		}

		break;

	case 'external_link':
		$dimensions = function_exists( 'vc_extract_dimensions' ) ? vc_extract_dimensions( $external_img_size ) : vcExtractDimensions( $external_img_size );
		$hwstring   = $dimensions ? image_hwstring( $dimensions[0], $dimensions[1] ) : '';

		$custom_src = $custom_src ? $custom_src : $default_src;

		// lazy loading of the external image, added in WPBakery 9.0.
		$loading_attr = '';
		if ( function_exists( 'vc_add_lazy_loading_attribute' ) ) {
			$lazy_attributes = vc_add_lazy_loading_attribute( array() );
			if ( ! empty( $lazy_attributes['loading'] ) ) {
				$loading_attr = ' loading="' . esc_attr( $lazy_attributes['loading'] ) . '"';
			}
		}

		$img = array(
			'thumbnail' => '<img class="vc_single_image-img" ' . $hwstring . $loading_attr . ' src="' . esc_url( $custom_src ) . '" alt="external" />',
		);
		break;

	default:
		$img = false;
}

if ( ! $img ) {
	if ( false === $img ) {
		$img = array();
	}
	if ( $dynamic_image ) {
		$img['thumbnail'] = '';
	} else {
		$attr_width = '';
		if ( ! empty( $img_size ) && $img_size != 'full' ) {
			$img_size_arr = explode( 'x', $img_size );
			if ( empty( $img_size_arr[1] ) ) {
				$img_size_arr[0] = (int) get_option( $img_size . '_size_w' );
				$img_size_arr[1] = (int) get_option( $img_size . '_size_h' );
			}
			$attr_width = ' width="' . $img_size_arr[0] . '" height="' . $img_size_arr[1] . '"';
		}
		$img['thumbnail'] = '<img class="' . ( $img_id ? '' : 'vc_img-placeholder' ) . ' vc_single_image-img" src="' . esc_url( $default_src ) . '" alt="placeholder image"' . $attr_width . ' />';
	}
}

$el_class = $this->getExtraClass( $el_class );

// backward compatibility
if ( porto_has_class( 'prettyphoto', $el_class ) ) {
	$onclick = 'link_image';
}

// backward compatibility. will be removed in 4.7+
if ( ! empty( $atts['img_link'] ) ) {
	$link = $atts['img_link'];
	if ( ! preg_match( '/^(https?\:\/\/|\/\/)/', $link ) ) {
		$link = 'http://' . $link;
	}
}

// backward compatibility
if ( in_array( $link, array( 'none', 'link_no' ), true ) ) {
	$link = '';
}

$a_attrs = array();

switch ( $onclick ) {
	case 'img_link_large':
		if ( 'external_link' === $source ) {
			$link = $custom_src;
		} else {
			$link = wp_get_attachment_image_src( $img_id, 'large' );
			if ( isset( $link[0] ) ) {
				$link = $link[0];
			}
		}

		break;

	case 'link_image':
		wp_enqueue_script( 'prettyphoto' );
		wp_enqueue_style( 'prettyphoto' );

		$a_attrs['class']    = 'prettyphoto';
		$a_attrs['data-rel'] = 'prettyPhoto[rel-' . get_the_ID() . '-' . wp_rand() . ']';

		// backward compatibility
		if ( ! porto_has_class( 'prettyphoto', $el_class ) && 'external_link' === $source ) {
			$link = $custom_src;
		} elseif ( ! porto_has_class( 'prettyphoto', $el_class ) ) {
			$link = wp_get_attachment_image_src( $img_id, 'large' );
			$link = $link[0];
		}

		break;

	case 'custom_link':
		// $link is already defined
		break;

	case 'zoom':
		wp_enqueue_script( 'vc_image_zoom' );

		if ( 'external_link' === $source ) {
			$large_img_src = $custom_src;
		} else {
			$large_img_src = wp_get_attachment_image_src( $img_id, 'large' );
			if ( $large_img_src ) {
				$large_img_src = $large_img_src[0];
			}
		}

		$img['thumbnail'] = str_replace( '<img ', '<img data-vc-zoom="' . esc_url( $large_img_src ) . '" ', $img['thumbnail'] );

		break;
}

// backward compatibility
if ( porto_has_class( 'prettyphoto', $el_class ) ) {
	$el_class = vc_remove_class( 'prettyphoto', $el_class );
}

$html = ( 'vc_box_shadow_3d' === $style ) ? '<span class="vc_box_shadow_3d_wrap">' . $img['thumbnail'] . '</span>' : $img['thumbnail'];

if ( in_array( $source, array( 'media_library', 'featured_image' ), true ) && 'yes' === $add_caption ) {
	$post    = get_post( $img_id );
	$caption = $post->post_excerpt;
} elseif ( 'external_link' === $source ) {
	$add_caption = 'yes';
}

/* porto lightbox */
if ( $lightbox && 'img_link_large' == $onclick ) {
	wp_enqueue_script( 'porto-vc-zoom' );
	if ( $hover_effect ) {
		$a_attrs['class'] = 'porto-vc-zoom porto-vc-zoom-hover-icon';
	} else {
		$a_attrs['class'] = 'porto-vc-zoom';
	}
	$a_attrs['data-gallery'] = $image_gallery ? 'true' : 'false';
	$a_attrs['data-title']   = $caption;
	if ( $image_gallery && 'vc_row' != $container_class ) {
		$a_attrs['data-container'] = $container_class;
	}
	if ( ! $hover_effect && $zoom_icon ) {
		$html .= '<div class="zoom-icon"></div>';
	}
}

$wrapper_style = $border_color_style;
if ( ! empty( $img_width ) ) {
	$img_width_unit = json_decode( $img_width, true );
	if ( ! empty( $img_width_unit['unit'] ) && '%' == $img_width_unit['unit'] ) {
		$wrapper_style .= 'width: 100%;';
	}
}
$inline_style = $wrapper_style ? ' style="' . esc_attr( $wrapper_style ) . '"' : '';

$html = '<div class="vc_single_image-wrapper ' . esc_attr( $style ) . ' ' . esc_attr( $border_color ) . '"' . $inline_style . '>' . $html . '</div>';

if ( $link ) {
	/**
	 * Since WPBakery 9.0 the image link is a link param ( url:...|target:...|rel:...|title:... ),
	 * older versions store a plain url plus the separate img_link_target param.
	 */
	if ( function_exists( 'vc_build_link' ) && preg_match( '/(^|\|)(url|target|rel|title):/', $link ) ) {
		$wpb_link = vc_build_link( $link );
		$link     = empty( $wpb_link['url'] ) ? '' : $wpb_link['url'];
		if ( ! empty( $wpb_link['target'] ) ) {
			$img_link_target = trim( $wpb_link['target'] );
		}
		if ( ! empty( $wpb_link['rel'] ) ) {
			$a_attrs['rel'] = trim( $wpb_link['rel'] );
		}
		if ( ! empty( $wpb_link['title'] ) ) {
			$a_attrs['title'] = trim( $wpb_link['title'] );
		}
	}

	/**
	 * The img_link_target param is not mapped anymore since 9.0, so it is read from the raw
	 * shortcode attributes to keep the target of the links saved with the older versions.
	 */
	if ( ! $img_link_target && ! empty( $init_atts['img_link_target'] ) ) {
		$img_link_target = $init_atts['img_link_target'];
	}

	if ( $link ) {
		$a_attrs['href'] = esc_url( $link );
		if ( $img_link_target ) {
			$a_attrs['target'] = $img_link_target;
		}
		$a_attrs['aria-label'] = __( 'Zoom the image', 'porto' );
		$html                  = '<a ' . porto_stringify_attributes( $a_attrs ) . '>' . $html . '</a>';
	}
}

$settings = $this->getSettings();
$element_class = empty( $settings['element_default_class'] ) ? '' : $settings['element_default_class'];
$class_to_filter  = 'wpb_single_image wpb_content_element vc_align_' . $alignment . ( $element_class ? ' ' . trim( $element_class ) : '' ) . ' ' . $this->getCSSAnimation( $css_animation );
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class );
$css_class        = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $settings['base'], $atts );

$wrapper_attributes = '';
if ( $animation_type ) {
	$wrapper_attributes .= ' data-appear-animation="' . esc_attr( $animation_type ) . '"';
	if ( $animation_delay ) {
		$wrapper_attributes .= ' data-appear-animation-delay="' . esc_attr( $animation_delay ) . '"';
	}
	if ( $animation_duration && 1000 != $animation_duration ) {
		$wrapper_attributes .= ' data-appear-animation-duration="' . esc_attr( $animation_duration ) . '"';
	}
	if ( false !== strpos( $animation_type, 'revealDir' ) ) {
		$wrapper_attributes .= ' data-animation-reveal-clr="' . ( ! empty( $animation_reveal_clr ) ? esc_attr( $animation_reveal_clr ) : '' ) . '"';
	}
} elseif ( ! empty( $atts ) && function_exists( 'porto_shortcode_add_floating_options' ) ) {
	$floating_option = porto_shortcode_add_floating_options( $atts );
	if ( ! empty( $floating_option ) ) {
		$wrapper_attributes .= $floating_option;
	}
}

if ( class_exists( 'WPBMap' ) ) {
	$sc = WPBMap::getShortCode( 'vc_single_image' );
	if ( ! empty( $sc['params'] ) && class_exists( 'PortoShortcodesClass' ) && method_exists( 'PortoShortcodesClass', 'get_global_hashcode' ) ) {

		foreach ( $original_atts as $key => $item ) {
			if ( in_array( $key, array( 'img_width', 'img_mwidth', 'img_height', 'img_mheight', 'img_object', 'img_pos' ) ) ) {
				$original_atts[ $key ] = str_replace( '"', '``', $item );
			}
		}
		$shortcode_class = 'wpb_custom_' . PortoShortcodesClass::get_global_hashcode( $original_atts, 'vc_single_image', $sc['params'] );
		if ( empty( $css_class ) ) {
			$css_class = $shortcode_class;
		} else {
			$css_class .= ' ' . $shortcode_class;
		}
		$internal_css = PortoShortcodesClass::generate_wpb_css( 'vc_single_image', $original_atts );
	}
}

// Integrate with Carousel
if ( 'yes' == $to_slide_item ) {
	if ( '' !== $slider_selector && '' !== $item_order ) {
		wp_enqueue_script( 'porto-focus-slider' );
		$wrapper_attributes .= ' data-focus-slider="' . esc_attr( json_encode( array( 'selector' => $slider_selector, 'order' => $item_order - 1 ) ) ) . '"';
		$css_class .= ' porto-focus-slider';
	}
}

if ( 'yes' === $add_caption && '' !== $caption ) {
	$html = '
		<figure class="vc_figure">
			' . $html . '
			<figcaption class="vc_figure-caption">' . esc_html( $caption ) . '</figcaption>
		</figure>
	';
}

$output = '
	<div class="' . esc_attr( trim( $css_class ) ) . '"' . $wrapper_attributes . '>' .	
	( ! empty( $internal_css ) ? '<style>' . wp_strip_all_tags( $internal_css ) . '</style>' : '' )	. 
	'<div class="wpb_wrapper">
			' . wpb_widget_title(
	array(
		'title'      => $title,
		'extraclass' => 'wpb_singleimage_heading',
	)
) . '
			' . $html . '
		</div>
	</div>
';

echo porto_filter_output( $output );
