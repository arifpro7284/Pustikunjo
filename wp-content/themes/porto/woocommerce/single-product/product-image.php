<?php
/**
 * Single Product Image
 *
 * @version     11.1.0
 */

use Automattic\WooCommerce\Internal\ProductGallery\ProductMediaGallery;

defined( 'ABSPATH' ) || exit;

global $post, $woocommerce, $product, $porto_settings, $porto_settings_optimize, $porto_product_layout, $porto_product_info, $porto_scatted_layout;
$attachment_ids = $product->get_gallery_image_ids();

if ( ! isset( $porto_product_layout ) && ( ! wp_doing_ajax() || ! isset( $_REQUEST['action'] ) || 'porto_product_quickview' != $_REQUEST['action'] ) ) {
		$porto_product_layout = get_post_meta( get_the_ID(), 'product_layout', true );
		if ( ! $porto_product_layout ) {
			$builder_id = porto_check_builder_condition( 'product' );
			if ( $builder_id ) {
				$porto_product_layout = 'builder';
			}
		}
		$porto_product_layout = ( ! $porto_product_layout && isset( $porto_settings['product-single-content-layout'] ) ) ? $porto_settings['product-single-content-layout'] : $porto_product_layout;
		if ( ! $porto_product_layout ) {
			$porto_product_layout = 'default';
		}
	}

$items_count            = 1;
$product_images_classes = '';
$product_image_classes  = 'img-thumbnail';
$product_images_attrs   = '';
$full_size              = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );

if ( 'extended' === $porto_product_layout ) {
	$items_count               = get_post_meta( get_the_ID(), 'product_layout_columns', true );
	$items_count               = ( ! $items_count && isset( $porto_settings['product-single-columns'] ) ) ? $porto_settings['product-single-columns'] : 3;
	if ( ! empty( $porto_product_info['items'] ) ) {
		$product_images_attrs .= ' data-items="' . $porto_product_info['items'] . '"';
	} else {
		$product_images_attrs .= ' data-items="3"';
	}
	if ( ! isset( $porto_product_info['center_mode'] ) || ! empty( $porto_product_info['center_mode'] ) ) {
		$product_images_attrs .= ' data-centeritem';
	}
	if ( ! empty( $porto_product_info['responsive'] ) ) {
		$product_images_attrs .= ' data-responsive="' . esc_attr( json_encode( $porto_product_info['responsive'] ) ) . '"';
	} else {
		$columns_responsive        = array();
		$columns_responsive['768'] = 3;
		$columns_responsive['0']   = 1;
		$product_images_attrs     .= ' data-responsive="' . esc_attr( json_encode( $columns_responsive ) ) . '"';
	}
	if ( isset( $porto_product_info['loop'] ) ) {
		$product_images_attrs .= ' data-loop="' . esc_attr( $porto_product_info['loop'] ) . '"';
	}
	if ( ! empty( $porto_product_info['margin'] ) ) {
		$product_images_attrs .= ' data-margin="' . esc_attr( $porto_product_info['margin'] ) . '"';
	}
}
if ( 'grid' === $porto_product_layout ) {
	$product_images_classes = 'product-images-block row';
	$items_count            = get_post_meta( get_the_ID(), 'product_layout_grid_columns', true );
	$items_count            = ( ! $items_count && isset( $porto_settings['product-single-columns'] ) ) ? $porto_settings['product-single-columns'] : 2;
	$items_count            = '2';
	if ( '1' === $items_count ) {
		$product_image_classes .= ' col-lg-12';
	} elseif ( '2' === $items_count ) {
		$product_image_classes .= ' col-sm-6';
	} elseif ( '3' === $items_count ) {
		$product_image_classes .= ' col-sm-6 col-lg-4';
	} elseif ( '4' === $items_count ) {
		$product_image_classes .= ' col-sm-6 col-lg-3';
	}
} elseif ( 'sticky_info' === $porto_product_layout || 'sticky_both_info' === $porto_product_layout ) {
	$product_images_classes = 'product-images-block';
} else {
	$product_images_classes = 'product-image-slider owl-carousel show-nav-hover';
	if ( 'extended' === $porto_product_layout ) {
		if ( ! empty( $porto_product_info['columns_class'] ) ) {
			$product_images_classes .= ' has-ccols-spacing ' . $porto_product_info['columns_class'];
		} else {
			$product_images_classes .= ' has-ccols ccols-1 ccols-md-3 has-ccols-spacing';
		}
		if ( ! empty( $porto_product_info['enable_flick'] ) ) {
			$product_images_classes .= ' flick-carousel';
		}
	} else {
		$product_images_classes .= ' has-ccols ccols-1';
	}
}
// for 360 degree view
$attach_gallery_ids = get_post_meta( $post->ID, 'porto_product_360_gallery', true );
$image_view_cls = 'image-galley-viewer';
if ( ! $porto_settings['product-image-popup'] || 'sticky_both_info' === $porto_product_layout ) {
	$image_view_cls .= ' without-zoom';
}

