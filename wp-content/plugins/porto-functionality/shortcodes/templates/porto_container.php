<?php
$output = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(
	shortcode_atts(
		array(
			'animation_type'        => '',
			'animation_duration'    => 1000,
			'animation_delay'       => 0,
			'animation_reveal_clr'  => '',
			'is_half'               => '',
			'is_full_md'            => '',
			'el_id'                 => '',
			'el_class'              => '',
			'css_design'            => '',
			'sticky_content_marker' => '',
		),
		$atts
	)
);

$el_class = porto_shortcode_extract_class( $el_class );

$cls = '';
if ( ! empty( $is_half ) ) {
	$cls = 'col-half-section half-container';
	if ( ! empty( $is_full_md ) ) {
		$cls .= ' col-fullwidth-md';
	}
} else {
	$cls = 'porto-container container';
}
if ( $animation_type ) {
	$cls .= ' appear-animation';
}
if ( $el_class ) {
	$cls .= ' ' . esc_attr( trim( $el_class ) );
}
if ( defined( 'VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG' ) ) {
	$cls .= ' ' . apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_design, ' ' ), 'porto_container', $atts );
}
if ( ! empty( $sticky_content_marker ) ) {
	wp_enqueue_script( 'porto-gsap' );
	wp_enqueue_script( 'porto-scroll-trigger' );
	$cls .= ' gsap-content-marker';
}

$output = '<div class="' . $cls . '"';
if ( ! empty( $el_id ) ) {
	$output .= ' id="' . $el_id . '"';
}
if ( $animation_type ) {
	$output .= ' data-appear-animation="' . esc_attr( $animation_type ) . '"';
	if ( $animation_delay ) {
		$output .= ' data-appear-animation-delay="' . esc_attr( $animation_delay ) . '"';
	}
	if ( $animation_duration && 1000 != $animation_duration ) {
		$output .= ' data-appear-animation-duration="' . esc_attr( $animation_duration ) . '"';
	}
	if ( false !== strpos( $animation_type, 'revealDir' ) ) {
		$output .= ' data-animation-reveal-clr="' . ( ! empty( $animation_reveal_clr ) ? esc_attr( $animation_reveal_clr ) : '' ) . '"';
	}
}
if ( ! empty( $sticky_content_marker ) ) {
	$output .= ' data-marker-content="' . esc_attr( $sticky_content_marker ) . '"';
}
$output .= '>';

$output .= do_shortcode( $content );

$output .= '</div>';

echo porto_filter_output( $output );
