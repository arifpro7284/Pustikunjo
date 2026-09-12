<?php

$responsive_controls = array( 'gap' );
$tabletBp = 991;
$mobileBp = 767;
foreach ( $responsive_controls as $responsive_control ) {
	if ( empty( $atts[ $responsive_control ] ) ) {
		continue;
	}
	$css_escaped = '';
	$sel = '.porto-style-' . $responsive_control . '-' . PortoShortcodesClass::get_global_hashcode( $atts[ $responsive_control ], '', array(), 8 );

	if ( ! empty( $atts[ $responsive_control ]['desktop'] ) ) {
		$desktopDecl = porto_build_style_res_declaration( $atts[ $responsive_control ]['desktop'], $responsive_control );
		if ( $desktopDecl ) {
			$css_escaped .= $sel . '{' . $desktopDecl . '}';
		}
	}

	if ( ! empty( $atts[ $responsive_control ]['tablet'] ) ) {
		$tabletDecl = porto_build_style_res_declaration( $atts[ $responsive_control ]['tablet'], $responsive_control );
		if ( $tabletDecl ) {
			$css_escaped .= '@media (max-width:' . $tabletBp . 'px){' . $sel . '{' . $tabletDecl . '}}';
		}
	}

	if ( ! empty( $atts[ $responsive_control ]['mobile'] ) ) {
		$mobileDecl = porto_build_style_res_declaration( $atts[ $responsive_control ]['mobile'], $responsive_control );
		if ( $mobileDecl ) {
			$css_escaped .= '@media (max-width:' . $mobileBp . 'px){' . $sel . '{' . $mobileDecl . '}}';
		}
	}

	if ( $css_escaped ) {
		echo porto_filter_output( $css_escaped );
	}
}
