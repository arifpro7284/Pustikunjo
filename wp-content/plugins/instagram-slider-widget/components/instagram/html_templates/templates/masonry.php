<?php
/** @var string $image_url file path */
/** @var string $short_caption alt for a pic */
/** @var string $link_to link to instagram post */
/** @var string $type class */
/** @var int $args ['masonry_image_width'] width from settings */
/** @var int $comments comments count */
/** @var int $likes likes count */
/** @var string $caption text of a post */
/** @var array $sidecar_media contains media for sidecar*/

$username = $args['username'];
$image_url = $args['image_url'];
$id = $args['id'];
$link_to = $args['link_to'];
$type = $args['type'];
$width = $args['masonry_image_width'];
$likes = $args['likes'];
$comments = $args['comments'];
$caption = $args['caption'];
$fullcaption = $args['full_caption'];
$timestamp = $args['timestamp'];
$sidecar_media = $args['sidecar_media'];
$image_width = $args['width'];
$image_height = $args['height'];
$video = $args['video'];
$post_link = $args['post_link'];
$profile_img_link = $args['profile_img_link'];

$profile_url = "https://www.instagram.com/$username/";
?>
<div class="wis-item">
    <div class="wis-img">
	    <?php if ($link_to == 'popup') {?>
        <a data-remodal-target="<?php echo esc_attr( $id ); ?>" class="wis-popup-a wis-masonry-a">
		    <?php } else if($link_to == 'none') {?>

			    <?php } else {?>
                <a href="<?php echo esc_url($link_to); ?>" target="_blank">
				    <?php } ?>
                <?php if ($type == 'GraphVideo' && $args['enable_icons']) { ?>
                    <i class="fa fa-video-camera wis-pic_icon" style="color: white"></i>
                <?php } ?>
                <?php if ($type == 'GraphSidecar' && $args['enable_icons']) { ?>
                    <i class="fa fa-clone wis-pic_icon" style="color: white"></i>
                <?php } ?>

                <div class="wis-masonry-neg"
                     style="background-color: black; width: 100%; height: 100%; position: absolute; z-index: 1;"></div>
                <img alt="<?php echo esc_attr( $caption ? $caption : '' ); ?>" src="<?php echo esc_url($image_url) ?>" style="<?php echo esc_attr( "width: {$width}px !important;" ); ?>" width="<?php echo esc_attr($width) ?>"/>
                <div class="post-date">
	                <?php if ($timestamp) : ?>
                        <span class="fa fa-clock">&nbsp;</span><?php echo date_i18n( 'j M', $timestamp )?>
	                <?php endif; ?>
                    <p>
	                    <?php if(!empty($likes)) {?><span class="fa fa-heart">&nbsp;</span><?php echo esc_html($likes); }?>
                        &nbsp;&nbsp;
	                    <?php if(!empty($comments)) {?><span class="fa fa-comment">&nbsp;</span><?php echo esc_html($comments); }?>
                    </p>
                </div>
	                <?php if($link_to != 'none'):?></a><?php endif; ?>
    </div>
    <?php if ($caption !== "&hellip;") : ?>
        <div class="wis-img_description">
            <?php echo esc_html($caption) ?>
        </div>
    <?php endif; ?>

    <?php if ($link_to == 'popup') {
        $path = WIG_COMPONENT_VIEWS_DIR . "/templates/remodal.php";
        ob_start();
        include $path;
        echo ob_get_clean();
    } ?>

    <style lang="css">
        .wis-img_description {
            text-align: center;
            color: gray;
            margin-left: auto;
            margin-right: auto;
            font-size: 0.8em;
            width: <?php echo esc_attr($width) ?>px;
        }
    </style>
</div>

