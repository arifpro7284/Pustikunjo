<?php
/** @var array $args account data */

$images  = $args['images'];
$feed_id = $args['feed_id'];
$args    = $args['template_args'];

$direction_nav = ( 'prev_next' === $args['controls'] ) ? 'true' : 'false';
$control_nav   = ( 'numberless' === $args['controls'] ) ? 'true' : 'false';
$icons         = $args['enable_icons'] ? "" : " no-isw-icons";
$type          = $args['type'] ?? '';
?>
<div class='pllexislider pllexislider-overlay instaslider-nr-<?php echo esc_attr( $feed_id ); ?>'>
	<ul class='no-bullet slides' id='wis-slides'>
		<?php foreach ( $images as $key => $data ) {
			$time      = $data['timestamp'];
			$username  = $data['username'];
			$image_url = $data['image'];
			$caption   = $data['caption'];
			$nopin     = ( 1 == $args['no_pin'] ) ? 'nopin="nopin"' : '';

			$clean_image_url = WIG_COMPONENT_URL . "/assets/img/image.png";
			$image_src       = "<img alt='" . esc_attr( $caption ) . "' src='" . esc_url( $clean_image_url ) . "' " . esc_attr( $nopin ) . " class='" . esc_attr( $data['type'] ) . "' style='opacity: 0;'>";
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
			<li class="<?php echo esc_attr( $type . $icons ); ?>">
				<div id="jr-image-overlay" style="background: url(<?php echo esc_url( $image_url ); ?>) no-repeat center center; background-size: cover;">
					<?php echo wp_kses_post( $image_output ); ?>
				</div>
				<?php if ( $args['description'] ) { ?>
					<div class='jr-insta-wrap'>
						<div class='jr-insta-datacontainer' style=''>
							<?php
							if ( $username && in_array( 'username', $args['description'], true ) ) {
								echo "<span class='jr-insta-username'>by <a rel='nofollow noreferrer' href='" . esc_url( "https://www.instagram.com/{$username}/" ) . "' style='color:black; font-weight: 600' target='_blank'>" . esc_html( $username ) . "</a></span>\n";
							}
							if ( $time && in_array( 'time', $args['description'], true ) ) {
								$time = human_time_diff( $time );
								echo "<strong><span class='jr-insta-time pull-right' style='font-size: 0.9em'>" . esc_html( $time ) . " ago</span></strong><br>\n";
							}

							if ( $caption != '' && in_array( 'caption', $args['description'], true ) ) {
								$caption = preg_replace( '/\@([a-z0-9А-Яа-я_-]+)/u', '&nbsp;<a href="' . esc_url( 'https://www.instagram.com/$1/' ) . '" rel="nofollow noreferrer" style="color:black; font-weight: 600" target="_blank">@$1</a>&nbsp;', $caption );
								$caption = preg_replace( '/\#([a-zA-Z0-9А-Яа-я_-]+)/u', '&nbsp;<a href="' . esc_url( 'https://www.instagram.com/explore/tags/$1/' ) . '" style="color:black; font-weight: 600" rel="nofollow noreferrer" target="_blank">$0</a>&nbsp;', $caption );
								echo "<span class='jr-insta-caption' style='text-align: left !important;'>" . wp_kses_post( $caption ) . "</span>\n";
							}
							?>
						</div>
					</div>
				<?php } ?>
			</li>
		<?php } ?>
	</ul>
</div>
<script type='text/javascript'>
    jQuery(document).ready(function ($) {
        $('.instaslider-nr-<?php echo esc_js( $feed_id ); ?>').pllexislider({
            animation: '<?php echo esc_js( $args['animation'] ); ?>',
            slideshowSpeed: <?php echo esc_js( $args['slidespeed'] ); ?>,
            directionNav: <?php echo esc_js( $direction_nav ); ?>,
            controlNav: <?php echo esc_js( $control_nav ); ?>,
            prevText: '',
            nextText: '',
            start: function (slider) {
                slider.hover(function () {
                    slider.find('.pllex-control-nav, .pllex-direction-nav').stop(true, true).fadeIn();
                    slider.find('.jr-insta-datacontainer').fadeIn();
                }, function () {
                    slider.find('.pllex-control-nav, .pllex-direction-nav').stop(true, true).fadeOut();
                    slider.find('.jr-insta-datacontainer').fadeOut();
                });
            }
        });
    });
</script>