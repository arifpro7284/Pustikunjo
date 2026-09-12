<?php
/** @var string $image_url file path */
/** @var string $short_caption alt for a pic */
/** @var string $link_to link to instagram post */
/** @var string $type class */
/** @var int $args ['masonry_image_width'] width from settings */
/** @var int $comments comments count */
/** @var int $likes likes count */
/** @var string $caption text of a post */
/** @var string $timestamp post date */

$image_url        = $args['image_url'];
$link_to          = $args['link_to'];
$type             = $args['type'];
$padding          = $args['padding'];
$id               = $args['id'];
$caption          = $args['caption'];
$fullcaption      = $args['full_caption'];
$likes            = $args['likes'];
$comments         = $args['comments'];
$is_big_pic       = $args['is_big_pic'];
$timestamp        = $args['timestamp'];
$username         = $args['username'];
$sidecar_media    = $args['sidecar_media'];
$video            = $args['video'];
$post_link        = $args['post_link'];
$profile_img_link = $args['profile_img_link'];
$profile_url      = "https://www.instagram.com/$username/";

?>

<div class="highlight-item <?php echo ( $is_big_pic ) ? 'highlight-item-2x' : '' ?>">
    <div>
        <div class="pic <?php echo ( $is_big_pic ) ? 'pic-2x' : '' ?>"
             style="background-image: url(<?php echo esc_url( $image_url ) ?>);
                     background-repeat: no-repeat;
                     background-position: center;
                     position: relative;
                     background-size: cover;">
			<?php if ( $link_to == 'popup' ) { ?>
            <a data-remodal-target="<?php echo esc_attr( $id ); ?>" class="wis-popup-a wis-masonry-a">
				<?php } else if ( $link_to == 'none' ) { ?>

				<?php } else { ?>
                <a href="<?php echo esc_url( $link_to ); ?>" target="_blank" rel="nofollow">
					<?php } ?>
                    <div class="highlight-neg" style="width: 100%; height: 100%">
                        <div class="post-data" style="">
                            <div class="post-stats">
								<?php if ( ! empty( $likes ) ) { ?><span
                                        class="fa fa-heart">&nbsp;</span><?php echo esc_html( $likes );
								} ?>
                                &nbsp;&nbsp;
								<?php if ( ! empty( $comments ) ) { ?><span
                                        class="fa fa-comment">&nbsp;</span><?php echo esc_html( $comments );
								} ?>
                            </div>
                            <div class="post-date">
								<?php if ( $timestamp ) : ?>
                                    <span class="fa fa-clock">&nbsp;</span><?php echo date_i18n( 'j M', $timestamp ) ?>
								<?php endif; ?>
                            </div>
                        </div>
                    </div>

					<?php if ( $type == 'GraphVideo' && $args['enable_icons'] ) { ?>
                        <i class="fa fa-video-camera pic_icon pull-right" style="color: white"></i>
					<?php } ?>
					<?php if ( $type == 'GraphSidecar' && $args['enable_icons'] ) { ?>
                        <i class="fa fa-clone pic_icon pull-right" style="color: white"></i>
					<?php } ?>
					<?php if ( $link_to != 'none' ): ?></a><?php endif; ?>
        </div>
    </div>

	<?php if ( $link_to == 'popup' ) {
		$path = WIG_COMPONENT_VIEWS_DIR . "/templates/remodal.php";
		ob_start();
		include $path;
		echo ob_get_clean();
	} ?>

    <script>
        var block = jQuery('#highlight_container_id').width();
        var size = Math.floor(block / 4) - 1;
        var size2x = Math.floor(block / 2) - 2;

        if (size2x / size === 2) size2x--;
        if ((size * 2) % size2x === 1) size2x += 2;

        size += 'px';
        size2x += 'px';

        jQuery(".highlight-item").css('width', size);
        jQuery(".highlight-item").css('height', size);
        jQuery(".pic").css('height', size);
        jQuery(".pic").css('width', size);
        //jQuery(".pic").css('background-size', size);

        jQuery(".highlight-item-2x").css('width', size2x);
        jQuery(".highlight-item-2x").css('height', size2x);
        jQuery(".pic-2x").css('width', size2x);
        jQuery(".pic-2x").css('height', size2x);
        //jQuery(".pic-2x").css('background-size', size2x);
    </script>
    <style>

        .post-stats
        {
            margin-top: 45% !important;
        }

        .pic_icon
        {
            margin-right: 5%;
            margin-top: 5%;
        }

        .post-data
        {
            vertical-align: middle;
            width: 100%;
            height: 100%;
            display: none;
            text-align: center;
            line-height: 1;
            color: white;
        }

        .post-data span.fa
        {
            color: white;
        }

        .highlight-neg
        {
            position: absolute;
            background-color: black;
            width: 100%;
            height: 100%;
            bottom: 0;
            opacity: 0;
        }

        .highlight-item:hover .highlight-neg
        {
            opacity: 0.7;
        }

        .highlight-item
        {
            cursor: pointer;
        }

        .highlight-item:hover .post-data
        {
            display: block !important;
            position: absolute;
            opacity: 1 !important;
            color: white;
            font-size: small;
        }

        .highlight-item:hover .pic_icon
        {
            display: none;
        }

    </style>
</div>
