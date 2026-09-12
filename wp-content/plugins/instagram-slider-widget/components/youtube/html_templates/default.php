<?php
/**
 * Represents an account object which may contain details
 * related to a user or entity's account data.
 *
 * This variable is typically used to store or manipulate
 * account-related information such as identification,
 * credentials, or status.
 *
 * The structure and properties of the account object
 * depend on the specific implementation or system
 * requirements.
 *
 * @var mixed $account The account object used within the application.
 */

/* @var $args array */
$account = $args['account'];

/* @var $videos \YoutubeFeed\Api\Video\YoutubeVideo[] */
$videos = $args['posts'];

$width = 100 / $args['columns'];

$yt_link = "https://www.youtube.com/watch?v=";
?>

<div class='wyoutube-videos-container'>
	<?php foreach ( $videos as $video ): ?>
		<?php echo 'yt_link' == $args['yimages_link'] ? sprintf( '<a href="%s%s" target="_blank" style="text-decoration: none;">', $yt_link, $video->id->videoId ) : '' ?>
        <div class="wyoutube-video-container" data-remodal-target="<?php echo esc_attr( $video->id->videoId ) ?>"
             style="margin-top: 10px; width: <?php echo esc_attr( $width - 2 ) ?>%; <?php echo 'ypopup' == $args['yimages_link'] ? 'cursor: pointer' : '' ?> ">
            <img src="<?php echo esc_url( $video->snippet->thumbnails->medium->url ) ?>" alt="">
            <div class="wyoutuve-video-title ellipsis-2-lines">
				<?php echo esc_html( $video->snippet->title ); ?>
            </div>
            <div class="woutube-video-specs">
                <div class="wyoutube-video-watches">
					<?php echo sprintf( "%s %s", $video->statistics->viewCount, __( 'views', 'instagram-slider-widget' ) ) ?>
                </div>
                <div class="wyoutube-video-publish">
					<?php echo esc_html( time_elapsed_string( $video->snippet->publishedAt ) ); ?>
                </div>
            </div>
        </div>
		<?php echo 'yt_link' == $args['yimages_link'] ? "</a>" : '' ?>
	<?php endforeach; ?>
</div>
