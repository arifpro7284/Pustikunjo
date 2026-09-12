<?php

/**
 * Helpers for the new responsive "advanced dimension" value.
 *
 * Value shape ( new ):
 *   array(
 *       'desktop' => array( 'top' => '20', 'right' => '20', 'bottom' => '20', 'left' => '20', 'unit' => 'px' ),
 *       'tablet'  => array( ... ),
 *       'mobile'  => array( ... ),
 *   )
 *
 * Legacy shapes accepted ( both auto-migrated to the desktop slot ):
 *   array( 'top' => '20px', 'right' => '20px', 'bottom' => '20px', 'left' => '20px' )
 *   array( 'margin-top' => '20px', 'margin-right' => '20px', ... )   ( prefixed variant )
 *
 * Wrapped in function_exists guards because this file is included once per block.
 */
if ( ! function_exists( 'porto_is_legacy_dimension' ) ) {
	function porto_is_legacy_dimension( $value ) {
		if ( empty( $value ) || ! is_array( $value ) ) {
			return false;
		}
		if ( isset( $value['desktop'] ) || isset( $value['tablet'] ) || isset( $value['mobile'] ) ) {
			return false;
		}
		if ( isset( $value['top'] ) || isset( $value['right'] ) || isset( $value['bottom'] ) || isset( $value['left'] ) ) {
			return true;
		}
		foreach ( $value as $k => $v ) {
			if ( is_string( $k ) && preg_match( '/-(top|right|bottom|left)$/', $k ) ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'porto_migrate_legacy_dimension' ) ) {
	function porto_migrate_legacy_dimension( $value, $default_unit = 'px' ) {
		$desktop = array(
			'top'    => '',
			'right'  => '',
			'bottom' => '',
			'left'   => '',
			'unit'   => $default_unit,
		);
		if ( empty( $value ) || ! is_array( $value ) ) {
			return array( 'desktop' => $desktop );
		}
		$sides    = array( 'top', 'right', 'bottom', 'left' );
		$side_raw = array();
		foreach ( $sides as $side ) {
			$raw = isset( $value[ $side ] ) ? $value[ $side ] : '';
			if ( '' === $raw || null === $raw ) {
				// Fallback to "<prefix>-<side>".
				foreach ( $value as $k => $v ) {
					if ( $k !== $side && is_string( $k ) && '-' . $side === substr( $k, -strlen( '-' . $side ) ) ) {
						$raw = $v;
						break;
					}
				}
			}
			if ( '' !== $raw && null !== $raw ) {
				$side_raw[ $side ] = (string) $raw;
			}
		}
		if ( empty( $side_raw ) ) {
			return array( 'desktop' => $desktop );
		}
		$numeric_part = array();
		$units        = array();
		$all_simple   = true;
		foreach ( $side_raw as $side => $v ) {
			if ( preg_match( '/^(-?\d*\.?\d+)([a-zA-Z%]*)$/', trim( $v ), $m ) ) {
				$numeric_part[ $side ]     = $m[1];
				$units[ (string) $m[2] ]   = true;
			} else {
				$all_simple = false;
			}
		}
		if ( $all_simple && 1 === count( $units ) ) {
			$shared_unit     = (string) array_keys( $units )[0];
			$desktop['unit'] = '' !== $shared_unit ? $shared_unit : $default_unit;
			foreach ( $numeric_part as $side => $v ) {
				$desktop[ $side ] = $v;
			}
		} else {
			$desktop['unit'] = 'custom';
			foreach ( $side_raw as $side => $v ) {
				$desktop[ $side ] = $v;
			}
		}
		return array( 'desktop' => $desktop );
	}
}

if ( ! function_exists( 'porto_advanced_dimension_decls' ) ) {
	/**
	 * Build "<property>:<top> <right> <bottom> <left>;" ( shorthand ) or per-side declarations
	 * for one device of the new advanced-dimension value.
	 *
	 * @param array  $device_val Per-device array ( top/right/bottom/left/unit ).
	 * @param string $property   CSS property prefix ( e.g. "margin", "padding", "border-width" ).
	 * @return string CSS declarations ( may be empty ).
	 */
	function porto_advanced_dimension_decls( $device_val, $property ) {
		if ( empty( $device_val ) || ! is_array( $device_val ) ) {
			return '';
		}
		$unit    = ! empty( $device_val['unit'] ) ? $device_val['unit'] : 'px';
		$sides   = array( 'top', 'right', 'bottom', 'left' );
		$values  = array();
		$missing = false;
		foreach ( $sides as $side ) {
			if ( ! isset( $device_val[ $side ] ) || '' === $device_val[ $side ] || null === $device_val[ $side ] ) {
				$values[ $side ] = null;
				$missing         = true;
				continue;
			}
			$v = (string) $device_val[ $side ];
			if ( 'custom' === $unit ) {
				$values[ $side ] = $v;
			} elseif ( preg_match( '/^-?\d*\.?\d+$/', trim( $v ) ) ) {
				$values[ $side ] = $v . $unit;
			} else {
				// Defensive: non-numeric stored value with non-custom unit – emit as-is.
				$values[ $side ] = $v;
			}
		}
		if ( ! $missing ) {
			return $property . ':' . $values['top'] . ' ' . $values['right'] . ' ' . $values['bottom'] . ' ' . $values['left'] . ';';
		}
		$css = '';
		foreach ( $sides as $side ) {
			if ( null !== $values[ $side ] ) {
				$css .= ( $property ? $property . '-' : '' ) . $side . ':' . $values[ $side ] . ';';
			}
		}
		return $css;
	}
}

if ( ! function_exists( 'porto_advanced_dimension_decls_responsive' ) ) {
	/**
	 * Build CSS declarations for the desktop / tablet / mobile slots of an advanced-dimension value.
	 * Legacy non-responsive shapes are auto-migrated to the desktop slot.
	 *
	 * @param array  $value    Dimension value.
	 * @param string $property CSS property prefix.
	 * @return array { 'desktop' => '...', 'tablet' => '...', 'mobile' => '...' }
	 */
	function porto_advanced_dimension_decls_responsive( $value, $property ) {
		$out = array(
			'desktop' => '',
			'tablet'  => '',
			'mobile'  => '',
		);
		if ( empty( $value ) || ! is_array( $value ) ) {
			return $out;
		}
		if ( porto_is_legacy_dimension( $value ) ) {
			$value = porto_migrate_legacy_dimension( $value );
		}
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $dev ) {
			if ( ! empty( $value[ $dev ] ) && is_array( $value[ $dev ] ) ) {
				$out[ $dev ] = porto_advanced_dimension_decls( $value[ $dev ], $property );
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'porto_advanced_dimension_border_radius_decls' ) ) {
	/**
	 * Build CSS declarations for a single device of an advanced-dimension value, mapped to the
	 * four border-radius corners. The advanced-dimension control exposes the sides as
	 * top / right / bottom / left – for radii these map ( clockwise from top-left ) to:
	 *   top    => border-top-left-radius
	 *   right  => border-top-right-radius
	 *   bottom => border-bottom-right-radius
	 *   left   => border-bottom-left-radius
	 *
	 * @param array $device_val Per-device array ( top/right/bottom/left/unit ).
	 * @return string CSS declarations ( may be empty ).
	 */
	function porto_advanced_dimension_border_radius_decls( $device_val ) {
		if ( empty( $device_val ) || ! is_array( $device_val ) ) {
			return '';
		}
		$unit       = ! empty( $device_val['unit'] ) ? $device_val['unit'] : 'px';
		$sides      = array( 'top', 'right', 'bottom', 'left' );
		$corner_map = array(
			'top'    => 'border-top-left-radius',
			'right'  => 'border-top-right-radius',
			'bottom' => 'border-bottom-right-radius',
			'left'   => 'border-bottom-left-radius',
		);
		$values  = array();
		$missing = false;
		foreach ( $sides as $side ) {
			if ( ! isset( $device_val[ $side ] ) || '' === $device_val[ $side ] || null === $device_val[ $side ] ) {
				$values[ $side ] = null;
				$missing         = true;
				continue;
			}
			$v = (string) $device_val[ $side ];
			if ( 'custom' === $unit ) {
				$values[ $side ] = $v;
			} elseif ( preg_match( '/^-?\d*\.?\d+$/', trim( $v ) ) ) {
				$values[ $side ] = $v . $unit;
			} else {
				$values[ $side ] = $v;
			}
		}
		if ( ! $missing ) {
			// CSS border-radius shorthand order: top-left top-right bottom-right bottom-left.
			return 'border-radius:' . $values['top'] . ' ' . $values['right'] . ' ' . $values['bottom'] . ' ' . $values['left'] . ';';
		}
		$css = '';
		foreach ( $sides as $side ) {
			if ( null !== $values[ $side ] ) {
				$css .= $corner_map[ $side ] . ':' . $values[ $side ] . ';';
			}
		}
		return $css;
	}
}

if ( ! function_exists( 'porto_advanced_dimension_border_radius_decls_responsive' ) ) {
	/**
	 * Build border-radius CSS declarations for the desktop / tablet / mobile slots of an
	 * advanced-dimension value. Legacy non-responsive shapes are auto-migrated to desktop.
	 *
	 * @param array $value Dimension value.
	 * @return array { 'desktop' => '...', 'tablet' => '...', 'mobile' => '...' }
	 */
	function porto_advanced_dimension_border_radius_decls_responsive( $value ) {
		$out = array(
			'desktop' => '',
			'tablet'  => '',
			'mobile'  => '',
		);
		if ( empty( $value ) || ! is_array( $value ) ) {
			return $out;
		}
		if ( porto_is_legacy_dimension( $value ) ) {
			$value = porto_migrate_legacy_dimension( $value );
		}
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $dev ) {
			if ( ! empty( $value[ $dev ] ) && is_array( $value[ $dev ] ) ) {
				$out[ $dev ] = porto_advanced_dimension_border_radius_decls( $value[ $dev ] );
			}
		}
		return $out;
	}
}

// Per-request collectors for tablet / mobile declarations that need to be wrapped in @media queries
// after the main desktop selector block closes.
$porto_so_tablet_extra = '';
$porto_so_mobile_extra = '';

if ( ! empty( $settings['bg'] ) || ! empty( $settings['border'] ) || ! empty( $settings['padding'] ) || ! empty( $settings['margin'] ) || ! empty( $settings['position'] ) || ! empty( $settings['borderRadius'] ) || ! empty( $settings['transform'] ) || ! empty( $settings['boxshadow'] ) ) {
	echo '.page-wrapper ' . sanitize_text_field( $settings['selector'] ) . '{';
	if ( ! empty( $settings['bg'] ) ) {
		if ( ! empty( $settings['bg']['color'] ) ) {
			echo 'background-color:' . sanitize_text_field( $settings['bg']['color'] ) . ';';
		}
		if ( ! empty( $settings['bg']['img_url'] ) ) {
			echo 'background-image:url(' . esc_url( $settings['bg']['img_url'] ) . ');';
		}
		if ( ! empty( $settings['bg']['position'] ) ) {
			echo 'background-position:' . sanitize_text_field( $settings['bg']['position'] ) . ';';
		}
		if ( ! empty( $settings['bg']['attachment'] ) ) {
			echo 'background-attachment:' . sanitize_text_field( $settings['bg']['attachment'] ) . ';';
		}
		if ( ! empty( $settings['bg']['repeat'] ) ) {
			echo 'background-repeat:' . sanitize_text_field( $settings['bg']['repeat'] ) . ';';
		}
		if ( ! empty( $settings['bg']['size'] ) ) {
			echo 'background-size:' . sanitize_text_field( $settings['bg']['size'] ) . ';';
		}
	}

	// Border width now supports the new responsive advanced-dimension shape while still
	// accepting the legacy { top, right, bottom, left } shape ( with values like "1px" ).
	// The "style" and "color" sibling keys are shared across breakpoints – we only emit them
	// once in the desktop selector.
	if ( ! empty( $settings['border'] ) ) {
		if ( ! empty( $settings['border']['style'] ) ) {
			echo 'border-style:' . sanitize_text_field( $settings['border']['style'] ) . ';';
		}
		$porto_so_border_decls = porto_advanced_dimension_decls_responsive( isset( $settings['border']['width'] ) ? $settings['border']['width'] : $settings['border'], 'border-width' );
		if ( ! empty( $porto_so_border_decls['desktop'] ) ) {
			echo sanitize_text_field( $porto_so_border_decls['desktop'] );
		}
		if ( ! empty( $porto_so_border_decls['tablet'] ) ) {
			$porto_so_tablet_extra .= $porto_so_border_decls['tablet'];
		}
		if ( ! empty( $porto_so_border_decls['mobile'] ) ) {
			$porto_so_mobile_extra .= $porto_so_border_decls['mobile'];
		}
		if ( ! empty( $settings['border']['color'] ) ) {
			echo 'border-color:' . sanitize_text_field( $settings['border']['color'] ) . ';';
		}
	}

	// Border radius now supports the new responsive advanced-dimension shape while still
	// accepting the legacy { top, right, bottom, left } shape ( with values like "10px" ).
	if ( ! empty( $settings['borderRadius'] ) ) {
		$porto_so_radius_decls = porto_advanced_dimension_border_radius_decls_responsive( $settings['borderRadius'] );
		if ( ! empty( $porto_so_radius_decls['desktop'] ) ) {
			echo sanitize_text_field( $porto_so_radius_decls['desktop'] );
		}
		if ( ! empty( $porto_so_radius_decls['tablet'] ) ) {
			$porto_so_tablet_extra .= $porto_so_radius_decls['tablet'];
		}
		if ( ! empty( $porto_so_radius_decls['mobile'] ) ) {
			$porto_so_mobile_extra .= $porto_so_radius_decls['mobile'];
		}
	}

	// Margin / padding now support the new responsive advanced-dimension shape, while
	// continuing to accept the legacy { top, right, bottom, left } ( and prefixed ) shapes.
	if ( ! empty( $settings['margin'] ) ) {
		$porto_so_margin_decls = porto_advanced_dimension_decls_responsive( $settings['margin'], 'margin' );
		if ( ! empty( $porto_so_margin_decls['desktop'] ) ) {
			echo sanitize_text_field( $porto_so_margin_decls['desktop'] );
		}
		if ( ! empty( $porto_so_margin_decls['tablet'] ) ) {
			$porto_so_tablet_extra .= $porto_so_margin_decls['tablet'];
		}
		if ( ! empty( $porto_so_margin_decls['mobile'] ) ) {
			$porto_so_mobile_extra .= $porto_so_margin_decls['mobile'];
		}
	}

	if ( ! empty( $settings['padding'] ) ) {
		$porto_so_padding_decls = porto_advanced_dimension_decls_responsive( $settings['padding'], 'padding' );
		if ( ! empty( $porto_so_padding_decls['desktop'] ) ) {
			echo sanitize_text_field( $porto_so_padding_decls['desktop'] );
		}
		if ( ! empty( $porto_so_padding_decls['tablet'] ) ) {
			$porto_so_tablet_extra .= $porto_so_padding_decls['tablet'];
		}
		if ( ! empty( $porto_so_padding_decls['mobile'] ) ) {
			$porto_so_mobile_extra .= $porto_so_padding_decls['mobile'];
		}
	}

	if ( ! empty( $settings['position'] ) ) {
		if ( ! empty( $settings['position']['style'] ) ) {
			echo 'position:' . sanitize_text_field( $settings['position']['style'] ) . ';';
		}
		if ( isset( $settings['position']['zindex'] ) && ( ( is_string( $settings['position']['zindex'] ) && strlen( $settings['position']['zindex'] ) ) || ( ! empty( $settings['position']['zindex'] ) && is_numeric( $settings['position']['zindex'] ) ) ) ) {
			echo 'z-index:' . sanitize_text_field( $settings['position']['zindex'] ) . ';';
		} elseif ( ! empty( $settings['position']['zindex'] ) && is_array( $settings['position']['zindex'] ) ) {
			if ( ! empty( $settings['position']['zindex']['desktop'] ) ) {
				echo porto_build_style_res_declaration( $settings['position']['zindex']['desktop'], 'z-index' );
			}

			if ( ! empty( $settings['position']['zindex']['tablet'] ) ) {
				$tabletDecl = porto_build_style_res_declaration( $settings['position']['zindex']['tablet'], 'z-index' );
				if ( $tabletDecl ) {
					$porto_so_tablet_extra .= $tabletDecl;
				}
			}

			if ( ! empty( $settings['position']['zindex']['mobile'] ) ) {
				$mobileDecl = porto_build_style_res_declaration( $settings['position']['zindex']['mobile'], 'z-index' );
				if ( $mobileDecl ) {
					$porto_so_mobile_extra .= $mobileDecl;
				}
			}
		}
		if ( ! empty( $settings['position']['offset'] ) && is_array( $settings['position']['offset'] ) ) {
			$porto_so_position_offset_decls = porto_advanced_dimension_decls_responsive( $settings['position']['offset'], '' );
			if ( ! empty( $porto_so_position_offset_decls['desktop'] ) ) {
				echo sanitize_text_field( $porto_so_position_offset_decls['desktop'] );
			}
			if ( ! empty( $porto_so_position_offset_decls['tablet'] ) ) {
				$porto_so_tablet_extra .= $porto_so_position_offset_decls['tablet'];
			}
			if ( ! empty( $porto_so_position_offset_decls['mobile'] ) ) {
				$porto_so_mobile_extra .= $porto_so_position_offset_decls['mobile'];
			}
		} else {
			if ( isset( $settings['position']['top'] ) && strlen( str_replace( array( 'px', '%', 'em', 'rem', 'vw', 'vh' ), '', $settings['position']['top'] ) ) ) {
				echo 'top:' . sanitize_text_field( $settings['position']['top'] ) . ';';
			}
			if ( isset( $settings['position']['right'] ) && strlen( str_replace( array( 'px', '%', 'em', 'rem', 'vw', 'vh' ), '', $settings['position']['right'] ) ) ) {
				echo 'right:' . sanitize_text_field( $settings['position']['right'] ) . ';';
			}
			if ( isset( $settings['position']['bottom'] ) && strlen( str_replace( array( 'px', '%', 'em', 'rem', 'vw', 'vh' ), '', $settings['position']['bottom'] ) ) ) {
				echo 'bottom:' . sanitize_text_field( $settings['position']['bottom'] ) . ';';
			}
			if ( isset( $settings['position']['left'] ) && strlen( str_replace( array( 'px', '%', 'em', 'rem', 'vw', 'vh' ), '', $settings['position']['left'] ) ) ) {
				echo 'left:' . sanitize_text_field( $settings['position']['left'] ) . ';';
			}
		}
		if ( isset( $settings['position']['height_val'] ) && is_string( $settings['position']['height_val'] ) && strlen( str_replace( array( 'px', '%', 'em', 'rem', 'vw', 'vh' ), '', $settings['position']['height_val'] ) ) ) {
			echo 'height:' . sanitize_text_field( $settings['position']['height_val'] ) . ';';
		} elseif ( ! empty( $settings['position']['height_val'] ) && is_array( $settings['position']['height_val'] ) ) {
			if ( ! empty( $settings['position']['height_val']['desktop'] ) ) {
				echo porto_build_style_res_declaration( $settings['position']['height_val']['desktop'], 'height' );
			}

			if ( ! empty( $settings['position']['height_val']['tablet'] ) ) {
				$tabletDecl = porto_build_style_res_declaration( $settings['position']['height_val']['tablet'], 'height' );
				if ( $tabletDecl ) {
					$porto_so_tablet_extra .= $tabletDecl;
				}
			}

			if ( ! empty( $settings['position']['height_val']['mobile'] ) ) {
				$mobileDecl = porto_build_style_res_declaration( $settings['position']['height_val']['mobile'], 'height' );
				if ( $mobileDecl ) {
					$porto_so_mobile_extra .= $mobileDecl;
				}
			}
		}
		if ( isset( $settings['position']['width'] ) && strlen( $settings['position']['width'] ) ) {
			if ( 'custom' != $settings['position']['width'] && '' != $settings['position']['width'] ) {
				echo 'width:' . sanitize_text_field( $settings['position']['width'] ) . ';';
			} elseif ( 'custom' == $settings['position']['width'] ) {
				if ( isset( $settings['position']['width_val'] ) && is_string( $settings['position']['width_val'] ) && strlen( str_replace( array( 'px', '%', 'em', 'rem', 'vw', 'vh' ), '', $settings['position']['width_val'] ) ) ) {
					echo 'width:' . sanitize_text_field( $settings['position']['width_val'] ) . ';';
				} elseif ( ! empty( $settings['position']['width_val'] ) && is_array( $settings['position']['width_val'] ) ) {
					if ( ! empty( $settings['position']['width_val']['desktop'] ) ) {
						echo porto_build_style_res_declaration( $settings['position']['width_val']['desktop'], 'width' );
					}

					if ( ! empty( $settings['position']['width_val']['tablet'] ) ) {
						$tabletDecl = porto_build_style_res_declaration( $settings['position']['width_val']['tablet'], 'width' );
						if ( $tabletDecl ) {
							$porto_so_tablet_extra .= $tabletDecl;
						}
					}

					if ( ! empty( $settings['position']['width_val']['mobile'] ) ) {
						$mobileDecl = porto_build_style_res_declaration( $settings['position']['width_val']['mobile'], 'width' );
						if ( $mobileDecl ) {
							$porto_so_mobile_extra .= $mobileDecl;
						}
					}
				}
			}
		}
		if ( isset( $settings['position']['opacity'] ) && ( is_string( $settings['position']['opacity'] ) || is_numeric( $settings['position']['opacity'] ) ) && strlen( (string) $settings['position']['opacity'] ) ) {
			echo 'opacity:' . sanitize_text_field( $settings['position']['opacity'] ) . ';';
		} elseif ( ! empty( $settings['position']['opacity'] ) && is_array( $settings['position']['opacity'] ) ) {
			if ( ! empty( $settings['position']['opacity']['desktop'] ) ) {
				echo porto_build_style_res_declaration( $settings['position']['opacity']['desktop'], 'opacity' );
			}

			if ( ! empty( $settings['position']['opacity']['tablet'] ) ) {
				$tabletDecl = porto_build_style_res_declaration( $settings['position']['opacity']['tablet'], 'opacity' );
				if ( $tabletDecl ) {
					$porto_so_tablet_extra .= $tabletDecl;
				}
			}

			if ( ! empty( $settings['position']['opacity']['mobile'] ) ) {
				$mobileDecl = porto_build_style_res_declaration( $settings['position']['opacity']['mobile'], 'opacity' );
				if ( $mobileDecl ) {
					$porto_so_mobile_extra .= $mobileDecl;
				}
			}
		}

		if ( ( isset( $settings['position']['translatex'] ) && strlen( $settings['position']['translatex'] ) ) || ( isset( $settings['position']['translatey'] ) && strlen( $settings['position']['translatey'] ) ) ) {
			echo 'transform:';
			if ( isset( $settings['position']['translatex'] ) && strlen( $settings['position']['translatex'] ) ) {
				echo ' translateX(' . sanitize_text_field( $settings['position']['translatex'] ) . ')';
			}
			if ( isset( $settings['position']['translatey'] ) && strlen( $settings['position']['translatey'] ) ) {
				echo ' translateY(' . sanitize_text_field( $settings['position']['translatey'] ) . ')';
			}
			echo ';';
		}
	}

	if ( ! empty( $settings['transform'] ) ) {
		$transform_css = '';
		if ( ! empty( $settings['transform']['translate'] ) ) {
			if ( ! empty( $settings['transform']['translatex'] ) && ! empty( $settings['transform']['translatey'] ) ) {
				$transform_css .= ' translate(' . esc_html( $settings['transform']['translatex'] ) . ', ' . esc_html( $settings['transform']['translatey'] ) . ')';
			} elseif ( ! empty( $settings['transform']['translatex'] ) ) {
				$transform_css .= ' translateX(' . esc_html( $settings['transform']['translatex'] ) . ')';
			} elseif ( ! empty( $settings['transform']['translatey'] ) ) {
				$transform_css .= ' translateY(' . esc_html( $settings['transform']['translatey'] ) . ')';
			}
		}
		if ( ! empty( $settings['transform']['rotate'] ) && ! empty( $settings['transform']['rotatedeg'] ) ) {
			$transform_css .= ' rotate(' . esc_html( $settings['transform']['rotatedeg'] ) . 'deg)';
		}
		if ( ! empty( $settings['transform']['scale'] ) || ! empty( $settings['transform']['flipx'] ) || ! empty( $settings['transform']['flipy'] ) ) {
			if ( ! empty( $settings['transform']['scalex'] ) ) {
				$scaleX = (float) $settings['transform']['scalex'];
			}
			if ( ! empty( $settings['transform']['scaley'] ) ) {
				$scaleY = (float) $settings['transform']['scaley'];
			}

			if ( ! empty( $settings['transform']['flipx'] ) ) {
				if ( ! empty( $scaleX ) ) {
					$scaleX = -1 * $scaleX;
				} else {
					$scaleX = -1;
				}
			}
			if ( ! empty( $settings['transform']['flipy'] ) ) {
				if ( ! empty( $scaleY ) ) {
					$scaleY = -1 * $scaleY;
				} else {
					$scaleY = -1;
				}
			}
			if ( isset( $scaleX ) && isset( $scaleY ) ) {
				$transform_css .= ' scale(' . $scaleX . ', ' . $scaleY . ')';
			} elseif ( isset( $scaleX ) ) {
				$transform_css .= ' scaleX(' . $scaleX . ')';
			} elseif ( isset( $scaleY ) ) {
				$transform_css .= ' scaleY(' . $scaleY . ')';
			}
		}
		if ( ! empty( $settings['transform']['skew'] ) ) {
			if ( ! empty( $settings['transform']['skewx'] ) && ! empty( $settings['transform']['skewy'] ) ) {
				$transform_css .= ' skew(' . esc_html( $settings['transform']['skewx'] ) . 'deg, ' . esc_html( $settings['transform']['skewy'] ) . 'deg)';
			} elseif ( ! empty( $settings['transform']['skewx'] ) ) {
				$transform_css .= ' skewX(' . esc_html( $settings['transform']['skewx'] ) . 'deg)';
			} elseif ( ! empty( $settings['transform']['skewy'] ) ) {
				$transform_css .= ' skewY(' . esc_html( $settings['transform']['skewy'] ) . 'deg)';
			}
		}
		if ( $transform_css ) {
			echo 'transform:' . $transform_css . ';';
		}
		if ( ! empty( $settings['transform']['duration'] ) ) {
			echo 'transition:' . esc_html( $settings['transform']['duration'] ) . 'ms;';
		}
	}

	if ( ! empty( $settings['boxshadow'] ) && ( ! empty( $settings['boxshadow']['type'] ) || ! empty( $settings['boxshadow']['color'] ) ) ) {
		echo 'box-shadow:';
		if ( ! empty( $settings['boxshadow']['type'] ) && 'inset' != $settings['boxshadow']['type'] ) {
			echo sanitize_text_field( $settings['boxshadow']['type'] );
		} else {
			if ( ! empty( $settings['boxshadow']['type'] ) ) {
				echo sanitize_text_field( $settings['boxshadow']['type'] );
			}
			if ( ! empty( $settings['boxshadow']['x'] ) ) {
				echo ' ' . sanitize_text_field( $settings['boxshadow']['x'] );
			} else {
				echo ' 0';
			}
			if ( ! empty( $settings['boxshadow']['y'] ) ) {
				echo ' ' . sanitize_text_field( $settings['boxshadow']['y'] );
			} else {
				echo ' 0';
			}
			if ( ! empty( $settings['boxshadow']['blur'] ) ) {
				echo ' ' . sanitize_text_field( $settings['boxshadow']['blur'] );
			}
			if ( ! empty( $settings['boxshadow']['spread'] ) ) {
				echo ' ' . sanitize_text_field( $settings['boxshadow']['spread'] );
			}
			if ( ! empty( $settings['boxshadow']['color'] ) ) {
				echo ' ' . sanitize_text_field( $settings['boxshadow']['color'] );
			}
		}
		echo ';';
	}
	echo '}';
}

// Emit responsive @media blocks for the new advanced-dimension margin / padding values.
if ( ! empty( $porto_so_tablet_extra ) || ! empty( $porto_so_mobile_extra ) ) {
	$porto_so_selector = '.page-wrapper ' . sanitize_text_field( $settings['selector'] );
	if ( ! empty( $porto_so_tablet_extra ) ) {
		echo '@media (max-width:991px){' . $porto_so_selector . '{' . sanitize_text_field( $porto_so_tablet_extra ) . '}}';
	}
	if ( ! empty( $porto_so_mobile_extra ) ) {
		echo '@media (max-width:767px){' . $porto_so_selector . '{' . sanitize_text_field( $porto_so_mobile_extra ) . '}}';
	}
}

/* hover style */
if ( ! empty( $settings['hover'] ) ) {

	echo '.page-wrapper ' . sanitize_text_field( $settings['selector'] ) . ':hover{';
	if ( ! empty( $settings['hover']['bg'] ) ) {
		echo 'background-color:' . sanitize_text_field( $settings['hover']['bg'] ) . ';';
	}
	if ( ! empty( $settings['hover']['color'] ) ) {
		echo 'color:' . sanitize_text_field( $settings['hover']['color'] ) . ';';
	}
	if ( ! empty( $settings['hover']['border_style'] ) ) {
		echo 'border-style:' . sanitize_text_field( $settings['hover']['border_style'] ) . ';';
	}
	if ( isset( $settings['hover']['border_top'], $settings['hover']['border_right'], $settings['hover']['border_bottom'], $settings['hover']['border_left'] ) && strlen( $settings['hover']['border_top'] ) && strlen( $settings['hover']['border_right'] ) && strlen( $settings['hover']['border_bottom'] ) && strlen( $settings['hover']['border_left'] ) ) {
		echo 'border-width:' . sanitize_text_field( $settings['hover']['border_top'] . ' ' . $settings['hover']['border_right'] . ' ' . $settings['hover']['border_bottom'] . ' ' . $settings['hover']['border_left'] ) . ';';
	} else {
		if ( isset( $settings['hover']['border_top'] ) && strlen( $settings['hover']['border_top'] ) ) {
			echo 'border-top-width:' . sanitize_text_field( $settings['hover']['border_top'] ) . ';';
		}
		if ( isset( $settings['hover']['border_right'] ) && strlen( $settings['hover']['border_right'] ) ) {
			echo 'border-right-width:' . sanitize_text_field( $settings['hover']['border_right'] ) . ';';
		}
		if ( isset( $settings['hover']['border_bottom'] ) && strlen( $settings['hover']['border_bottom'] ) ) {
			echo 'border-bottom-width:' . sanitize_text_field( $settings['hover']['border_bottom'] ) . ';';
		}
		if ( isset( $settings['hover']['border_left'] ) && strlen( $settings['hover']['border_left'] ) ) {
			echo 'border-left-width:' . sanitize_text_field( $settings['hover']['border_left'] ) . ';';
		}
	}
	if ( ! empty( $settings['hover']['border_color'] ) ) {
		echo 'border-color:' . sanitize_text_field( $settings['hover']['border_color'] ) . ';';
	}
	if ( ! empty( $settings['hover']['top'] ) ) {
		echo 'top:' . sanitize_text_field( $settings['hover']['top'] ) . ';';
	}
	if ( ! empty( $settings['hover']['right'] ) ) {
		echo 'right:' . sanitize_text_field( $settings['hover']['right'] ) . ';';
	}
	if ( ! empty( $settings['hover']['bottom'] ) ) {
		echo 'bottom:' . sanitize_text_field( $settings['hover']['bottom'] ) . ';';
	}
	if ( ! empty( $settings['hover']['left'] ) ) {
		echo 'left:' . sanitize_text_field( $settings['hover']['left'] ) . ';';
	}
	if ( isset( $settings['hover']['opacity'] ) && strlen( $settings['hover']['opacity'] ) ) {
		echo 'opacity:' . floatval( $settings['hover']['opacity'] ) . ';';
	}
	if ( ( isset( $settings['hover']['translatex'] ) && strlen( $settings['hover']['translatex'] ) ) || ( isset( $settings['hover']['translatey'] ) && strlen( $settings['hover']['translatey'] ) ) ) {
		echo 'transform:';
		if ( isset( $settings['hover']['translatex'] ) && strlen( $settings['hover']['translatex'] ) ) {
			echo ' translateX(' . $settings['hover']['translatex'] . ')';
		}
		if ( isset( $settings['hover']['translatey'] ) && strlen( $settings['hover']['translatey'] ) ) {
			echo ' translateY(' . $settings['hover']['translatey'] . ')';
		}
		echo ';';
	}

	if ( ! empty( $settings['hover']['transform'] ) ) {
		$transform_css = '';
		if ( ! empty( $settings['hover']['transform']['translate'] ) ) {
			if ( ! empty( $settings['hover']['transform']['translatex'] ) && ! empty( $settings['hover']['transform']['translatey'] ) ) {
				$transform_css .= ' translate(' . esc_html( $settings['hover']['transform']['translatex'] ) . ', ' . esc_html( $settings['hover']['transform']['translatey'] ) . ')';
			} elseif ( ! empty( $settings['hover']['transform']['translatex'] ) ) {
				$transform_css .= ' translateX(' . esc_html( $settings['hover']['transform']['translatex'] ) . ')';
			} elseif ( ! empty( $settings['hover']['transform']['translatey'] ) ) {
				$transform_css .= ' translateY(' . esc_html( $settings['hover']['transform']['translatey'] ) . ')';
			}
		}
		if ( ! empty( $settings['hover']['transform']['rotate'] ) && ! empty( $settings['hover']['transform']['rotatedeg'] ) ) {
			$transform_css .= ' rotate(' . esc_html( $settings['hover']['transform']['rotatedeg'] ) . 'deg)';
		}
		if ( ! empty( $settings['hover']['transform']['scale'] ) || ! empty( $settings['hover']['transform']['flipx'] ) || ! empty( $settings['hover']['transform']['flipy'] ) ) {
			if ( ! empty( $settings['hover']['transform']['scalex'] ) ) {
				$scaleX = (float) $settings['hover']['transform']['scalex'];
			}
			if ( ! empty( $settings['hover']['transform']['scaley'] ) ) {
				$scaleY = (float) $settings['hover']['transform']['scaley'];
			}

			if ( ! empty( $settings['hover']['transform']['flipx'] ) ) {
				if ( ! empty( $scaleX ) ) {
					$scaleX = -1 * $scaleX;
				} else {
					$scaleX = -1;
				}
			}
			if ( ! empty( $settings['hover']['transform']['flipy'] ) ) {
				if ( ! empty( $scaleY ) ) {
					$scaleY = -1 * $scaleY;
				} else {
					$scaleY = -1;
				}
			}
			if ( isset( $scaleX ) && isset( $scaleY ) ) {
				$transform_css .= ' scale(' . $scaleX . ', ' . $scaleY . ')';
			} elseif ( isset( $scaleX ) ) {
				$transform_css .= ' scaleX(' . $scaleX . ')';
			} elseif ( isset( $scaleY ) ) {
				$transform_css .= ' scaleY(' . $scaleY . ')';
			}
		}
		if ( ! empty( $settings['hover']['transform']['skew'] ) ) {
			if ( ! empty( $settings['hover']['transform']['skewx'] ) && ! empty( $settings['hover']['transform']['skewy'] ) ) {
				$transform_css .= ' skew(' . esc_html( $settings['hover']['transform']['skewx'] ) . 'deg, ' . esc_html( $settings['hover']['transform']['skewy'] ) . 'deg)';
			} elseif ( ! empty( $settings['hover']['transform']['skewx'] ) ) {
				$transform_css .= ' skewX(' . esc_html( $settings['hover']['transform']['skewx'] ) . 'deg)';
			} elseif ( ! empty( $settings['hover']['transform']['skewy'] ) ) {
				$transform_css .= ' skewY(' . esc_html( $settings['hover']['transform']['skewy'] ) . 'deg)';
			}
		}
		if ( $transform_css ) {
			echo 'transform:' . $transform_css . ';';
		}
	}

	if ( ! empty( $settings['hover']['boxshadow'] ) && ( ! empty( $settings['hover']['boxshadow']['type'] ) || ! empty( $settings['hover']['boxshadow']['color'] ) ) ) {
		echo 'box-shadow:';
		if ( ! empty( $settings['hover']['boxshadow']['type'] ) && 'inset' != $settings['hover']['boxshadow']['type'] ) {
			echo sanitize_text_field( $settings['hover']['boxshadow']['type'] );
		} else {
			if ( ! empty( $settings['hover']['boxshadow']['type'] ) ) {
				echo sanitize_text_field( $settings['hover']['boxshadow']['type'] );
			}
			if ( ! empty( $settings['hover']['boxshadow']['x'] ) ) {
				echo ' ' . sanitize_text_field( $settings['hover']['boxshadow']['x'] );
			} else {
				echo ' 0';
			}
			if ( ! empty( $settings['hover']['boxshadow']['y'] ) ) {
				echo ' ' . sanitize_text_field( $settings['hover']['boxshadow']['y'] );
			} else {
				echo ' 0';
			}
			if ( ! empty( $settings['hover']['boxshadow']['blur'] ) ) {
				echo ' ' . sanitize_text_field( $settings['hover']['boxshadow']['blur'] );
			}
			if ( ! empty( $settings['hover']['boxshadow']['spread'] ) ) {
				echo ' ' . sanitize_text_field( $settings['hover']['boxshadow']['spread'] );
			}
			if ( ! empty( $settings['hover']['boxshadow']['color'] ) ) {
				echo ' ' . sanitize_text_field( $settings['hover']['boxshadow']['color'] );
			}
		}
		echo ';';
	}
	echo '}';
}
