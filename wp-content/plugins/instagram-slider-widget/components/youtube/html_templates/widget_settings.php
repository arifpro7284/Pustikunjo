<?php
/** @var array $args*/
/** @var WYT_Widget $this */

use YoutubeFeed\Api\YoutubeApi;

$accounts       = $args['accounts'];
$sliders        = $args['sliders'];
$options_linkto = $args['options_linkto'];
$instance       = $args['instance'];

?>

<div class="wyt-container">
	<p>
		<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><strong><?php _e( 'Title:', 'instagram-slider-widget' ); ?></strong></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"
		       name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>"
		       value="<?php echo esc_attr( $instance['title'] ); ?>"/>
	</p>
	<p>
                <span class="wyt-search-for-container">
                    <?php
                    if ( count( $accounts ) ) {
	                    ?>
	                    <label for="<?php echo esc_attr($this->get_field_id( 'search' )); ?>"><strong><?php _e( 'Youtube feed', 'instagram-slider-widget' ); ?></strong>:</label>
	                    <select id="<?php echo esc_attr($this->get_field_id( 'search' )); ?>" class="widefat"
	                            name="<?php echo esc_attr($this->get_field_name( 'search' )); ?>"><?php
	                    foreach ( $accounts as $channelId => $account ) {
		                    $selected = $instance['search'] == $channelId ? "selected='selected'" : "";
		                    echo "<option value='" . esc_attr( $channelId ) . "' " . esc_attr( $selected ) ." >" . esc_attr( $account->snippet->title ) . "</option>";
	                    }
	                    ?>
	                    </select><?php
                    } else {
	                    echo "<a href='/wp-admin/admin.php?page=settings-wisw&tab=Youtube'>" . __( 'Add feed in settings', 'instagram-slider-widget' ) . "</a>";
                    }
                    ?>
                </span>
	</p>
	<p id="img_to_show">
		<label for="<?php echo esc_attr($this->get_field_id( 'images_number' )); ?>"><strong><?php _e( 'Number of images to display:', 'instagram-slider-widget' ); ?></strong>
			<input class="small-text" type="number" min="1" max=""
			       id="<?php echo esc_attr($this->get_field_id( 'images_number' )); ?>"
			       name="<?php echo esc_attr($this->get_field_name( 'images_number' )); ?>"
			       value="<?php echo esc_attr( $instance['images_number'] ); ?>"/>
		</label>
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id( 'refresh_hour' )); ?>"><strong><?php _e( 'Check for new posts every:', 'instagram-slider-widget' ); ?></strong>
			<input class="small-text" type="number" min="1" max="200"
			       id="<?php echo esc_attr($this->get_field_id( 'refresh_hour' )); ?>"
			       name="<?php echo esc_attr($this->get_field_name( 'refresh_hour' )); ?>"
			       value="<?php echo esc_attr( $instance['refresh_hour'] ); ?>"/>
			<span><?php _e( 'hours', 'instagram-slider-widget' ); ?></span>
		</label>
	</p>
	<p class="show_feed_header">
		<strong><?php _e( 'Show feed header:', 'instagram-slider-widget' ); ?></strong>
		<label class="switch" for="<?php echo esc_attr($this->get_field_id( 'show_feed_header' )); ?>">
			<input type="hidden" id="<?php echo esc_attr( $this->get_field_id( 'show_feed_header' ) );?>"
			       name="<?php echo esc_attr($this->get_field_name( 'show_feed_header' )); ?>"  value="0">
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'show_feed_header' )); ?>"
			       name="<?php echo esc_attr($this->get_field_name( 'show_feed_header' )); ?>" type="checkbox"
			       value="1" <?php checked( '1', $instance['show_feed_header'] ); ?> />
			<span class="slider round"></span>
		</label>
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id( 'template' )); ?>"><strong><?php _e( 'Template', 'instagram-slider-widget' ); ?></strong>
			<select class="widefat" name="<?php echo esc_attr($this->get_field_name( 'template' )); ?>"
			        id="<?php echo esc_attr($this->get_field_id( 'template' )); ?>">
				<?php
				if ( count( $sliders ) ) {
					foreach ( $sliders as $key => $slider ) {
						$selected = ( $instance['template'] == $key ) ? "selected='selected'" : '';
						echo "<option value='" . esc_attr( $key ) ."' " . esc_attr( $selected ) . ">" . esc_attr( $slider ) . "</option>\n";
					}
				}
				?>
			</select>
		</label>
	</p>
	<p class="<?php if ( 'default' != $instance['template'] && 'default-no-border' != $instance['template'] ) {echo 'hidden';} ?>">
		<label for="<?php echo esc_attr($this->get_field_id( 'columns' )); ?>"><strong><?php _e( 'Number of Columns:', 'instagram-slider-widget' ); ?></strong>
			<input class="small-text" id="<?php echo esc_attr($this->get_field_id( 'columns' )); ?>"
			       type="number" min="1" max="10"
			       name="<?php echo esc_attr($this->get_field_name( 'columns' )); ?>"
			       value="<?php echo esc_attr( $instance['columns'] ); ?>"/>
			<span class='wyt-description'><?php _e( 'max is 10 ( only for thumbnails template )', 'instagram-slider-widget' ); ?></span>
		</label>
	</p>
    <p>
        <label for="<?php echo esc_attr( $this->get_field_id('request_by') ); ?>"><strong><?php _e('Request videos by:', 'instagram-slider-widget'); ?></strong>
            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name('request_by' ) ); ?>"
                    id="<?php echo esc_attr( $this->get_field_id('request_by') ); ?>">
                <option value="<?php echo esc_attr( YoutubeApi::orderByRelevance ); ?>" <?php selected($instance['request_by'], YoutubeApi::orderByRelevance, true); ?>><?php _e('Relevance', 'instagram-slider-widget'); ?></option>
                <option value="<?php echo esc_attr( YoutubeApi::orderByDate ); ?>"      <?php selected($instance['request_by'], YoutubeApi::orderByDate, true); ?>>     <?php _e('Date', 'instagram-slider-widget'); ?></option>
                <option value="<?php echo esc_attr( YoutubeApi::orderByRating ); ?>"    <?php selected($instance['request_by'], YoutubeApi::orderByRating, true); ?>>   <?php _e('Rating', 'instagram-slider-widget'); ?></option>
                <option value="<?php echo esc_attr( YoutubeApi::orderByViewCount ); ?>" <?php selected($instance['request_by'], YoutubeApi::orderByViewCount, true); ?>><?php _e('View count', 'instagram-slider-widget'); ?></option>
                <option value="<?php echo esc_attr( YoutubeApi::orderByUnspec ); ?>" <?php selected($instance['request_by'], YoutubeApi::orderByUnspec, true); ?>>   <?php _e('Unspecified', 'instagram-slider-widget'); ?></option>
            </select>
        </label>
    </p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id( 'orderby' )); ?>"><strong><?php _e( 'Order by', 'instagram-slider-widget' ); ?></strong>
			<select class="widefat" name="<?php echo esc_attr($this->get_field_name( 'orderby' )); ?>"
			        id="<?php echo esc_attr($this->get_field_id( 'orderby' )); ?>">
				<option value="date-ASC" <?php selected( $instance['orderby'], 'date-ASC', true ); ?>><?php _e( 'Date - Ascending', 'instagram-slider-widget' ); ?></option>
				<option value="date-DESC" <?php selected( $instance['orderby'], 'date-DESC', true ); ?>><?php _e( 'Date - Descending', 'instagram-slider-widget' ); ?></option>
				<option value="popular-ASC" <?php selected( $instance['orderby'], 'popular-ASC', true ); ?>><?php _e( 'Popularity - Ascending', 'instagram-slider-widget' ); ?></option>
				<option value="popular-DESC" <?php selected( $instance['orderby'], 'popular-DESC', true ); ?>><?php _e( 'Popularity - Descending', 'instagram-slider-widget' ); ?></option>
				<option value="rand" <?php selected( $instance['orderby'], 'rand', true ); ?>><?php _e( 'Random', 'instagram-slider-widget' ); ?></option>
			</select>
		</label>
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id( 'yimages_link' )); ?>"><strong><?php _e( 'Link to', 'instagram-slider-widget' ); ?></strong>
			<select class="widefat" name="<?php echo esc_attr($this->get_field_name( 'yimages_link' )); ?>"
			        id="<?php echo esc_attr($this->get_field_id( 'yimages_link' )); ?>">
				<?php
				if ( count( $options_linkto ) ) {
					foreach ( $options_linkto as $key => $option ) {
						$selected = selected( $instance['yimages_link'], $key, false );
						echo "<option value='" . esc_attr( $key ) . "' " . esc_attr( $selected ) . ">" . esc_attr( $option ) . "</option>\n";
					}
				}
				?>
			</select>
		</label>
	</p>
	<p class="<?php if ( 'custom_url' !== $instance['yimages_link'] ) {echo 'hidden';} ?>">
		<label for="<?php echo esc_attr($this->get_field_id( 'custom_url' )); ?>"><?php _e( 'Custom link:', 'instagram-slider-widget' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'custom_url' )); ?>"
		       name="<?php echo esc_attr($this->get_field_name( 'custom_url' )); ?>"
		       value="<?php echo esc_attr( $instance['custom_url'] ); ?>"/>
		<span><?php _e( '* use this field only if the above option is set to <strong>Custom Link</strong>', 'instagram-slider-widget' ); ?></span>
	</p>
	<p>
		<label for="<?php echo esc_attr($this->get_field_id( 'blocked_words' )); ?>"><?php _e( 'Block words', 'instagram-slider-widget' ); ?>
			:</label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'blocked_words' )); ?>"
		       name="<?php echo esc_attr($this->get_field_name( 'blocked_words' )); ?>"
		       value="<?php echo esc_attr( $instance['blocked_words'] ); ?>"/>
		<span class="wyt-description"><?php _e( 'Enter comma-separated words. If one of them occurs in the image description, the image will not be displayed', 'instagram-slider-widget' ); ?></span>
	</p>


	<?php $widget_id = preg_replace( '/[^0-9]/', '', $this->id );
	if ( $widget_id != '' ) : ?>
		<p>
			<label for="jr_insta_shortcode"><?php _e( 'Shortcode of this Widget:', 'instagram-slider-widget' ); ?></label>
			<input id="jr_insta_shortcode" onclick="this.setSelectionRange(0, this.value.length)" type="text"
			       class="widefat" value="[cm_youtube_feed id=&quot;<?php echo esc_attr( $widget_id ); ?>&quot;]"
			       readonly="readonly" style="border:none; color:black; font-family:monospace;">
			<span class="wyt-description"><?php _e( 'Use this shortcode to display images with this widget configuration', 'instagram-slider-widget' ) ?></span>
		</p>
	<?php endif; ?>
</div>