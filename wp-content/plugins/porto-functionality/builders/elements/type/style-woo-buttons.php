<?php
$css_escaped = '';
if ( isset( $atts['spacing'] ) && ( $atts['spacing'] || '0' == $atts['spacing'] ) ) {
	$css_escaped .= 'margin-' . ( is_rtl() ? 'right' : 'left' ) . ':' . esc_html( $atts['spacing'] ) . ';';
}
if ( isset( $atts['font_settings'], $atts['font_settings']['color'] ) && $atts['font_settings']['color'] ) {
	$css_escaped .= '--add-to-wishlist-icon-color:' . esc_html( $atts['font_settings']['color'] ) . ';';
}
if ( $css_escaped ) {
	echo porto_filter_output( $atts['selector'] ) . '{' . $css_escaped . '}';
}

if ( isset( $atts['style_options'], $atts['style_options']['hover'], $atts['style_options']['hover']['color'] ) && $atts['style_options']['hover']['color'] ) {
	echo porto_filter_output( $atts['selector'] ) . ':hover{--add-to-wishlist-icon-color:' . esc_html( $atts['style_options']['hover']['color'] ) . '}';
}
