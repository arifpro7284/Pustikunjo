<?php
    $block_width = 630;
?>


<div class="remodal test-paert" data-remodal-id="<?php echo esc_attr( $id ); ?>">
<div class="remodal-container">
    <div class="remodal-md-9 white">
        <div class="wis-remodal-block wis-remodal-img" >
        <?php if ($sidecar_media) {
            ?>
        <div class="wis-remodal-slick-container" style="">
            <?php foreach ($sidecar_media as $media) {
                if (isset($media['images']) && !empty($media['images'])) {
                    ?>
                    <div class="wis-remodal-item">
                        <img alt="" src="<?php echo esc_url($media['images']['standard_resolution']['url']) ?>"
                             class="remodal-media" style="width: <?php echo $block_width?>px"/>
                    </div>

                <?php } else if (isset($media['videos']) && !empty($media['videos'])) {?>

                    <div class="wis-remodal-item">
                        <div class="wis-video-block" >
                                <video class="wis-video remodal-media" width="100%" playsinline loop controls="controls"
                                       src="<?php echo esc_url($media['videos']['standard_resolution']['url']) ?>" style=""></video>
                        </div>
                    </div>

                <?php }
            } ?>
        </div>
        <?php } else if ($video) {
            ?>
            <div class="wis-video-block" >
                <div class="wis-play-icon-div" style="">
                    <span class="wis-play-icon fa fa-4x fa-play"></span></div>
                <div class="">
                    <video class="wis-video remodal-media" width="100%" playsinline loop
                           src="<?php echo esc_url($video) ?>" style=""></video>
                </div>
            </div>
        <?php } else { ?>
            <div style="width: 100%;">
                <img alt="" src="<?php echo esc_url($image_url) ?>" width="<?php echo esc_attr( $block_width ); ?>"  class="remodal-media-image"/>
            </div>
        <?php } ?>
    </div>
    </div>
    <div class="remodal-md-3 red">
        <div class="wis-remodal-block wis-remodal-info">
        <div class="wis-remodal-header">
            <div class="wis-remodal-header-img">
                <a href="<?php echo esc_url($profile_url) ?>" target="_blank" class="wis-href">
                    <img class="wis-remodal-round" src="<?php echo esc_url($profile_img_link) ?>" width="30"
                         height="30">
                    <span class="wis-remodal-username"><?php echo esc_html($username) ?></span>
                </a>
            </div>
        </div>
        <div class="wis-remodal-text"><?php echo esc_html($fullcaption) ?></div>
        <div class="wis-remodal-stats">
            <div class="wis-inline"><span class="fa fa-heart">&nbsp;</span><?php echo esc_html($likes) ?></div> &nbsp;&nbsp;
            <div class="wis-inline"><span class="fa fa-comment">&nbsp;</span><?php echo esc_html($comments) ?></div>&nbsp;&nbsp;|&nbsp;&nbsp;
            <div class="wis-inline">
                <p class="wis-remodal-share-buttons" style="display: none">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_html($post_link) ?>"><span
                                class="fa fa-facebook-square shr-btn shr-btn-fcbk"></span></a>
                    <a href="https://twitter.com/home?status=<?php echo esc_html($post_link) ?>" target="_blank"><span
                                class="fa fa-twitter-square shr-btn"></span></a>
                    <a href="https://plus.google.com/share?url=<?php echo esc_html($post_link) ?>" target="_blank"><span
                                class="fa fa-google-plus-square shr-btn"></span></a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo esc_html($post_link) ?>"
                       target="_blank"><span class="fa fa-linkedin-square shr-btn"></span></a>
                    <a href="https://pinterest.com/pin/create/button/?url=<?php echo esc_html($post_link) ?>"
                       target="_blank"><span class="fa fa-pinterest-square shr-btn"></span></a>
                </p>
                <div class="wis-href wis-remodal-share"><span class="fa fa-share ">&nbsp;</span>Share</div>
            </div>&nbsp;&nbsp;|&nbsp;&nbsp;
            <div class="wis-inline"><a href="<?php echo esc_url($post_link) ?>" target="_blank" class="wis-href"><span
                            class="fa fa-instagram">&nbsp;</span><?php esc_html_e( 'Instagram', 'instagram-slider-widget' ); ?></a></div>
        </div>
        <div class="date-post">
	        <?php if ($timestamp) : ?>
                <span class="fa fa-clock">&nbsp;</span><?php echo date_i18n( 'j M', $timestamp )?>
	        <?php endif; ?>
        </div>
    </div>
    </div>
</div>
</div>
