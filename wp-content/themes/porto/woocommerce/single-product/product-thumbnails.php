<?php
/**
 * Single Product Thumbnails
 *
 * @version     11.1.0
 */

use Automattic\WooCommerce\Internal\ProductGallery\ProductMediaGallery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $woocommerce, $product, $porto_product_layout, $porto_settings;

if ( ! $product || ! $product instanceof WC_Product ) {
	return '';
}

if ( 'extended' == $porto_product_layout || 'sticky_info' == $porto_product_layout || 'sticky_both_info' == $porto_product_layout || 'grid' == $porto_product_layout ) {
	return;
}

$attachment_ids     = $product->get_gallery_image_ids();
$thumbnails_classes = '';
$thumb_size         = 'woocommerce_thumbnail';
$gallery_thumbnail  = wc_get_image_size( 'gallery_thumbnail' );
if ( $gallery_thumbnail['width'] !== 150 ) {
	$thumb_size = 'woocommerce_gallery_thumbnail';
}

$thumb_size         = apply_filters( 'woocommerce_gallery_thumbnail_size', $thumb_size );
// $thumb_size         = has_image_size( 'shop_thumbnail' ) ? 'shop_thumbnail' : 'woocommerce_thumbnail';
if ( 'full_width' === $porto_product_layout || 'centered_vertical_zoom' === $porto_product_layout ) {
	$thumbnails_classes = 'product-thumbnails-inner';
} elseif ( 'transparent' === $porto_product_layout ) {
	$thumbnails_classes = 'product-thumbs-vertical-slider';
} else {
	$thumbnails_classes = 'product-thumbs-slider owl-carousel';
}

$post_thumbnail_id = method_exists( $product, 'get_image_id' ) ? $product->get_image_id() : get_post_thumbnail_id();

/**
 * WooCommerce 11.1 introduced a mixed media gallery (images and videos) which is exposed through
 * ProductMediaGallery. The same list is used by single-product/product-image.php, so both templates
 * render the very same amount of items in the very same order and the thumbnails stay in sync with
 * the main slider, which is selected by index.
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

?>
<div class="product-thumbnails thumbnails">
	<?php
	$html = '<div class="' . esc_attr( $thumbnails_classes ) . ( 'product-thumbs-slider owl-carousel' == $thumbnails_classes ? ' has-ccols-spacing has-ccols ccols-' . intval( $porto_settings['product-thumbs-count'] ) : '' ) . '">';

	$index = 0;

	foreach ( $media_items as $media_item ) {

		$media_id    = isset( $media_item['id'] ) ? absint( $media_item['id'] ) : 0;
		$media_type  = isset( $media_item['media_type'] ) ? $media_item['media_type'] : 'image';
		$source_type = isset( $media_item['source_type'] ) ? $media_item['source_type'] : 'attachment';
		$is_main     = ( 0 === $index ); // The first rendered item matches the main gallery slide.
		$gallery_key = $is_main ? 0 : $index - 1;

		if ( 'placeholder' === $source_type || ! $media_id ) {

			$image_thumb_link = wc_placeholder_img_src();
			$index ++;
			$html            .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', '<div class="img-thumbnail"><div class="inner"><img class="woocommerce-main-thumb img-responsive" alt="placeholder" src="' . esc_url( $image_thumb_link ) . '" /></div></div>', false, $post->ID, '', $index ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
			continue;
		}

		if ( 'video' === $media_type ) {

			if ( ! $has_video_gallery ) {
				continue;
			}

			$video_src = wp_get_attachment_url( $media_id );
			if ( ! $video_src ) {
				continue;
			}

			$poster_id = method_exists( ProductMediaGallery::class, 'get_video_poster_id' ) ? ProductMediaGallery::get_video_poster_id( $media_item ) : 0;
			$image_alt = $poster_id ? trim( strip_tags( get_post_meta( $poster_id, '_wp_attachment_image_alt', true ) ) ) : '';
			if ( empty( $image_alt ) ) {
				$video_title = get_post_field( 'post_title', $media_id );
				/* translators: %s is the video title. */
				$image_alt = $video_title ? sprintf( __( 'Video: %s', 'woocommerce' ), $video_title ) : '';
			}

			$image_thumb_link = $poster_id ? wp_get_attachment_image_src( $poster_id, $thumb_size ) : false;

			$item_html = '<div class="img-thumbnail woocommerce-product-gallery__video-thumbnail">';
			if ( isset( $image_thumb_link[0] ) ) {
				$item_html .= '<img class="' . ( $is_main ? 'woocommerce-main-thumb ' : '' ) . 'img-responsive" alt="' . esc_attr( $image_alt ) . '" src="' . esc_url( $image_thumb_link[0] ) . '" width="' . esc_attr( $image_thumb_link[1] ) . '" height="' . esc_attr( $image_thumb_link[2] ) . '" />';
			} else {
				// No poster image is set, let the browser render the first frame of the video instead.
				$item_html .= '<video class="img-responsive woocommerce-product-gallery__video-thumbnail-preview" src="' . esc_url( $video_src ) . '" preload="metadata" muted playsinline aria-label="' . esc_attr( $image_alt ) . '"></video>';
			}
			$item_html .= '</div>';

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
			$html .= apply_filters( 'woocommerce_single_product_video_thumbnail_html', $item_html, $media_id, $media_item ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
			continue;
		}

		$image_thumb_link = wp_get_attachment_image_src( $media_id, $thumb_size );

		if ( ! isset( $image_thumb_link[0] ) ) {
			continue;
		}

		$image_alt = trim( strip_tags( get_post_meta( $media_id, '_wp_attachment_image_alt', true ) ) );
		if ( empty( $image_alt ) && function_exists( 'woocommerce_get_alt_from_product_title_and_position' ) ) {
			$image_alt = woocommerce_get_alt_from_product_title_and_position( $product->get_title(), $is_main, $gallery_key );
		}

		$item_html = '<div class="img-thumbnail"><img class="' . ( $is_main ? 'woocommerce-main-thumb ' : '' ) . 'img-responsive" alt="' . esc_attr( $image_alt ) . '" src="' . esc_url( $image_thumb_link[0] ) . '" width="' . esc_attr( $image_thumb_link[1] ) . '" height="' . esc_attr( $image_thumb_link[2] ) . '" /></div>';

		$index ++;

		/**
		 * Filter product image thumbnail HTML string.
		 *
		 * @since 1.6.4
		 *
		 * @param string $html          Product image thumbnail HTML string.
		 * @param int    $attachment_id Attachment ID.
		 */
		$html .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', $item_html, $media_id, $post->ID, '', $index ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	$html .= apply_filters( 'porto_single_product_after_thumbnails', '', false, $index );

	$html .= '</div>';

	echo porto_filter_output( $html );

	?>
</div>
