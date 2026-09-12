<?php
extract(
	shortcode_atts(
		array(
			'type'                 => 'html',
			'id'                   => '',
			'addlinks_pos'         => '',
			'block'                => '',
			'icon_type'            => 'fontawesome',
			'icon'                 => '',
			'icon_simpleline'      => '',
			'icon_porto'           => '',
			'spot_icon'            => '',
			'pos'                  => 'right',
			'x'                    => '',
			'y'                    => '',
			'size'                 => '',
			'icon_size'            => '',
			'color'                => '',
			'bg_color'             => '',
			'slider_selector'      => '',
			'item_order'           => '',
			'animation_type'       => '',
			'animation_duration'   => 1000,
			'animation_delay'      => 0,
			'animation_reveal_clr' => '',
			'el_class'             => '',
			'inner_content_only'   => '',
			'type_id'              => '',
		),
		$atts
	)
);
wp_enqueue_script( 'porto-hotspot' );

if ( ( ! isset( $content ) || empty( $content ) ) && isset( $atts['content'] ) ) {
	$content = $atts['content'];
}
switch ( $icon_type ) {
	case 'simpleline':
		if ( $icon_simpleline ) {
			$icon = $icon_simpleline;
		}
		break;
	case 'porto':
		if ( $icon_porto ) {
			$icon = $icon_porto;
		}
		break;	
}
if ( empty( $icon ) ) {
	$icon = 'fas fa-circle';
}

$inline_style = '';
if ( $x ) {
	$inline_style .= 'left:' . esc_attr( $x ) . '%;';
}
if ( $y ) {
	$inline_style .= 'top:' . esc_attr( $y ) . '%;';
}
if ( $size ) {
	$unit = trim( preg_replace( '/[0-9.]/', '', $size ) );
	if ( ! $unit ) {
		$size .= 'px';
	}
	$inline_style .= 'width:' . esc_attr( $size ) . ';height:' . esc_attr( $size ) . ';';
}
if ( $bg_color ) {
	$inline_style .= 'background-color:' . esc_attr( $bg_color ) . ';';
}

$icon_inline_style = '';
if ( $color ) {
	$icon_inline_style .= 'color:' . esc_attr( $color ) . ';';
}
if ( $icon_size ) {
	$unit = trim( preg_replace( '/[0-9.]/', '', $icon_size ) );
	if ( ! $unit ) {
		$icon_size .= 'px';
	}
	$icon_inline_style .= 'font-size:' . esc_attr( $icon_size ) . ';';
}
if ( $icon_inline_style ) {
	$icon_inline_style = ' style="' . $icon_inline_style . '"';
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
	if ( false !== strpos( $animation_type, 'revealDir' ) ) {
		$attrs .= ' data-animation-reveal-clr="' . ( ! empty( $animation_reveal_clr ) ? esc_attr( $animation_reveal_clr ) : '' ) . '"';
    }
}

if ( 'slider' == $type ) {
	if ( ! empty( $slider_selector ) && isset( $item_order ) ) {
		wp_enqueue_script( 'porto-focus-slider' );
		$attrs .= ' data-focus-slider="' . esc_attr( json_encode( array( 'selector' => $slider_selector, 'order' => $item_order - 1 ) ) ) . '"';
		$el_class .= ' porto-focus-slider';
	}
}
if ( $inline_style ) {
	$attrs .= ' style="' . $inline_style . '"';
}
?>

<?php if ( empty( $inner_content_only ) ) : ?>
<div class="porto-hotspot pos-<?php echo esc_attr( $pos ), ! $el_class ? '' : ' ' . esc_attr( $el_class ); ?>"<?php echo porto_filter_output( $attrs ); ?>>
<?php endif; ?>
	<i class="porto-hotspot-icon <?php echo esc_attr( $icon ); ?>"<?php echo porto_filter_output( $icon_inline_style ); ?>></i>
	<?php if ( 'slider' != $type ) : ?>
		<?php
		if ( 'html' == $type && ! empty( $content ) ) {
			?><div class="popup-wrap"><?php
			echo do_shortcode( porto_strip_script_tags( $content ) );
			?></div><?php
		} elseif ( 'product' == $type && $id ) {
			?><div class="popup-wrap"><?php
			if ( apply_filters( 'porto_legacy_mode', true ) ) {
				echo do_shortcode( '[porto_product id="' . intval( $id ) . '" addlinks_pos="' . esc_attr( $addlinks_pos ) . '"]' );
			} else { // if soft mode
				if ( $type_id ) {
					$type_id      = porto_multi_lang_post_id( $type_id, 'porto_builder' );
					$builder_post = false;
					if ( $type_id ) {
						$builder_post = get_post( (int) $type_id );
						global $porto_settings;
						if ( ! isset( $porto_settings['rendered_builders'] ) ) {
							$porto_settings['rendered_builders'] = array();
						}
						if ( ! in_array( $type_id, $porto_settings['rendered_builders'] ) || apply_filters( 'porto_already_start_before', false ) ) {
							echo '<style scope="scope">';
							// Don't record the builder id during a shadow render inside wp_head ( e.g. All in
							// One SEO builds the meta description by running do_blocks()/do_shortcode() and
							// discarding the HTML ). Recording it there would make the real body render skip
							// this <style>/<script>, dropping the hotspot builder CSS/JS. Mirrors porto_posts_grid.
							if ( ! doing_action( 'wp_head' ) ) {
								$porto_settings['rendered_builders'][] = $type_id;
							}
							$css = get_post_meta( $type_id, 'porto_builder_css', true );
							if ( $css ) {
								echo wp_strip_all_tags( $css );
							}
							$css = get_post_meta( $type_id, 'porto_blocks_style_options_css', true );
							if ( $css ) {
								echo wp_strip_all_tags( $css );
							}
							$css = get_post_meta( $type_id, 'custom_css', true );
							if ( $css ) {
								echo wp_strip_all_tags( $css );
							}
							echo '</style>';
							if ( ! doing_action( 'wp_head' ) ) {
								$porto_settings['rendered_builders'][] = $type_id . '_js';
							}
							$js_code                               = get_post_meta( $type_id, 'custom_js_body', true );
							if ( $js_code ) {
								echo '<script>';
								echo porto_strip_script_tags( $js_code );
								echo '</script>';
							}
						}
						add_filter( 'porto_is_tb_rendering', '__return_true' );
						global $post;
						$post = get_post( $id );
						setup_postdata( $id );
						echo '<div class="product porto-tb-item mb-0">';
						echo do_blocks( $builder_post->post_content );
						echo '</div>';
						wp_reset_postdata();
						remove_filter( 'porto_is_tb_rendering', '__return_true' );
					}
				} else {
					global $porto_woocommerce_loop;
					$porto_woocommerce_loop['addlinks_pos'] = $addlinks_pos;
					echo do_shortcode( '[product id="' . $id . '" columns="1"]' );
				}
			}
			?></div><?php
		} elseif ( 'block' == $type && $block ) {
			?><div class="popup-wrap"><?php
			if ( is_numeric( $block ) ) {
				echo do_shortcode( '[porto_block id="' . intval( $block ) . '"]' );
			} else {
				echo do_shortcode( '[porto_block name="' . esc_attr( $block ) . '"]' );
			}
			?></div><?php
		}
		?>
	<?php endif; ?>
<?php if ( empty( $inner_content_only ) ) : ?>
</div>
<?php endif; ?>
