<?php

$output = $subtitle = $image_url = $image_id = $heading = $shadow = $heading_color = $subtitle_color = $animation_type = $animation_duration = $animation_delay = $el_class = '';

extract(
	shortcode_atts(
		array(
			'item_title_tag'       => '',
			'item_type'            => empty( $GLOBALS['porto_steps_type'] ) ? 'schedule' : $GLOBALS['porto_steps_type'],
			'subtitle'             => '',
			'icon_type'            => 'custom',
			'icon'                 => '',
			'icon_simpleline'      => '',
			'icon_porto'           => '',
			'image_url'            => '',
			'image_id'             => '',
			'heading'              => '',
			'shadow'               => '',
			'heading_color'        => '',
			'subtitle_color'       => '',
			'animation_type'       => '',
			'animation_duration'   => 1000,
			'animation_delay'      => 0,
			'animation_reveal_clr' => '',
			'badge_html'           => '',
			'el_class'             => '',
		),
		$atts
	)
);

$el_class = porto_shortcode_extract_class( $el_class );
if ( ! empty( $shortcode_class ) ) {
	$el_class .= ' ' . $shortcode_class;
}
switch ( $icon_type ) {
	case 'custom':
		if ( ! $image_url && $image_id ) {
			$image_size = 'history' == $item_type || 'timeline' == $item_type ? 'full' : 'thumbnail';
			$image_url  = wp_get_attachment_image_url( $image_id, $image_size );
		}

		$image_url = str_replace( array( 'http:', 'https:' ), '', $image_url );
		break;
	case 'simpleline':
		$icon = $icon_simpleline;
		break;
	case 'porto':
		$icon = $icon_porto;
		break;
}

global $porto_schedule_timeline_count, $porto_schedule_step_count, $porto_timeline_count;