$post_thumbnail_id = method_exists( $product, 'get_image_id' ) ? $product->get_image_id() : get_post_thumbnail_id();

/**
 * WooCommerce 11.1 introduced a mixed media gallery (images and videos) which is exposed through
 * ProductMediaGallery. The helper returns the full gallery order, already deduplicated, with the
 * featured image first and the stored videos spliced in at their saved positions.
 * Older WooCommerce versions keep using the featured image + gallery image ids.
 */
$has_media_gallery = class_exists( ProductMediaGallery::class ) && method_exists( ProductMediaGallery::class, 'get_product_media_gallery_items_for_display' );
$has_video_gallery = $has_media_gallery && method_exists( ProductMediaGallery::class, 'get_gallery_video_html' );
$media_items       = $has_media_gallery ? ProductMediaGallery::get_product_media_gallery_items_for_display( $product ) : array();

if ( empty( $media_items ) ) {
	if ( $post_thumbnail_id ) {
		$media_items[] = array(
			'media_type'  => 'image',
			'source_type' => 'attachment',
			'id'          => $post_thumbnail_id,
		);
	}
	if ( $attachment_ids ) {
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment_id = absint( $attachment_id );
			if ( $attachment_id ) {
				$media_items[] = array(
					'media_type'  => 'image',
					'source_type' => 'attachment',
					'id'          => $attachment_id,
				);
			}
		}
	}
	if ( empty( $media_items ) ) {
		$media_items[] = array(
			'media_type'  => 'image',
			'source_type' => 'placeholder',
			'id'          => 0,
		);
	}
}

$has_media = 'placeholder' !== ( isset( $media_items[0]['source_type'] ) ? $media_items[0]['source_type'] : 'attachment' );

$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $has_media ? 'with-images' : 'without-images' ),
		'images',
	)
);

?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>">
	<div class="woocommerce-product-gallery__wrapper">
