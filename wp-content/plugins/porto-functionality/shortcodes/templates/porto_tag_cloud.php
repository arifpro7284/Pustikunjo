<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Porto Tag Cloud Template
 *
 * @since 2.6.0
 */

if ( ! wp_style_is( 'porto-widget-tag-cloud' ) && defined( 'PORTO_CSS' ) ) {
	porto_enqueue_link_style( 'porto-widget-tag-cloud', PORTO_CSS . '/part/widget-tag-cloud' . ( is_rtl() ? '_rtl' : '' ) . '.css' );
}

extract(
	shortcode_atts(
		array(
			'taxonomy'   => 'post_tag',
			'show_count' => '',
			'el_class'   => '',
		),
		$atts
	)
);

if ( ! empty( $shortcode_class ) ) {
	if ( empty( $el_class ) ) {
		$el_class = $shortcode_class;
	} else {
		$el_class .= ' ' . $shortcode_class;
	}
}


if ( $el_class ) {
	echo '<div' . ' class="' . esc_attr( $el_class ) . '"' . '>';
}

$instance = array(
	'taxonomy' => $taxonomy,
);

if ( ! empty( $show_count ) ) {
	$instance['count'] = true;
}

the_widget( 'WP_Widget_Tag_Cloud', $instance );

if ( $el_class ) {
	echo '</div>';
}
