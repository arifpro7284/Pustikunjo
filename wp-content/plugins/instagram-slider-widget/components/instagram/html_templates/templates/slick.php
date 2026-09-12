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
/** @var bool $keep_ratio keep 1x1 instagram pic ratio */
/** @var int $slick_img_size image size */

$image_url = $args['image_url'];
$link_to = $args['link_to'];
$type = $args['type'];
$padding = $args['padding'];
$id = $args['id'];
$caption = $args['caption'];
$fullcaption = $args['full_caption'];
$likes = $args['likes'];
$comments = $args['comments'];
$timestamp = $args['timestamp'];
$username = $args['username'];
$sidecar_media = $args['sidecar_media'];
$video = $args['video'];
$post_link = $args['post_link'];
$profile_img_link = $args['profile_img_link'];
$profile_url = "https://www.instagram.com/$username/";

if($keep_ratio) {
    $picture_size = sprintf("height: %spx; width: %spx;", $slick_img_size,$slick_img_size);
} else {
    $picture_size = sprintf("height: %spx;", 300); // default value
}

?>
<div class="slick-slide slick-div" style="<?php echo $padding ? 'padding:5px;' : '' ?>">
    <?php if ($link_to == 'popup') {?>
        <a data-remodal-target="<?php echo esc_attr( $id ); ?>" class="wis-popup-a wis-masonry-a">
    <?php } else if($link_to == 'none') {?>

    <?php } else {?>
        <a href="<?php echo esc_url($link_to); ?>" target="_blank">
    <?php } ?>
    <div class="img" style="
            background: url('<?php echo esc_url($image_url) ?>') no-repeat center center;
            <?php echo esc_attr( $picture_size ); ?>
            background-size: cover;
            padding: 10px;
            position: relative;
            ">
        <div class="slick-neg" style="font-size: 1em">
            <div class="stats" style="">
	            <?php if(!empty($likes)) {?><span class="fa fa-heart">&nbsp;</span><?php echo esc_html($likes); }?>
                &nbsp;&nbsp;
	            <?php if(!empty($comments)) {?><span class="fa fa-comment">&nbsp;</span><?php echo esc_html($comments); }?>
            </div>
            <div class="post-date" style="">
	            <?php if ($timestamp) : ?>
                    <span class="fa fa-clock">&nbsp;</span><?php echo date_i18n( 'j M', $timestamp )?>
	            <?php endif; ?>
            </div>
        </div>
        <?php if ($type == 'GraphVideo' && $args['enable_icons'] ) { ?>
            <i class="fa fa-video-camera image-icon"></i>
        <?php } ?>
        <?php if ($type == 'GraphSidecar' && $args['enable_icons'] ) { ?>
            <i class="fa fa-clone image-icon"></i>
        <?php } ?>
        <img src="<?php echo esc_url($image_url) ?>" alt="<?php echo esc_attr( $caption ? $caption : '' ); ?>" style="display: none;">
    </div>
	        <?php if($link_to != 'none'):?></a><?php endif; ?>
    <?php if ($link_to == 'popup'){
            $path = WIG_COMPONENT_VIEWS_DIR . "/templates/remodal.php";
            ob_start();
            include $path;
            echo ob_get_clean();
    } ?>
</div>