<div class="product-images images">
	<?php
	$html            = '<div class="' . esc_attr( $product_images_classes ) . '"' . $product_images_attrs . '>';
	
	$product_icon_cl = 'porto-icon-plus';
	if ( ! empty( $porto_product_info['icon_cl'] ) ) {
		$product_icon_cl = $porto_product_info['icon_cl'];
	}

	$size      = apply_filters( 'woocommerce_gallery_image_size', ( ! empty( $porto_scatted_layout ) || 'full_width' === $porto_product_layout ) ? 'full' : 'woocommerce_single' );
	$image_cls = $product_image_classes;
	// Videos are excluded from the zoom / lightbox by the theme scripts through the `vd-image` wrapper.
	$video_cls = trim( str_replace( 'img-thumbnail', 'vd-image', $product_image_classes ) );
	$index     = 0;

	foreach ( $media_items as $media_item ) {

		$media_id    = isset( $media_item['id'] ) ? absint( $media_item['id'] ) : 0;
		$media_type  = isset( $media_item['media_type'] ) ? $media_item['media_type'] : 'image';
		$source_type = isset( $media_item['source_type'] ) ? $media_item['source_type'] : 'attachment';
		$is_main     = ( 0 === $index ); // The first rendered item is the main gallery slide.
		$is_video    = ( 'video' === $media_type );

		if ( 'placeholder' === $source_type || ! $media_id ) {

			$image_link      = wc_placeholder_img_src( 'woocommerce_single' );
			// Check for visible children with prices to determine if variation image swapping is possible.
			// Using get_visible_children() + get_price() is more efficient than get_available_variations()
			// as it uses cached IDs and synced price data rather than loading all variation objects.
			$placeholder_cls = ( $product->is_type( 'variable' ) && ! empty( $product->get_visible_children() ) && '' !== $product->get_price() ?
					'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder' :
					'woocommerce-product-gallery__image--placeholder' ) . ' ' . $image_cls;
			$item_html       = '';
			$item_html      .= '<div class="' . esc_attr( $placeholder_cls ) . '"><div class="inner">';
			$item_html      .= '<img src="' . esc_url( $image_link ) . '" alt="' . esc_attr__( 'Awaiting product image', 'woocommerce' ) . '" data-large_image_width="600" data-large_image_height="600" href="' . esc_url( $image_link ) . '" class="woocommerce-main-image wp-post-image" />';
			$item_html      .= '</div></div>';
			$index ++;
			$html           .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', $item_html, '', '', 'large', $index );
			continue;
		}

		if ( $is_video ) {

			if ( ! $has_video_gallery ) {
				continue;
			}

			$video_html = ProductMediaGallery::get_gallery_video_html( $media_item, $is_main );
			if ( ! $video_html ) {
				continue;
			}

			$item_html = '<div class="' . esc_attr( $video_cls ) . '">' . $video_html . '</div>';
			$index ++;

			/**
			 * Filter product video thumbnail HTML string.
			 *
			 * @since 11.0.0
			 * @internal For exclusive usage of WooCommerce core, backwards compatibility not guaranteed.
			 *
			 * @param string $html          Product video thumbnail HTML string.
			 * @param int    $attachment_id Video attachment ID.
			 * @param array  $media_item    Product media gallery item.
			 */
			$html .= apply_filters( 'woocommerce_single_product_video_thumbnail_html', $item_html, $media_id, $media_item );
			continue;
		}

		$full_src = wp_get_attachment_image_src( $media_id, $full_size );
		if ( empty( $full_src[0] ) ) {
			continue;
		}

		$item_html  = '';
		$item_html .= '<div class="' . esc_attr( $image_cls ) . '"><div class="inner">';

		if ( $is_main ) {
			$item_html .= wp_get_attachment_image(
				$media_id,
				$size,
				false,
				apply_filters(
					'woocommerce_gallery_image_html_attachment_image_params',
					array(
						'href'                    => esc_url( $full_src[0] ),
						'class'                   => 'woocommerce-main-image wp-post-image',
						'title'                   => _wp_specialchars( get_post_field( 'post_title', $media_id ), ENT_QUOTES, 'UTF-8', true ),
						'data-large_image_width'  => esc_attr( $full_src[1] ),
						'data-large_image_height' => esc_attr( $full_src[2] ),
					),
					$media_id,
					$size,
					true
				)
			);
		} elseif ( strpos( $product_images_classes, 'product-image-slider owl-carousel' ) !== false && isset( $porto_settings_optimize['lazyload'] ) && $porto_settings_optimize['lazyload'] ) {
			$thumb_image = wp_get_attachment_image_src( $media_id, $size );
			if ( $thumb_image && is_array( $thumb_image ) && count( $thumb_image ) >= 3 ) {
				$placeholder = porto_generate_placeholder( $thumb_image[1] . 'x' . $thumb_image[2] );
				$item_html  .= wp_get_attachment_image(
					$media_id,
					$size,
					false,
					apply_filters(
						'woocommerce_gallery_image_html_attachment_image_params',
						array(
							'data-src'                => esc_url( $thumb_image[0] ),
							'src'                     => esc_url( $placeholder[0] ),
							'href'                    => esc_url( $full_src[0] ),
							'data-large_image_width'  => esc_attr( $full_src[1] ),
							'data-large_image_height' => esc_attr( $full_src[2] ),
							'class'                   => 'owl-lazy',
						),
						$media_id,
						$size,
						false
					)
				);
			}
		} else {
			$item_html .= wp_get_attachment_image(
				$media_id,
				$size,
				false,
				apply_filters(
					'woocommerce_gallery_image_html_attachment_image_params',
					array(
						'href'                    => esc_url( $full_src[0] ),
						'class'                   => 'img-responsive',
						'data-large_image_width'  => esc_attr( $full_src[1] ),
						'data-large_image_height' => esc_attr( $full_src[2] ),
					),
					$media_id,
					$size,
					false
				)
			);
		}

		if ( ( 'grid' === $porto_product_layout || 'sticky_info' === $porto_product_layout || 'sticky_both_info' === $porto_product_layout ) && $attach_gallery_ids ) {
			$item_html .= '<a role="button" aria-label="View product image as 360 degree" class="' . $image_view_cls . '" href="#"><i class="porto-icon-rotate"></i></a>';
		}
		if ( $porto_settings['product-image-popup'] && ( 'grid' === $porto_product_layout || 'sticky_info' === $porto_product_layout ) ) {
			$item_html .= '<a role="button" aria-label="Zoom the product image" class="zoom" href="' . esc_url( $full_src[0] ) . '"><i class="' . esc_attr( $product_icon_cl ) . '"></i></a>';
		}
		$item_html .= '</div></div>';
		$index ++;

		/**
		 * Filter product image thumbnail HTML string.
		 *
		 * @since 1.6.4
		 *
		 * @param string $html          Product image thumbnail HTML string.
		 * @param int    $attachment_id Attachment ID.
		 */
		$html .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', $item_html, $media_id, '', 'large', $index );
	}

	$html .= apply_filters( 'porto_single_product_gallery_img_after', '', false, $index );
	$html .= '</div>';
	
	if ( ( 'default' === $porto_product_layout || 'full_width' === $porto_product_layout || 'transparent' === $porto_product_layout || 'centered_vertical_zoom' === $porto_product_layout || 'extended' === $porto_product_layout || 'left_sidebar' === $porto_product_layout ) && $attach_gallery_ids ) {
		$html .= '<a role="button" aria-label="View product image as 360 degree" class="' . $image_view_cls . '" href="#"><i class="porto-icon-rotate"></i></a>';
	}
	if ( $porto_settings['product-image-popup'] && ( 'default' === $porto_product_layout || 'full_width' === $porto_product_layout || 'transparent' === $porto_product_layout || 'centered_vertical_zoom' === $porto_product_layout || 'extended' === $porto_product_layout || 'left_sidebar' === $porto_product_layout ) ) {
		$html .= '<span class="zoom" data-index="0"><i class="' . esc_attr( $product_icon_cl ) . '"></i></span>';
	}

	// Render contents for 360 degree view
	if ( $attach_gallery_ids ) {
		wp_enqueue_script( 'porto-360-gallery' );
		$attach_ids = json_decode( $attach_gallery_ids );
		$html .= '<div class="d-none gallery-images-wrap"><ul class="porto-360-gallery-images" data-src="';
		foreach ( $attach_ids as $key => $attach_id ) {
			if ( 0 !== $key ) {
				$html .= ',';
			}
			$html .= wp_get_attachment_image_url( $attach_id, 'full' );
		}
		$html .= '"></ul><div class="360-degree-progress-bar"></div><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 300 300" style="enable-background:new 0 0 300 300; fill: #808080;" xml:space="preserve"><g>
			<g>
					<path d="M99.9,95.6L92.6,94c2.2-7.3,7.6-11,15.5-11c4.7,0,8.2,0.9,10.7,3.2c2.5,2.2,4.1,5,4.1,8.8
						c0,4.7-2.5,8.2-7.9,10.1c6.3,1.6,9.1,5,9.1,11c0,4.1-1.6,7.6-4.7,9.8c-3.2,2.5-6.9,3.5-11.7,3.5c-4.4,0-7.9-0.9-11-2.8
						c-3.2-1.9-5-5-5.7-9.1l7.3-1.6c1.3,5,4.4,7.6,9.1,7.6c2.2,0,4.1-0.6,5.7-1.9c1.6-1.3,2.5-3.5,2.5-6c0-2.2-0.6-4.1-2.2-5.4
						c-1.6-1.3-4.1-2.2-7.9-2.2h-3.2v-5.4h3.2c2.2,0,3.8-0.3,5-0.6c1.3-0.3,2.2-0.9,2.8-2.2c0.6-1.3,1.3-2.5,1.3-4.4
						c0-2.2-0.6-3.8-1.9-5c-1.3-0.9-2.8-1.6-5-1.6C103.7,88.7,100.8,90.9,99.9,95.6z"/>
					<path d="M164.5,94l-6.9,1.9c-1.3-4.7-3.8-7.3-7.6-7.3c-3.2,0-5.4,1.6-6.9,4.4c-1.6,2.8-2.2,7.3-2.5,13.2
						c2.2-4.4,5.7-6.6,10.7-6.6c3.8,0,7.3,1.3,9.8,4.1s4.1,6.3,4.1,10.7c0,4.7-1.6,8.2-4.4,11s-6.6,4.1-11.4,4.1c-5,0-9.1-1.9-12-5.4
						c-3.2-3.5-4.7-9.1-4.7-17s1.6-13.9,4.7-18.3c3.2-4.1,7.3-6.3,12.6-6.3c3.5,0,6.6,0.9,9.1,2.5C161.7,87.4,163.6,90.3,164.5,94z
						M157,114.8c0-3.2-0.6-5.4-1.9-6.9c-1.3-1.6-3.2-2.2-5.4-2.2c-2.5,0-4.7,0.9-6,2.5c-1.3,1.9-2.2,3.8-2.2,6.6
						c0,2.5,0.6,4.7,2.2,6.6c1.6,1.6,3.5,2.5,6,2.5C154.5,124,157,120.8,157,114.8z"/>
					<path d="M203.3,106c0,8.5-1.3,14.5-4.1,18.3c-2.8,3.8-6.9,5.7-12.3,5.7c-11,0-16.4-7.6-16.4-23
						c0-8.5,1.3-14.5,4.1-18.3c2.8-3.8,6.9-5.7,12.3-5.7C197.7,83,203.3,90.6,203.3,106z M194.5,106.3c0-6.6-0.6-11.4-1.9-13.9
						c-1.3-2.5-3.2-4.1-6-4.1c-2.5,0-4.4,1.3-6,3.8c-1.6,2.5-1.9,7.3-1.9,13.9c0,7.3,0.6,12,2.2,14.5c1.6,2.5,3.5,3.5,5.7,3.5
						c2.5,0,4.7-1.3,6-4.1C193.9,117.7,194.5,113,194.5,106.3z"/>
					<path d="M228.2,93.7c0,2.8-0.9,5.4-3.2,7.6c-2.2,2.2-4.4,3.2-7.3,3.2c-2.8,0-5.4-0.9-7.3-3.2
						c-1.9-2.2-3.2-4.4-3.2-7.6c0-2.8,0.9-5.4,3.2-7.6c1.9-2.2,4.4-3.2,7.3-3.2c2.8,0,5.4,0.9,7.3,3.2C227,88.4,228.2,90.6,228.2,93.7z
						M222.9,93.7c0-1.9-0.6-3.2-1.6-4.4c-0.9-1.3-2.2-1.6-3.8-1.6c-1.6,0-2.5,0.6-3.8,1.9c-1.3,1.3-1.3,2.2-1.3,4.1
						c0,1.6,0.6,3.2,1.6,4.4c0.9,1.3,2.2,1.9,3.8,1.9c1.3,0,2.5-0.6,3.8-1.9C222.9,96.9,222.9,95.3,222.9,93.7z"/>
					<path d="M113.5,146.1h5.4l5,17.3l5-17.3h4.7l-7.6,24.3h-5L113.5,146.1z"/>
					<path d="M142.5,142.9h-6v-5.4h6V142.9z M136.8,146.1h5.7v24.3h-5.7V146.1z"/>
					<path d="M168.6,159h-15.5c0,3.2,0.6,5.4,1.6,6.6c0.9,0.9,2.2,1.6,3.8,1.6c2.5,0,4.1-1.6,4.7-4.4l5,0.6
						c-1.3,5-4.7,7.6-10.1,7.6c-3.2,0-6-0.9-7.9-3.2s-2.8-5-2.8-9.1c0-4.1,0.9-7.3,2.8-9.8s4.7-3.5,7.9-3.5c2.2,0,4.1,0.6,5.7,1.6
						c1.6,1.3,2.5,2.5,3.5,4.4C168,153.3,168.6,155.8,168.6,159z M163,155.5c0-4.4-1.6-6.3-4.7-6.3c-2.8,0-4.4,2.2-4.7,6.3H163z"/>
					<path d="M169.9,146.1h5.4l4.1,16.7l3.8-16.7h5l3.8,16.7l4.4-16.7h4.4l-6.6,24.3h-4.7l-4.1-16.7l-3.8,16.7h-4.7
						L169.9,146.1z"/>
				</g>
				<path d="M81.3,131.9v13.6c0,0-41,16.4,13.6,35.3c0,0,30,8.2,59.9,8.2v-11l41,19.2l-41,21.8v-13.6c0,0-106.3,0-106.3-41
					C48.5,164.4,48.5,142.6,81.3,131.9z"/>
				<path d="M201.1,178.2c2.8,0,32.8-8.2,32.8-21.8c0-13.6-11-16.4-11-16.4v-8.2c0,0,27.1,5.4,27.1,30c0,0,0,20.5-30,30
					L201.1,178.2z"/>
			</g></svg></div>';
	}
	echo porto_filter_output( $html ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

	?>
</div>

<?php
if ( $porto_settings['product-thumbs'] ) {
	do_action( 'woocommerce_product_thumbnails' );
}
?>
	</div>
</div>
