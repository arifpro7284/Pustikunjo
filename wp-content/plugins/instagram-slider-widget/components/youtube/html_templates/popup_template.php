<?php

/**
 * @var $account \YoutubeFeed\Api\Channel\YoutubeChannelItem
 */
$account = $args['account'];

/**
 * @var $videos \YoutubeFeed\Api\Video\YoutubeVideo[]
 */
$videos = $args['videos'];
$followers       = ! $account->statistics->hiddenSubscriberCount ?
    sprintf('%s %s',$account->statistics->subscriberCount , __('subscribers', 'yft')) :
    __('user has hidden the number of followers', 'yft');

?>
<div class="wyt-remodals">
<?php foreach ($videos as $video): ?>
    <?php $post_link = "https://youtube.com/?watch=" . $video->id->videoId?>
    <div class="remodal wyt-remodal" data-remodal-id="<?php echo esc_attr( $video->id->videoId ); ?>">
        <div class="wyt-video-block">
            <div class="remodal-wyt-video-container">
                <iframe class="remodal-wyt-video-iframe"
                        style="width: 100%;"
                        frameborder="0"
                        src="<?php echo esc_url( "https://www.youtube.com/embed/" . $video->id->videoId . "?enablejsapi=1&version=3&playerapiid=ytplayer" ); ?>">
                </iframe>
            </div>

            <div class="remodal-wyt-video-info">

                <div class="remodal-wyt-video-title">
                    <?php echo esc_html( $video->snippet->title ); ?>
                </div>

                <div class="remodal-wyt-video-specs">
                    <?php echo sprintf('%s %s', esc_html( $video->statistics->viewCount ), __('views', 'yft')); ?> •
                    <?php echo esc_html( wp_date( 'd M Y', strtotime( $video->snippet->publishedAt ) ) ); ?>
                </div>

                <div class="remodal-wyt-video-stats" >
                    <i class="fas fa-thumbs-up"></i>&nbsp;&nbsp;&nbsp;<?php echo esc_html( $video->statistics->likeCount ); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <i class="fas fa-thumbs-down"></i>&nbsp;&nbsp;&nbsp;<?php echo esc_html( $video->statistics->dislikeCount ); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <div class="" style="display: inline-block">
                        <p class="remodal-wyt-share-buttons" style="display: none">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_html($post_link) ?>" target="_blank"><span
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
                        <div class="remodal-wyt-share"><i class="fas fa-share"></i>&nbsp;&nbsp;&nbsp;<?php echo __('share', 'yft'); ?></div>
                    </div>

                </div>

                <hr>

                <a href="<?php echo esc_url( "https://youtube.com/channel/" . $account->snippet->channelId ); ?>" target="_blank" style="text-decoration: none; cursor: pointer; color: black">
                    <div class="remodal-wyt-channel-container">
                        <div class="" style="display: flex; align-items: center; width: 70%">
                            <img class="" style="border-radius: 50%;" src="<?php echo esc_url( $account->snippet->thumbnails->default->url ) ?>"
                                 alt=""
                                 width="50" height="50">
                            <div class="" style="margin-left: 3%;width: 100%">
                                <div class="wyt-header-info-username ellipsis" style="font-size: 1rem; font-weight: 600;">
                                    <?php echo esc_html( $account->snippet->title )?>
                                </div>
                                <div class="wyt-header-info-followers">
                                    <?php echo esc_html( $followers  ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <div class="remodal-wyt-video-desc desc-ellipsis-5-lines" id="<?php echo esc_attr( "desc_" . $video->id->videoId ); ?>">
                    <?php echo esc_html( $video->snippet->description ); ?>
                </div>

                <div class="wyt-show-more" data-desc-id="<?php echo esc_attr( $video->id->videoId ); ?>">
                    <?php echo __('...', 'yft'); ?>
                </div>
            </div>
        </div>

            <div class="wyt-comments-block">
                <div class="" style="margin: 5% 0">
                    <?php echo __('Comments', 'yft'); ?>
                </div>
            <?php foreach ($video->comments as $comment): ?>
                    <div class="wyt-comment-block" style="">
                        <div class="wyt-main-info" >
                            <img class="wyt-comment-round" src="<?php echo esc_url( $comment->authorProfileImageUrl ) ?>"
                                 alt=""
                                 width="50" height="50">
                            <div class="" style="margin-left: 10%;width: 100%">
                                <div class="wyt-header-comment-info-username ellipsis" style="">
                                    <?php echo esc_html( $comment->authorDisplayName ) ?>
                                </div>
                                <div class="wyt-header-comment-info-followers ellipsis-2-lines">
                                    <?php echo  esc_html( $comment->textDisplay ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="wyt-comment-stats" style="text-align: left; margin-top: 3%">
                            <i class="fas fa-thumbs-up"></i>&nbsp;&nbsp;&nbsp;<?php echo esc_html( $comment->likeCount ); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <i class="fas fa-thumbs-down"></i>&nbsp;&nbsp;
                        </div>
                    </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php endforeach; ?>
</div>

<script>
     window.addEventListener('DOMContentLoaded', function () {
        jQuery('.wyt-show-more').on('click', function () {
            let desc_id = jQuery(this).data('desc-id');
            let desc_classes = jQuery('#desc_'+desc_id).attr('class').split(/\s+/)

            let isOpened = true
            $.each(desc_classes, function (index, item){
                if(item === 'desc-ellipsis-5-lines'){
                    isOpened = false
                }
            })
            if(isOpened){
                jQuery('#desc_'+desc_id).addClass('desc-ellipsis-5-lines')
            } else {
                jQuery('#desc_'+desc_id).removeClass('desc-ellipsis-5-lines')
            }
        });
     })


</script>
