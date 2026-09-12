<?php
/**
 *  Represents a data structure or configuration related to social media integrations
 *  or links associated with an application or user. This variable may store details
 *  such as URLs, platform names, user handles, or other relevant information required
 *  for social connectivity.
 *
 *  It is assumed that the variable's specific contents and structure are determined
 *  elsewhere in the application or by a related context where it is used.
 *
 * @var array $args
 * @var \YoutubeFeed\Api\Channel\YoutubeChannelItem[] $accounts
 * @var array $accounts
 */

$social = $args['social'];

$count_accounts = ! empty( $accounts ) ? count( $accounts ) : 0;
?>
<form method="post">
	<?php wp_nonce_field( 'wis_yt_token', 'csrf_check' ); ?>
	<div class="wis-youtube-form-row">
		<div class="wyt-add-form">
			<input type="text" name="wyt_api_key" id="wyt_api_key" class="" style="width: 550px;"
			       value="<?php echo esc_attr( WIS_Plugin::app()->getOption( WYT_API_KEY_OPTION_NAME, '' ) ); ?>"
			       placeholder="<?php _e( 'YouTube API key', 'instagram-slider-widget' ) ?>">
		</div>
		<div class="wyt-add-form">
			<input type="submit" class="wyt-btn-Youtube-account"
			       value="<?php _e( 'Save', 'instagram-slider-widget' ) ?>">
		</div>
		<div class="" style="display: inline-block;">
			<a href="<?php echo esc_url( admin_url() ); ?>?page=manual-wisw"
			   target="_blank"><?php _e( 'How to get YouTube API key', 'instagram-slider-widget' ); ?></a>
		</div>
	</div>

	<div class="wis-youtube-form-row" style="margin-top: 15px;">
		<a class="wis-btn-youtube-account" target="_self" href="#" title="Add Account">
			<?php _e( 'Add channel', 'instagram-slider-widget' ) ?>
		</a>
	</div>

</form>


<?php
if ( ! empty( $accounts ) ) :
	?>
	<div class="wis-social-group"><?php _e( 'Connected channels', 'instagram-slider-widget' ); ?></div>
	<table class="widefat wis-table">
		<thead>
		<tr>
			<th class="wis-profile-picture"><?php _e( 'Image', 'instagram-slider-widget' ); ?></th>
			<th class="wis-profile-id"><?php _e( 'ID', 'instagram-slider-widget' ); ?></th>
			<th class="wis-profile-name"><?php _e( 'Name', 'instagram-slider-widget' ); ?></th>
			<th class="wis-profile-actions"><?php _e( 'Action', 'instagram-slider-widget' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $accounts as $channelId => $account ) {
			$delete_link = $this->getActionUrl( 'delete', [ 'social' => $social, 'account' => $channelId ] );
			?>
			<tr>
				<td class="wis-profile-picture">
					<img src="<?php echo esc_url( $account->snippet->thumbnails->default->url ) ?>" width="30" alt=""/>
				</td>
				<td class="wis-profile-id"><?php echo esc_html( $channelId ); ?></td>
				<td class="wis-profile-name">
					<a href="https://youtube.com/channel/<?php echo esc_attr( $channelId ) ?>"><?php echo esc_html( $account->snippet->title ); ?></a>
				</td>
				<td class="wis-profile-actions">
					<a href="<?php echo esc_url( $delete_link ); ?>" class="btn btn-danger wyt-close-button">
						<span class="dashicons dashicons-trash"></span><?php _e( 'Delete', 'instagram-slider-widget' ); ?>
					</a>
					<span class="spinner"
					      id="wis-delete-spinner-<?php echo esc_attr( $channelId ); ?>"></span>
				</td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<?php wp_nonce_field( $this->plugin->getPrefix() . 'settings_form', $this->plugin->getPrefix() . 'nonce' ); ?>
<?php endif; ?>

<div id="wis_add_ytaccount_modal" class="wis_accounts_modal wis_closed">
	<div class="wis_modal_header">
		<?php _e( 'Add Youtube channel', 'instagram-slider-widget' ); ?>
	</div>
	<div class="wis_modal_content">
		<form method="post">
			<?php wp_nonce_field( 'wis_yt_link', 'csrf_check' ); ?>
			<div class="wis-youtube-form-row">
				<div class="wyt-add-form" style="width: 100%;">
					<input type="text" name="wyt_feed_link" id="wyt_feed_link" class=""
					       placeholder="<?php _e( 'Channel link. Example: https://www.youtube.com/channel/UC0WP5P-ufpRfjbNrmOWwLBQ', 'instagram-slider-widget' ) ?>">
				</div>
				<div class="">
					<a href="https://support.google.com/youtube/answer/6180214"
					   target="_blank"><?php _e( 'How to get channel link', 'instagram-slider-widget' ); ?></a>
				</div>
			</div>

			<div class='wis-row-style'>
				<input type="submit" class='btn btn-primary'
				       value="<?php _e( 'Add channel', 'instagram-slider-widget' ); ?>">
			</div>
		</form>
	</div>
</div>
<div id="wis_add_ytaccount_modal_overlay" class="wis_modal_overlay wis_closed"></div>