if ( 'history' == $item_type || isset( $porto_schedule_timeline_count ) ) {
	$porto_schedule_timeline_count++;

	if ( $subtitle ) {
		$output .= '<div class="timeline-date ' . ( ! empty( $shortcode_class ) ? $shortcode_class : '' ) . '"><h3' . ( $subtitle_color ? ' style="color:' . esc_attr( $subtitle_color ) . '"' : '' ) . ' class="time-text step-item-subtitle">' . esc_html( $subtitle ) . '</h3></div>';
	}
	$attrs = '';
	if ( $animation_type ) {
		$attrs .= ' data-appear-animation="' . esc_attr( $animation_type ) . '"';
		if ( $animation_delay ) {
			$attrs .= ' data-appear-animation-delay="' . esc_attr( $animation_delay ) . '"';
		}
		if ( $animation_duration && 1000 != $animation_duration ) {
			$attrs .= ' data-appear-animation-duration="' . esc_attr( $animation_duration ) . '"';
		}
	}

	if ( 1 == $porto_schedule_timeline_count % 2 ) {
		$position_class = ' left';
	} else {
		$position_class = ' right';
	}
	if ( ! empty( $el_class ) ) {
		$position_class .= ' ' . $el_class;
	}
	$item_title_tag  = empty( $item_title_tag ) ? 'h4' : $item_title_tag;
	$output         .= '<article class="timeline-box' . $position_class . '"' . $attrs . '>';
		$output     .= '<div>';
		if ( 'custom' == $icon_type && $image_url ) {
			$output .= '<img src="' . esc_url( $image_url ) . '" class="img-responsive" alt="' . esc_attr( $heading ) . '">';
		} elseif ( 'custom' != $icon_type && $icon ) {
			if ( defined( 'ELEMENTOR_VERSION' ) && 'svg' === $icon_type ) {
				ob_start();
				\ELEMENTOR\Icons_Manager::render_icon(
					array(
						'library' => 'svg',
						'value'   => array( 'id' => absint( $icon ) ),
					),
					array( 'aria-hidden' => 'true' )
				);
				$output .= ob_get_clean();
			} else {
				$output .= '<i class="' . esc_attr( $icon ) . '"></i>';
			}
		}
			$output .= '<' . $item_title_tag . ( $heading_color ? ' style="color:' . esc_attr( $heading_color ) . ' !important"' : '' ) . ' class="timeline-item-title step-item-title">' . esc_html( $heading ) . '</' . $item_title_tag . '>';
			$output .= '<div class="timeline-item-content">' . do_shortcode( $content ) . '</div>';
		$output     .= '</div>';
	$output         .= '</article>';
} elseif ( 'step' == $item_type || isset( $porto_schedule_step_count ) ) {
	$porto_schedule_step_count++;

	$attrs = '';
	if ( $animation_type ) {
		$attrs .= ' data-appear-animation="' . esc_attr( $animation_type ) . '"';
		if ( $animation_delay ) {
			$attrs .= ' data-appear-animation-delay="' . esc_attr( $animation_delay ) . '"';
		}
		if ( $animation_duration && 1000 != $animation_duration ) {
			$attrs .= ' data-appear-animation-duration="' . esc_attr( $animation_duration ) . '"';
		}
	}

	$output .= '<div class="process-step' . ( $el_class ? ' ' . esc_attr( trim( $el_class ) ) : '' ) . '"' . $attrs . '>';
	$output .= '<div class="process-step-circle">';
	$output .= '<strong class="process-step-circle-content">';
	if ( 'custom' == $icon_type && $image_url ) {
		$output .= '<img src="' . esc_url( $image_url ) . '" class="img-responsive img-circle" alt="' . esc_attr( $heading ) . '">';
	} elseif ( 'custom' != $icon_type && $icon ) {
		if ( defined( 'ELEMENTOR_VERSION' ) && 'svg' === $icon_type ) {
			ob_start();
			\ELEMENTOR\Icons_Manager::render_icon(
				array(
					'library' => 'svg',
					'value'   => array( 'id' => absint( $icon ) ),
				),
				array( 'aria-hidden' => 'true' )
			);
			$output .= ob_get_clean();
		} else {
			$output .= '<i class="' . esc_attr( $icon ) . '"></i>';
		}
	} elseif ( $subtitle ) {
		$output .= esc_html( $subtitle );
	} else {
		$output .= ( (int) $porto_schedule_step_count );
	}
	$item_title_tag  = empty( $item_title_tag ) ? 'h4' : $item_title_tag;
	$output .= '</strong>';
	$output .= '</div>';
	$output .= '<div class="process-step-content">';
	$output .= '<' . $item_title_tag . ' class="step-item-title"' . ( $heading_color ? ' style="color:' . esc_attr( $heading_color ) . '"' : '' ) . '>' . esc_html( $heading ) . '</' . $item_title_tag . '>';
	$output .= '<div class="process-step-desc">' . do_shortcode( $content ) . '</div>';
	$output .= '</div>';
	$output .= '</div>';
} else if ( 'timeline' == $item_type  || isset( $porto_timeline_count ) ) {
	$porto_timeline_count++;
	if ( $subtitle ) {
		$output .= '<div class="timeline-number ' . ( ! empty( $shortcode_class ) ? $shortcode_class : '' ) . '"><h3' . ( $subtitle_color ? ' style="color:' . esc_attr( $subtitle_color ) . '"' : '' ) . ' class="time-text step-item-subtitle">' . esc_html( $subtitle ) . '</h3></div>';
	}
	$attrs = '';
	if ( $animation_type ) {
		$attrs .= ' data-appear-animation="' . esc_attr( $animation_type ) . '"';
		if ( $animation_delay ) {
			$attrs .= ' data-appear-animation-delay="' . esc_attr( $animation_delay ) . '"';
		}
		if ( $animation_duration && 1000 != $animation_duration ) {
			$attrs .= ' data-appear-animation-duration="' . esc_attr( $animation_duration ) . '"';
		}
	}

	$item_title_tag  = empty( $item_title_tag ) ? 'h4' : $item_title_tag;
	$output         .= '<div class="row mt-5 mb-5 align-items-center justify-content-around ' . esc_attr( $el_class ) . '"' . $attrs . '>';
		$output     .= '<div class="timeline-image text-center text-lg-start col-lg-5 p-3 ' . ( 1 == $porto_timeline_count % 2 ? 'order-0' : 'order-lg-1' ) . '">';
				if ( 'custom' == $icon_type && $image_url ) {
					$output .= '<img src="' . esc_url( $image_url ) . '" class="img-responsive" alt="' . esc_attr( $heading ) . '">';
				} elseif ( 'custom' != $icon_type && $icon ) {
					if ( defined( 'ELEMENTOR_VERSION' ) && 'svg' === $icon_type ) {
						ob_start();
						\ELEMENTOR\Icons_Manager::render_icon(
							array(
								'library' => 'svg',
								'value'   => array( 'id' => absint( $icon ) ),
							),
							array( 'aria-hidden' => 'true' )
						);
						$output .= ob_get_clean();
					} else {
						$output .= '<i class="' . esc_attr( $icon ) . '"></i>';
					}
				}
		$output     .= '</div>';
		$output     .= '<div class="col-lg-5 p-3 pt-0 text-center text-lg-start timeline-content">';
			if ( ! empty( $badge_html ) ) {
				$output .= '<div class="badge">' . do_shortcode( $badge_html ) . '</div>';
			}
			$output .= '<' . $item_title_tag . ( $heading_color ? ' style="color:' . esc_attr( $heading_color ) . ' !important"' : '' ) . ' class="timeline-item-title step-item-title">' . esc_html( $heading ) . '</' . $item_title_tag . '>';
			$output .= '<div class="timeline-item-content pe-lg-5">' . do_shortcode( $content ) . '</div>';
		$output     .= '</div>';
	$output         .= '</div>';
} else {
	$output         .= '<div class="timeline-balloon p-b-lg m-b-sm ' . esc_attr( $el_class ) . '">';
		$output     .= '<div class="balloon-cell balloon-time">';
			$output .= '<span' . ( $subtitle_color ? ' style="color:' . esc_attr( $subtitle_color ) . ' !important"' : '' ) . ' class="time-text step-item-subtitle">' . esc_html( $subtitle ) . '</span>';
			$output .= '<div class="time-dot background-color-light"></div>';
		$output     .= '</div>';
		$output     .= '<div class="balloon-cell"';
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
		$output     .= '>';
			$output .= '<div class="balloon-content ';
	if ( $shadow ) {
		$output .= ' balloon-shadow ';
	}
			$output     .= 'background-color-light">';
				$output .= '<span class="balloon-arrow"></span>';
	if ( 'custom' == $icon_type && $image_url ) {
		$output     .= '<div class="balloon-photo">';
			$output .= '<img src="' . esc_url( $image_url ) . '" class="img-responsive img-circle" alt="' . esc_attr( $heading ) . '">';
		$output     .= '</div>';
	} elseif ( 'custom' != $icon_type && $icon ) {
		if ( defined( 'ELEMENTOR_VERSION' ) && 'svg' === $icon_type ) {
			ob_start();
			\ELEMENTOR\Icons_Manager::render_icon(
				array(
					'library' => 'svg',
					'value'   => array( 'id' => absint( $icon ) ),
				),
				array( 'aria-hidden' => 'true' )
			);
			$output .= '<div class="balloon-photo">' . ob_get_clean() . '</div>';
		} else {
			$output .= '<div class="balloon-photo"><i class="' . esc_attr( $icon ) . '"></i></div>';
		}
	}
				$output .= '<div class="balloon-description">';
	if ( $heading ) {
		$item_title_tag  = empty( $item_title_tag ) ? 'h5' : $item_title_tag;
		$output .= '<' . $item_title_tag . ( $heading_color ? ' style="color:' . esc_attr( $heading_color ) . ' !important"' : '' ) . ' class="step-item-title text-color-dark font-weight-bold p-t-xs">' . esc_html( $heading ) . '</' . $item_title_tag . '>';
	}

	if ( $content ) {
		$output .= '<div class="step-item-desc">' . do_shortcode( $content ) . '</div>';
	}
				$output .= '</div>';
			$output     .= '</div>';
		$output         .= '</div>';
	$output             .= '</div>';
}

echo porto_filter_output( $output );
