<?php
/** @var array $args account data */

$images       = $args['images'];
$feed_id      = $args['feed_id'];
$args         = $args['template_args'];
$enable_icons = $args['enable_icons'] ? "" : " no-isw-icons";
?>
<div class='jr-insta-thumb'>
	<ul class='no-bullet thumbnails jr_col_<?php echo esc_attr( $args['columns'] ); ?>' id='wis-slides'>
		<?php foreach ( $images as $key => $data ) {
			$image_url = $data['image'];
			$nopin     = ( 1 == $args['no_pin'] ) ? 'nopin="nopin"' : '';

			$clean_image_url = WIG_COMPONENT_URL . "/assets/img/image.png";
			$image_src       = "<img alt='" . esc_attr( $data['caption'] ) . "' src='" . esc_url( $clean_image_url ) . "' " . esc_attr( $nopin ) . " class='" . esc_attr( $data['type'] ) . "' style='opacity: 0;'>";
			$image_output    = $image_src;

			if ( $data['link_to'] && $args['images_link'] != 'none' ) {
				$image_output = "<a href='" . esc_url( $data['link_to'] ) . "' target='_blank' rel='nofollow noreferrer'";

				if ( ! empty( $args['link_rel'] ) ) {
					$image_output .= " rel='" . esc_attr( $args['link_rel'] ) . "'";
				}

				if ( ! empty( $args['link_class'] ) ) {
					$image_output .= " class='" . esc_attr( $args['link_class'] ) . "'";
				}
				$image_output .= "> $image_src</a>";
			}
			?>
			<li class='<?php echo esc_attr( $data['type'] . $enable_icons ); ?>'>
				<div style='background: url(<?php echo esc_url( $image_url ); ?>) no-repeat center center; background-size: cover;'>
					<?php echo wp_kses_post( $image_output ); ?>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>