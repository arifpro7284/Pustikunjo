<?php
/**
 * Represents the account information of a user or entity.
 * This variable may hold details relevant to an account,
 * such as username, account ID, balance, or other properties
 * depending on the context of its usage within the application.
 *
 * Ensure proper validation and sanitization when handling
 * the account data to maintain security and data integrity.
 *
 * @var mixed $account The account details, structure depends on implementation context.
 */


/* @var $account \YoutubeFeed\Api\Channel\YoutubeChannelItem */
/* @var $args array */
$account = $args['account'];

$username        = $account->snippet->title;
$profile_pic_url = $account->snippet->thumbnails->default->url;
$posts_count     = $account->statistics->videoCount;
$followers       = ! $account->statistics->hiddenSubscriberCount ? sprintf( '%s %s', $account->statistics->subscriberCount, __( 'subscribers', 'instagram-slider-widget' ) ) : __( 'user has hidden the number of followers', 'instagram-slider-widget' );
$profile_url     = "https://youtube.com/channel/" . $account->snippet->channelId;
?>

<div class="wyt-feed-header">
    <div class="wyt-account-container">
        <div class="wyt-main-info">
            <img class="wyt-round" src="<?php echo esc_url( $profile_pic_url ) ?>"
                 alt=""
                 width="90" height="90">
            <div class="" style="margin-left: 3%;width: 100%; color: grey">
                <div class="wyt-header-info-username ellipsis" style="">
					<?php echo esc_html( $username ) ?>
                </div>
                <div class="wyt-header-info-followers">
					<?php echo esc_html( $followers ) ?>
                </div>
            </div>
        </div>
        <div class="wyt-subscribe-button-container">
            <div class="wyt-subscribe-button">
                <a href="https://youtube.com/channel/<?php echo esc_attr( $account->snippet->channelId ) ?>"
                   target="_blank"
                   style=" text-decoration: none;color: white; font-size: 1rem"><?php echo __( 'subscribe', 'instagram-slider-widget' ) ?></a>
            </div>
        </div>
    </div>
</div>
<br>
<hr>
