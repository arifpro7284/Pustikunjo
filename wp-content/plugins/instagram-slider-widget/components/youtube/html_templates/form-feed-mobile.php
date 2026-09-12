<?php

/* @var array $instance */
/* @var array $args */
/* @var array $sliders */
/* @var array $options_linkto */
/* @var array $w_id */
/* @var $_this */

use YoutubeFeed\Api\YoutubeApi;

$instance       = $args['instance'];
$sliders        = $args['sliders'];
$options_linkto = $args['options_linkto'];
$feed_id        = $args['instance']['id'] ?? '';

$search_for = $instance['m_search_for'] ?? '';
?>

<div class="wis-flex-content">
	<div class="wis-flex-content-column">
		<div id="wis-field-images_number" class="form-group">
			<div class="input-group">
				<label class="form-label form-inline"
				       for="m_images_number"><?php esc_html_e( 'Number of images to display:', 'instagram-slider-widget' ); ?>
				</label>
				<div class="input-group">
					<input class="form-input" type="number" min="1" max="" id="m_images_number" name="m_images_number"
					       value="<?php echo esc_attr( $instance['m_images_number'] ); ?>"/>
					<span class="input-group-addon"><?php esc_html_e( 'pcs', 'instagram-slider-widget' ); ?></span>
				</div>
			</div>

		</div>
		<div id="wis-field-words_in_caption" class="form-group">
			<div class="input-group">
				<label class="form-label form-inline" for="m_title_words">
					<?php esc_html_e( 'Number of words in caption:', 'instagram-slider-widget' ); ?>
				</label>
				<div class="input-group">
					<input class="form-input" type="number" min="0" max="" id="m_title_words" name="m_title_words"
					       value="<?php echo esc_attr( $instance['m_title_words'] ); ?>"/>
					<span class="input-group-addon"><?php esc_html_e( 'pcs', 'instagram-slider-widget' ); ?></span>
				</div>
			</div>
		</div>
		<div id="wis-field-request_by" class="form-group">
			<label class="form-label" for="m_request_by">
				<?php esc_html_e( 'Request videos by:', 'instagram-slider-widget' ); ?>
			</label>
			<select class="form-select" name="m_request_by" id="m_request_by">
				<option value="<?php echo esc_attr( YoutubeApi::orderByRelevance ); ?>" <?php selected( $instance['m_request_by'], YoutubeApi::orderByRelevance, true ); ?>><?php esc_html_e( 'Relevance', 'instagram-slider-widget' ); ?></option>
				<option value="<?php echo esc_attr( YoutubeApi::orderByDate ); ?>" <?php selected( $instance['m_request_by'], YoutubeApi::orderByDate, true ); ?>>     <?php esc_html_e( 'Date', 'instagram-slider-widget' ); ?></option>
				<option value="<?php echo esc_attr( YoutubeApi::orderByRating ); ?>" <?php selected( $instance['m_request_by'], YoutubeApi::orderByRating, true ); ?>>   <?php esc_html_e( 'Rating', 'instagram-slider-widget' ); ?></option>
				<option value="<?php echo esc_attr( YoutubeApi::orderByViewCount ); ?>" <?php selected( $instance['m_request_by'], YoutubeApi::orderByViewCount, true ); ?>><?php esc_html_e( 'View count', 'instagram-slider-widget' ); ?></option>
				<option value="<?php echo esc_attr( YoutubeApi::orderByUnspec ); ?>" <?php selected( $instance['m_request_by'], YoutubeApi::orderByUnspec, true ); ?>>   <?php esc_html_e( 'Unspecified', 'instagram-slider-widget' ); ?></option>
			</select>
		</div>
		<div id="wis-field-orderby" class="form-group">
			<label class="form-label" for="m_orderby"><?php esc_html_e( 'Order by', 'instagram-slider-widget' ); ?></label>
			<select class="form-select" name="m_orderby" id="m_orderby">
				<option value="date-ASC" <?php selected( $instance['m_orderby'], 'date-ASC', true ); ?>><?php esc_html_e( 'Date - Ascending', 'instagram-slider-widget' ); ?></option>
				<option value="date-DESC" <?php selected( $instance['m_orderby'], 'date-DESC', true ); ?>><?php esc_html_e( 'Date - Descending', 'instagram-slider-widget' ); ?></option>
				<option value="popular-ASC" <?php selected( $instance['m_orderby'], 'popular-ASC', true ); ?>><?php esc_html_e( 'Popularity - Ascending', 'instagram-slider-widget' ); ?></option>
				<option value="popular-DESC" <?php selected( $instance['m_orderby'], 'popular-DESC', true ); ?>><?php esc_html_e( 'Popularity - Descending', 'instagram-slider-widget' ); ?></option>
				<option value="rand" <?php selected( $instance['m_orderby'], 'rand', true ); ?>><?php esc_html_e( 'Random', 'instagram-slider-widget' ); ?></option>
			</select>
		</div>
		<div id="wis-field-images_link" class="form-group">
			<label class="form-label" for="m_yimages_link">
				<?php esc_html_e( 'Link to', 'instagram-slider-widget' ); ?>
			</label>
			<select class="form-select" name="m_yimages_link" id="m_yimages_link">
				<?php
				if ( count( $options_linkto ) ) {
					foreach ( $options_linkto as $key => $option ) {
						$selected = selected( $instance['m_yimages_link'], $key, false );
						echo "<option value='" . esc_attr( $key ) . "' " . esc_attr( $selected ) . ">" . esc_attr( $option ) . "</option>\n";
					}
				}
				?>
			</select>
		</div>
		<div id="wis-field-custom_url" class="form-group" style="<?php echo 'custom_url' !== $instance['m_yimages_link'] ? 'display:none;' : '' ?>">
			<label class="form-label" for="m_custom_url"><?php esc_html_e( 'Custom link:', 'instagram-slider-widget' ); ?></label>
			<input class="form-input" id="m_custom_url" name="m_custom_url" value="<?php echo esc_url_raw($instance['m_custom_url'] ); ?>"/>
			<span class="jr-description"><?php esc_html_e( '* use this field only if the above option is set to <strong>Custom Link</strong>', 'instagram-slider-widget' ); ?></span>
		</div>
		<div id="wis-field-show_feed_header" class="form-group">
			<label class="form-switch" for="m_show_feed_header">
				<input class="form-input" id="m_show_feed_header"
				       name="m_show_feed_header" type="checkbox"
				       value="1" <?php checked( '1', $instance['m_show_feed_header'] ); ?> />
				<i class="form-icon"></i>
				<?php esc_html_e( 'Show feed header', 'instagram-slider-widget' ); ?>
			</label>
		</div>
		<div id="wis-field-blocked_words" class="form-group">
			<label class="form-label" for="m_blocked_words">
				<?php esc_html_e( 'Block words', 'instagram-slider-widget' ); ?>
			</label>
			<input class="form-input" id="m_blocked_words" name="m_blocked_words"
			       value="<?php echo esc_attr( $instance['m_blocked_words'] ); ?>"/>
			<div class="jr-description"><?php esc_html_e( 'Enter comma-separated words. If one of them occurs in the image description, the image will not be displayed', 'instagram-slider-widget' ); ?></div>
		</div>
	</div>
	<div class="wis-flex-content-column">
		<div id="wis-field-template" class="form-group">
			<label class="form-label" for="m_template">
				<?php esc_html_e( 'Template', 'instagram-slider-widget' ); ?>
			</label>
			<select class="form-select" name="m_template" id="m_template">
				<?php
				if ( count( $sliders ) ) {
					foreach ( $sliders as $key => $slider ) {
						$selected = ( $instance['m_template'] == $key ) ? "selected='selected'" : '';
						echo "<option value='" . esc_attr( $key ) . "' " . esc_attr( $selected ) . ">" . esc_attr( $slider ) . "</option>\n";
					}
				}
				?>
			</select>
			<div id="m_masonry_notice"
			     class="masonry_notice jr-description <?php if ( 'masonry' != $instance['m_template'] ) {
				     echo 'hidden';
			     } ?>">
				<?php esc_html_e( "Not recommended for <strong>sidebar</strong>" ) ?></div>
		</div>
		<div class="default_settings">
			<div id="wis-field-columns" class="form-group">
				<label class="form-label form-inline" for="m_columns">
					<?php esc_html_e( 'Number of Columns:', 'instagram-slider-widget' ); ?>
				</label>
				<input class="form-input" id="m_columns" name="m_columns" type="number" min="1" max="10"
				       value="<?php echo esc_attr( $instance['m_columns'] ); ?>"/>
				<div class='jr-description'><?php esc_html_e( 'max is 10', 'instagram-slider-widget' ); ?></div>
			</div>
		</div>
	</div>
</div>

