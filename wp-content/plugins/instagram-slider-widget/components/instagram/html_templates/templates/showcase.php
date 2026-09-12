<?php
/* @var array $posts */
/* @var array $args */

$color   = $args['shopifeed_color'];
$columns = $args['shopifeed_columns'];

$mail  = get_option( 'admin_email' );
$phone = str_replace( [ '+', '-', '(', ')', ' ' ], '', $args['shopifeed_phone'] );

$whatsapp_svg = WIG_COMPONENT_URL . "/assets/icons/whatsapp.svg";
$mail_svg     = WIG_COMPONENT_URL . "/assets/icons/mail.svg";

$message_template = "Hello! I want to buy this one ";

if ( ! WIS_Feed::isMobile() ) {
	$caption_words = isset( $args['caption_words'] ) ? $args['caption_words'] : 20;
} else {
	$caption_words = isset( $args['m_caption_words'] ) ? $args['m_caption_words'] : 20;
}

?>
<script>
    let cached;
</script>

<div class="isw-showcase-container">
    <div class="isw-showcase-wishlist-container">
        <div class="isw-showcase-wishlist-block">
            <a
                    data-isw-phone="<?php echo esc_attr( $phone ); ?>"
                    data-isw-color="<?php echo esc_attr( $color ); ?>"
                    data-isw-wasvg="<?php echo esc_attr( $whatsapp_svg ); ?>"
                    data-isw-mailsvg="<?php echo esc_attr( $mail_svg ); ?>"
                    data-isw-email="<?php echo esc_attr( $mail ); ?>"
                    class="isw-wishlist-a" id="show-wishlist" style="padding: 8px 15px !important;background-color: <?php echo esc_attr( $color ); ?>">
                <span class="dashicons dashicons-heart isw-heart" style="color: white;"></span>
                <span class="isw-wishlist-text"><?php esc_html_e( 'WISHLIST', 'instagram-slider-widget' ); ?></span>
            </a>
        </div>
    </div>
	<?php foreach ( $posts as $post ): ?>
        <div class="isw-showcase-item" style="width: <?php echo esc_attr( 85 / $columns - 2.5 ); ?>%; height: <?php echo esc_attr( 900 / $columns + 150 * $columns / 3 ); ?>px;">
            <div class="isw-showcase-item-image-container">

                <a data-remodal-target="<?php echo esc_attr( $post['id'] ); ?>">
                    <img class="isw-showcase-item-image" src="<?php echo esc_url( $post['image'] ); ?>" alt="<?php echo esc_attr( $post['caption'] ); ?>">
					<?php if ( $post['type'] == 'GraphVideo' && $args['enable_icons'] ) { ?>
                        <div class="">
                            <i class="fa fa-video-camera isw-pic_icon pull-right" style="color: white; font-size: 15px;"></i>
                        </div>
					<?php } ?>
					<?php if ( $post['type'] == 'GraphSidecar' && $args['enable_icons'] ) { ?>
                        <div class="">
                            <i class="fa fa-clone isw-pic_icon pull-right" style="color: white; font-size: 15px;"></i>
                        </div>
					<?php } ?>
                </a>
            </div>
            <div class="isw-showcase-item-wishlist">
                <div class="isw-inline-block isw-showcase-item-wishlist-hit">
                    <span style="vertical-align: middle; color: #757575">
                        <?php
                        $words = explode( ',', $args['allowed_words'] );
                        echo esc_html( $words[0] );
                        ?>
                    </span>
                </div>
                <div class="isw-inline-block isw-showcase-item-wishlist-like">
                    <span data-isw-wish-id="<?php echo esc_attr( $post['id'] ); ?>"
                          data-isw-wish-img="<?php echo esc_url( $post['image'] ); ?>"
                          data-isw-wish-capt="<?php echo mb_strlen( $post['caption'] ) > 100 ? esc_attr( mb_substr( $post['caption'], 0, 100 ) . "..." ) : esc_attr( $post['caption'] ); ?>"
                          data-isw-wish-likes="<?php echo esc_attr( $post['likes'] ); ?>"
                          data-isw-wish-comms="<?php echo esc_attr( $post['comments'] ); ?>"
                          data-isw-wish-link="<?php echo esc_url( $post['link'] ); ?>"
                          class="dashicons dashicons-heart isw-wish-heart" style="vertical-align: middle;color: <?php echo esc_attr( $color ); ?>">

                    </span>
                    <script>
                        jQuery(document).ready(function ($) {
                            cached = getCookie('isw-cached-wishlist', true);
                            if (typeof cached !== 'undefined') {
                                for (let i = 0; i < cached.length; i++) {
                                    let cached_item = cached[i];
                                    if (cached_item.id == <?php echo esc_attr( $post['id'] ); ?>) {
                                        $("span[data-isw-wish-id='<?php echo esc_attr( $post['id'] ); ?>']").addClass("isw-like-clicked");
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
            <div class="isw-showcase-item-desc">
                <p class="isw-showcase-item-desc-text">
					<?php echo esc_html( wp_trim_words( $post['caption'], $caption_words ) ); ?>
                </p>
            </div>
            <div class="isw-showcase-item-buttons">
                <div class="isw-inline-block isw-button isw-showcase-item-details">
                    <a data-remodal-target="<?php echo esc_attr( $post['id'] ); ?>" class="wis-popup-a">
                        <button class="isw-showcase-details-button" style="background-color: <?php echo esc_attr( $color ); ?>">
                            <?php esc_html_e( 'Details', 'instagram-slider-widget' ); ?>
                        </button>
                    </a>
                </div>
                <div class="isw-inline-block isw-button isw-button-social isw-showcase-item-whatsapp" style="background-color: <?php echo esc_attr( $color ); ?>">
                    <a href="<?php echo esc_url( "https://wa.me/$phone?text=" . urlencode( $message_template . $post['link'] ) ); ?>" target="_blank"
                    >
                        <img class="isw-center" style="height: 100%;" src="<?php echo esc_url( $whatsapp_svg ); ?>" alt="">
                    </a>
                </div>
                <div class="isw-inline-block isw-button isw-button-social" style="background-color: <?php echo esc_attr( $color ); ?>">
                       <a href="<?php echo esc_url( 'mailto:' . $mail . '?body=' . rawurlencode( $message_template . $post['link'] ) ); ?>" target="_blank"
                    >
                        <div class="isw-mail-container">
                            <img class="isw-center isw-mail-ico" src="<?php echo esc_url( $mail_svg ); ?>" alt="">
                        </div>
                    </a>
                </div>
            </div>
        </div>
	<?php endforeach; ?>
</div>
