<?php
/**@var array $posts */
/**@var array $args */

$block_width = 630;
$color = $args['shopifeed_color'];
$message_template = "Hello! I want to buy this one ";
$phone = str_replace(['+', '-', '(', ')',' '], '',$args['shopifeed_phone']);
$mail = get_option( 'admin_email');


?>

<div class="isw-popups">
    <?php foreach($posts as $post):?>
        <div class="remodal" data-remodal-id="<?php echo esc_attr( $post['id'] ); ?>">
            <div class="remodal-container">
                <div class="remodal-md-9 white">
                    <div class="wis-remodal-block wis-remodal-img" >
                        <?php if ( isset($post['sidecar_media'])) {
                            ?>
                            <div class="wis-remodal-slick-container" style="">
                                <?php foreach ($post['sidecar_media'] as $media) {
                                    if (isset($media['images']) && !empty($media['images'])) {
                                        $width_image = $media['images']['standard_resolution']['width'];
                                        ?>
                                        <div class="wis-remodal-item">
                                            <img alt="" src="<?php echo esc_url($media['images']['standard_resolution']['url']) ?>"
                                                 class="remodal-media" style="width: <?php echo esc_attr( $block_width ); ?>px"/>
                                        </div>

                                    <?php } else if (isset($media['videos']) && !empty($media['videos'])) {
                                        //$video_height = $video['standard_resolution']['height']/1.333333;
                                        ?>

                                        <div class="wis-remodal-item">
                                            <div class="wis-video-block" >
                                                <video class="wis-video remodal-media" width="100%" playsinline loop controls="controls"
                                                       src="<?php echo esc_url($media['videos']['standard_resolution']['url']) ?>" style=""></video>
                                            </div>
                                        </div>

                                    <?php }
                                } ?>
                            </div>
                        <?php } else if (isset($post['video'])) {
                            ?>
                            <div class="wis-video-block" >
                                <div class="wis-play-icon-div" style="">
                                    <img class="wis-icon-play" src="<?php echo esc_url( WIG_COMPONENT_URL . '/assets/icons/play.png' ); ?>"/></div>
                                <div class="">
                                    <video class="wis-video remodal-media" width="100%" playsinline loop
                                           src="<?php echo esc_url($post['video']) ?>" style=""></video>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div style="width: 100%;">
                                <img alt="" src="<?php echo esc_url($post['image']) ?>" width="<?php echo esc_attr( $block_width ); ?>"  class="remodal-media-image"/>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="remodal-md-3 red">
                    <div class="wis-remodal-block wis-remodal-info">
                        <div class="wis-remodal-text"><?php
                            if(mb_strlen($post['caption']) > 450){
                                echo wp_kses_post( mb_substr($post['caption'], 0,450) ) . "...";
                            } else {
                                echo wp_kses_post( $post['caption'] );
                            }

                            ?></div>
                        <div class="wis-remodal-stats">
                            <div class="wis-inline"><span class="fa fa-heart">&nbsp;</span><?php echo esc_html($post['likes']) ?></div> &nbsp;&nbsp;
                            <div class="wis-inline"><span class="fa fa-comment">&nbsp;</span><?php echo esc_html($post['comments']) ?></div>
                        </div>
                        <div class="isw-popup-buttons">
                            <div class="wis-inline wis-remodal-showcase-message" style="background-color: <?php echo esc_attr( $color ); ?>">
                                <a  href="<?php echo esc_url( "https://wa.me/{$phone}?text={$message_template}{$post['link']}" ); ?>" target="_blank"
                                >
                                    <img class="isw-center" style="height: 100%;" src="<?php echo esc_url( WIG_COMPONENT_URL . "/assets/icons/whatsapp.svg" ); ?>" alt="">
                                </a>
                            </div>
                            <div class="wis-inline wis-remodal-showcase-message" style="background-color: <?php echo esc_attr( $color ); ?>">
                                <a  href="mailto:<?php echo esc_attr( $mail ); ?>?body=<?php echo esc_attr( $message_template . $post['link'] ); ?>" target="_blank"
                                >
                                    <img class="isw-center" style="margin-top: 25%" src="<?php echo esc_url( WIG_COMPONENT_URL . "/assets/icons/mail.svg" ); ?>" alt="">
                                </a>
                            </div>
                        </div>
                        <div class="isw-popup-post-link-container" style="text-align: center; text-decoration: none;">
                            <a class="isw-popup-post-link" style="color: darkgrey !important; " href="<?php echo esc_url( $post['link'] ); ?>" target="_blank"><?php esc_html_e( 'go to the post', 'instagram-slider-widget' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach;?>
</div>
<div id="isw-wishlist-popup" class="remodal" data-remodal-id="isw-wishlist-popup"></div>

