<?php
/** @var string $image_url file path */
/** @var string $short_caption alt for a pic */
/** @var string $link_to link to instagram post */
/** @var string $type class */
/** @var int $args ['masonry_image_width'] width from settings */
/** @var int $args ['masonry_lite_cols'] masonry columns from settings */
/** @var int $args ['masonry_lite_gap'] masonry gap from settings */
/** @var int $comments comments count */
/** @var int $likes likes count */
/** @var string $caption text of a post */
/** @var array $sidecar_media contains media for sidecar */

$username          = $args['username'];
$image_url         = $args['image_url'];
$id                = $args['id'];
$link_to           = $args['link_to'];
$type              = $args['type'];
$width             = $args['masonry_image_width'];
$likes             = $args['likes'];
$comments          = $args['comments'];
$caption           = $args['caption'];
$fullcaption       = $args['full_caption'];
$timestamp         = $args['timestamp'];
$sidecar_media     = $args['sidecar_media'];
$image_width       = $args['width'];
$image_height      = $args['height'];
$video             = $args['video'];
$post_link         = $args['post_link'];
$profile_img_link  = $args['profile_img_link'];
$masonry_lite_cols = $args['masonry_lite_cols'];
$masonry_lite_gap  = $args['masonry_lite_gap'];

$profile_url = "https://www.instagram.com/$username/";
?>
<div class="wis-item">
	<div class="wis-img">
		<?php if ( 'popup' === $link_to ) { ?>
		<a data-remodal-target="<?php echo (int) $id; ?>" class="wis-popup-a wis-masonry-a">
			<?php } elseif ( 'none' === $link_to ) { ?>

			<?php }
			else { ?>
			<a href="<?php echo esc_url( $link_to ); ?>" target="_blank">
				<?php } ?>
				<?php if ( 'GraphVideo' === $type && $args['enable_icons'] ) { ?>
					<i class="fa fa-video-camera wis-pic_icon" style="color: white"></i>
				<?php } ?>
				<?php if ( 'GraphSidecar' === $type && $args['enable_icons'] ) { ?>
					<i class="fa fa-clone wis-pic_icon" style="color: white"></i>
				<?php } ?>

				<div class="wis-masonry-neg"
				     style="background-color: black; width: 100%; height: 100%; position: absolute; z-index: 1;"></div>
				<img alt="<?php echo $caption ? esc_attr( $caption ) : ''; ?>"
				     src="<?php echo esc_url( $image_url ); ?>"
				     style="width: 100%; object-fit: cover;"/>
				<div class="post-date">
					<?php if ( $timestamp ) : ?>
						<span class="fa fa-clock">&nbsp;</span><?php echo esc_html( date_i18n( 'j M', $timestamp ) ); ?>
					<?php endif; ?>
					<p>
						<?php
						if ( ! empty( $likes ) ) {
							?>
							<span
									class="fa fa-heart">&nbsp;</span>
							<?php
							echo esc_html( $likes );
						}
						?>
						&nbsp;&nbsp;
						<?php
						if ( ! empty( $comments ) ) {
							?>
							<span
									class="fa fa-comment">&nbsp;</span>
							<?php
							echo esc_html( $comments );
						}
						?>
					</p>
				</div>
				<?php
				if ( $link_to !== 'none' ) :
				?>
			</a><?php endif; ?>
	</div>
	<?php if ( $caption !== '&hellip;' ) : ?>
		<div class="wis-img_description">
			<?php echo esc_html( $caption ); ?>
		</div>
	<?php endif; ?>

	<?php
	if ( 'popup' === $link_to ) {
		$path = WIG_COMPONENT_VIEWS_DIR . '/templates/remodal.php';
		ob_start();
		include $path;
		echo ob_get_clean(); // @codingStandardsIgnoreLine
	}
	?>
</div>

