<?php
/** @var array $args account data */
/** @var string $username account username */
/** @var string $profile_pic_url URL of account profile picture */
/** @var int $posts_count count of account posts */
/** @var int $followers count of account followers */
/** @var string $profile_url ULR of account */

$account = $args['account_data'];

$username = isset($account['username']) ? $account['username'] : '';
$profile_pic_url = $account['profile_picture_url'] ?? '';
$posts_count = $account['media_count'];
$followers = $account['followers_count'];
$profile_url = "https://www.instagram.com/$username/";
?>

<div class="wis-feed-header">
    <div class="wis-box">
        <?php if ($account['enable_stories'] && $account['has_stories']) : ?>
                <a class="wis-avatar-stories" id='avatar_stories' href="#">
        <?php else: ?>
                <a href="#" onclick="event.preventDefault()" style="text-decoration: none; color: black; border: 0 !important;">
        <?php endif; ?>
                    <div class="wis-header-img">
                        <div class="wis-round wis-header-neg">
                            <i class="wis-header-neg-icon"></i>
                        </div>
                        <img class="wis-round wis-account-pic-profile" style="position: relative"
                             src="<?php echo esc_url($profile_pic_url) ?>" alt=""
                             width="50" height="50">
                    </div>
                </a>
            <div class="wis-header-info">
                <a href="<?php echo esc_url( $profile_url ); ?>" target="_blank" style="text-decoration: none; color: black; border: 0 !important;">
                    <p class="wis-header-info-username"><?php echo esc_html($username) ?></p>
                </a>
                <p style="margin-top: 0; font-size: 11px">
                    <span class="fa fa-image">&nbsp;<?php echo esc_html($posts_count) ?></span>&nbsp;&nbsp;
                    <span class="fa fa-user">&nbsp;<?php echo esc_html($followers) ?></span>
                </p>
            </div>
    </div>

</div>
<br>
<div id="storytime"></div>
<?php if ($account['has_stories'] && $account['enable_stories']): ?>
    <style>
        .wis-account-pic-profile {
            border: double 2px transparent;
            background-image: linear-gradient(white, white),
            linear-gradient(to top right, red, purple);
            background-origin: border-box;
            background-clip: content-box, border-box;
        }
    </style>
<?php endif; ?>
