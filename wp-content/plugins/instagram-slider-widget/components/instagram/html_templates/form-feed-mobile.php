<?php

/* @var array $instance */
/* @var array $args */
/* @var array $sliders */
/* @var array $options_linkto */
/* @var array $w_id */
/* @var $_this */

$instance          = $args['instance'];
$sliders           = $args['sliders'];
$options_linkto    = $args['options_linkto'];
$feed_id           = $args['instance']['id'] ?? '';

$search_for = $instance['search_for'] ?? '';
?>
<div class="wis-flex-content">
	<div class="wis-flex-content-column">
		<div id="wis-field-m_images_number" class="form-group">
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
		<div id="wis-field-m_caption_words" class="form-group"
			style="<?php echo ( 'thumbs' === $instance['m_template'] || 'thumbs-no-border' === $instance['m_template'] || 'highlight' === $instance['m_template'] || 'slick_slider' === $instance['m_template'] ) ? 'display:none;' : ''; ?>">
			<div class="input-group">
				<label class="form-label form-inline" for="m_caption_words">
					<?php esc_html_e( 'Number of words in caption:', 'instagram-slider-widget' ); ?>
				</label>
				<div class="input-group">
					<input class="form-input" type="number" min="0" max="" id="m_caption_words" name="m_caption_words"
					       value="<?php echo esc_attr( $instance['m_caption_words'] ); ?>"/>
					<span class="input-group-addon"><?php esc_html_e( 'pcs', 'instagram-slider-widget' ); ?></span>
				</div>
			</div>
		</div>
		<div id="wis-field-m_orderby" class="form-group">
			<label class="form-label" for="m_orderby"><?php esc_html_e( 'Order by', 'instagram-slider-widget' ); ?></label>
			<select class="form-select" name="m_orderby" id="m_orderby">
				<option value="date-ASC" <?php selected( $instance['m_orderby'], 'date-ASC', true ); ?>><?php esc_html_e( 'Date - Ascending', 'instagram-slider-widget' ); ?></option>
				<option value="date-DESC" <?php selected( $instance['m_orderby'], 'date-DESC', true ); ?>><?php esc_html_e( 'Date - Descending', 'instagram-slider-widget' ); ?></option>
				<option value="popular-ASC" <?php selected( $instance['m_orderby'], 'popular-ASC', true ); ?>><?php esc_html_e( 'Popularity - Ascending', 'instagram-slider-widget' ); ?></option>
				<option value="popular-DESC" <?php selected( $instance['m_orderby'], 'popular-DESC', true ); ?>><?php esc_html_e( 'Popularity - Descending', 'instagram-slider-widget' ); ?></option>
				<option value="rand" <?php selected( $instance['m_orderby'], 'rand', true ); ?>><?php esc_html_e( 'Random', 'instagram-slider-widget' ); ?></option>
			</select>
		</div>
		<div id="wis-field-m_images_link" class="form-group" style="<?php echo esc_attr( 'showcase' === $instance['m_template'] ? 'display:none;' : '' ); ?>">
			<label class="form-label" for="m_images_link">
				<?php esc_html_e( 'Link to', 'instagram-slider-widget' ); ?>
			</label>
			<select class="form-select" name="m_images_link"
			        id="m_images_link">
				<?php
				if ( count( $options_linkto ) ) {
					foreach ( $options_linkto as $key => $option ) {
						$selected = selected( $instance['m_images_link'], $key, false );
						echo "<option value='" . esc_attr( $key ) . "' " . esc_attr( $selected ) . ">" . esc_html( $option ) . "</option>\n";
					}
				}
				?>
			</select>
		</div>
		<div id="wis-field-m_custom_url" class="form-group" style="<?php echo 'm_custom_url' !== $instance['m_template'] ? 'display:none;' : ''; ?>">
			<label class="form-label" for="m_custom_url"><?php esc_html_e( 'Custom link:', 'instagram-slider-widget' ); ?></label>
			<input class="form-input" id="m_custom_url" name="m_custom_url" value="<?php echo esc_url_raw( $instance['m_custom_url'] ); ?>"/>
			<span class="jr-description"><?php esc_html_e( '* use this field only if the above option is set to <strong>Custom Link</strong>', 'instagram-slider-widget' ); ?></span>
		</div>
		<div id="wis-field-m_show_feed_header" class="form-group" style="<?php echo 'account_business' !== $search_for ? 'display:none;' : ''; ?>">
			<label class="form-switch" for="m_show_feed_header">
				<input class="form-input" id="m_show_feed_header"
				       name="m_show_feed_header" type="checkbox"
				       value="1" <?php checked( '1', $instance['m_show_feed_header'] ); ?> />
				<i class="form-icon"></i>
				<?php esc_html_e( 'Show feed header', 'instagram-slider-widget' ); ?>
			</label>
		</div>
		<div id="wis-field-m_enable_stories" class="form-group" style="<?php echo 'account_business' !== $search_for ? 'display:none;' : ''; ?>">
			<label class="form-switch" for="m_enable_stories">
				<input class="form-input" id="m_enable_stories"
					name="m_enable_stories" type="checkbox"
					value="1" <?php checked( '1', $instance['m_enable_stories'] ); ?>
				/>
				<i class="form-icon"></i><?php esc_html_e( 'Show Stories', 'instagram-slider-widget' ); ?>
			</label>
			<div class="jr-description">
				<?php esc_html_e( 'Works only with business account.', 'instagram-slider-widget' ); ?>
			</div>
		</div>
		<div id="wis-field-m_enable_ad" class="form-group">
			<label class="form-switch" for="m_enable_ad">
				<input class="form-input" id="m_enable_ad" name="m_enable_ad" type="checkbox"
				       value="1" <?php checked( '1', $instance['m_enable_ad'] ); ?> />
				<i class="form-icon"></i><?php esc_html_e( 'Enable author\'s ad', 'instagram-slider-widget' ); ?>
			</label>
		</div>
		<div id="wis-field-m_enable_icons" class="form-group">
			<label class="form-switch" for="m_enable_icons">
				<input class="form-input" id="m_enable_icons" name="m_enable_icons" type="checkbox"
				       value="1" <?php checked( '1', $instance['m_enable_icons'] ); ?> />
				<i class="form-icon"></i><?php esc_html_e( 'Enable instagram icons', 'instagram-slider-widget' ); ?>
			</label>
		</div>
		<div id="wis-field-m_blocked_words" class="form-group" style="<?php echo 'hashtag' === $search_for ? 'display:none;' : ''; ?>">
			<label class="form-label" for="m_blocked_words">
				<?php esc_html_e( 'Block words', 'instagram-slider-widget' ); ?>
			</label>
			<input class="form-input" id="m_blocked_words" name="m_blocked_words"
			       value="<?php echo esc_attr( $instance['m_blocked_words'] ); ?>"/>
			<div class="jr-description"><?php esc_html_e( 'Enter comma-separated words. If one of them occurs in the image description, the image will not be displayed', 'instagram-slider-widget' ); ?></div>
		</div>
		<div id="wis-field-m_allowed_words" class="form-group" style="<?php echo 'hashtag' === $search_for ? 'display:none;' : ''; ?>">
			<label class="form-label" for="m_allowed_words">
				<?php esc_html_e( 'Allow words', 'instagram-slider-widget' ); ?>
			</label>
			<input class="form-input" id="m_allowed_words"
			       name="m_allowed_words"
			       value="<?php echo esc_attr( $instance['m_allowed_words'] ); ?>"/>
			<div class="jr-description"><?php esc_html_e( 'Enter comma-separated words. If one of them occurs in the image description, the image will be displayed', 'instagram-slider-widget' ); ?></div>
		</div>
	</div>
	<div class="wis-flex-content-column">
		<div id="wis-field-m_template" class="form-group">
			<label class="form-label" for="m_template">
				<?php esc_html_e( 'Template', 'instagram-slider-widget' ); ?>
			</label>
			<select class="form-select" name="m_template" id="m_template">
				<?php
				if ( count( $sliders ) ) {
					foreach ( $sliders as $key => $slider ) {
						$selected = ( $instance['m_template'] === $key ) ? "selected='selected'" : '';
						echo "<option value='" . esc_attr( $key ) . "' " . esc_attr( $selected ) .">" . esc_attr( $slider ) . "</option>\n";
					}
				}
				?>
			</select>
			<div id="masonry_notice"
			     class="masonry_notice jr-description <?php if ( 'masonry' !== $instance['m_template'] ) {
				     echo 'hidden';
			     } ?>">
				<?php esc_html_e( "Not recommended for <strong>sidebar</strong>" ) ?></div>
		</div>
		<div class="m_thumbs_settings" style="<?php echo ( 'thumbs' !== $instance['m_template'] && 'thumbs-no-border' !== $instance['m_template'] ) ? 'display:none;' : ''; ?>">
			<div id="wis-field-m_columns" class="form-group">
				<label class="form-label form-inline" for="m_columns">
					<?php esc_html_e( 'Number of Columns:', 'instagram-slider-widget' ); ?>
				</label>
				<input class="form-input" id="m_columns" name="m_columns" type="number" min="1" max="10"
				       value="<?php echo esc_attr( $instance['m_columns'] ); ?>"/>
				<div class='jr-description'><?php esc_html_e( 'max is 10 ( only for thumbnails template )', 'instagram-slider-widget' ); ?></div>
			</div>
		</div>
		<div class="m_masonry_settings" style="<?php echo 'masonry' !== $instance['m_template'] ? 'display:none;' : ''; ?>">
			<div id="wis-field-m_gutter" class="form-group">
				<div class="input-group">
					<label class="form-label form-inline" for="m_gutter">
						<?php esc_html_e( 'Vertical space between item elements:', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input class="form-input" id="m_gutter" name="m_gutter" type="number" min="0" max=""
						       value="<?php echo esc_attr( $instance['m_gutter'] ); ?>"/>
						<span class="input-group-addon"><?php esc_html_e( 'px', 'instagram-slider-widget' ); ?></span>
					</div>
				</div>
			</div>
			<div id="wis-field-m_masonry_image_width" class="form-group">
				<div class="input-group">
					<label class="form-label form-inline" for="m_masonry_image_width">
						<?php esc_html_e( 'Image width:', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input class="form-input" id="m_masonry_image_width" name="m_masonry_image_width" type="number" min="0" max=""
						       value="<?php echo esc_attr( $instance['m_masonry_image_width'] ); ?>"/>
						<span class="input-group-addon"> <?php esc_html_e( 'px', 'instagram-slider-widget' ); ?> </span>
					</div>
				</div>
			</div>
		</div>
        <div class="m_masonry_lite_settings" style="<?php echo 'masonry_lite' !== $instance['m_template'] ? 'display:none;' : ''; ?>">
            <div id="wis-field-masonry-cols" class="form-group">
                <div class="input-group">
                    <label class="form-label form-inline" for="masonry_lite_cols">
						<?php esc_html_e( 'Columns:', 'instagram-slider-widget' ); ?>
                    </label>
                    <div class="input-group">
                        <input class="form-input" id="m_masonry_lite_cols" name="m_masonry_lite_cols"
                               type="number" min="1"
                               max="6"
                               value="<?php echo esc_attr( $instance['m_masonry_lite_cols'] ); ?>"/>
                        <span class="input-group-addon"><?php esc_html_e( 'cols', 'instagram-slider-widget' ); ?></span>
                    </div>
                </div>
            </div>
            <div id="wis-field-masonry-gap" class="form-group">
                <div class="input-group">
                    <label class="form-label form-inline" for="masonry_lite_gap">
				        <?php esc_html_e( 'Gap:', 'instagram-slider-widget' ); ?>
                    </label>
                    <div class="input-group">
                        <input class="form-input" id="m_masonry_lite_gap" name="m_masonry_lite_gap"
                               type="number"
                               value="<?php echo esc_attr( $instance['m_masonry_lite_gap'] ); ?>"/>
                        <span class="input-group-addon"><?php esc_html_e( 'px', 'instagram-slider-widget' ); ?></span>
                    </div>
                </div>
            </div>
        </div>
		<div class="m_slick_settings" style="<?php echo 'slick_slider' !== $instance['m_template'] ? 'display:none;' : ''; ?>">
			<div id="wis-field-m_enable_control_buttons" class="form-group">
				<label class="form-switch" for="m_enable_control_buttons">
					<input class="form-input" id="m_enable_control_buttons" name="m_enable_control_buttons" type="checkbox" value="1"
						<?php checked( '1', $instance['m_enable_control_buttons'] ); ?> />
					<i class="form-icon"></i><?php esc_html_e( 'Enable control buttons', 'instagram-slider-widget' ); ?>
				</label>
			</div>
			<div id="wis-field-m_keep_ratio" class="form-group">
				<label class="form-switch" for="m_keep_ratio">
					<input class="form-input" id="m_keep_ratio" name="m_keep_ratio" type="checkbox" value="1"
						<?php checked( '1', $instance['m_keep_ratio'] ); ?> />
					<i class="form-icon"></i><?php esc_html_e( 'Keep 1x1 Instagram ratio', 'instagram-slider-widget' ); ?>
				</label>
			</div>
			<div id="wis-field-m_slick_img_size" class="form-group">
				<div class="input-group">
					<label class="form-label" for="m_slick_img_size">
						<?php esc_html_e( 'Images size: ', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input class="form-input" type="number" min="1" max="500" step="1" id="m_slick_img_size" name="m_slick_img_size"
						       value="<?php echo esc_attr( $instance['m_slick_img_size'] ); ?>"/>
						<span class="input-group-addon"><?php esc_html_e( 'px', 'instagram-slider-widget' ); ?></span>
					</div>
				</div>
			</div>
			<div id="wis-field-m_slick_slides_to_show" class="form-group">
				<div class="input-group">
					<label class="form-label" for="m_slick_slides_to_show">
						<?php esc_html_e( 'Pictures per slide:', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input class="form-input" id="m_slick_slides_to_show" type="number" min="1" max="" step="1" name="m_slick_slides_to_show"
						       value="<?php echo esc_attr( $instance['m_slick_slides_to_show'] ); ?>"/>
						<span class="input-group-addon"><?php esc_html_e( 'pcs', 'instagram-slider-widget' ); ?></span>
					</div>
				</div>
			</div>
			<div id="wis-field-m_slick_sliding_speed" class="form-group">
				<div class="input-group">
					<label class="form-label" for="m_slick_sliding_speed">
						<?php esc_html_e( 'Sliding speed:', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input class="form-input" id="m_slick_sliding_speed" type="number" min="1" max="" name="m_slick_sliding_speed"
						       value="<?php echo esc_attr( $instance['m_slick_sliding_speed'] ); ?>"/>
						<span class="input-group-addon"><?php esc_html_e( 'ms', 'instagram-slider-widget' ); ?></span>
					</div>
				</div>
			</div>
			<div id="wis-field-m_slick_slides_padding" class="form-group">
				<label class="form-switch" for="m_slick_slides_padding">
					<input class="form-input" id="m_slick_slides_padding" name="m_slick_slides_padding" type="checkbox"
					       value="1" <?php checked( '1', $instance['m_slick_slides_padding'] ); ?> />
					<i class="form-icon"></i><?php esc_html_e( 'Space between pictures', 'instagram-slider-widget' ); ?>
				</label>
			</div>
		</div>
		<div class="m_highlight_settings" style="<?php echo 'highlight' !== $instance['m_template'] ? 'display:none;' : ''; ?>">
			<div id="wis-field-m_highlight_offset" class="form-group">
				<div class="input-group">
					<label class="form-label" for="m_highlight_offset">
						<?php esc_html_e( 'Offset', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input type="number" min="1" max="" class="form-input" id="m_highlight_offset" name="m_highlight_offset"
						       value="<?php echo esc_attr( $instance['m_highlight_offset'] ); ?>"/>
					</div>
				</div>
			</div>
			<div id="wis-field-m_highlight_pattern" class="form-group">
				<div class="input-group">
					<label class="form-label" for="m_highlight_pattern">
						<?php esc_html_e( 'Pattern', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input type="number" min="0" class="form-input" id="m_highlight_pattern" name="m_highlight_pattern"
						       value="<?php echo esc_attr( $instance['m_highlight_pattern'] ); ?>"/>
					</div>
				</div>
			</div>
		</div>
		<div class="m_shopifeed_settings" style="<?php echo 'showcase' !== $instance['m_template'] ? 'display:none;' : ''; ?>">
			<div id="wis-field-shopifeed_phone" class="form-group">
				<label class="form-label" for="m_shopifeed_phone">
					<?php esc_html_e( 'Phone', 'instagram-slider-widget' ); ?>
				</label>
				<input type="text" class="form-input" id="m_shopifeed_phone" name="m_shopifeed_phone"
				       value="<?php echo esc_attr( $instance['m_shopifeed_phone'] ); ?>"/>
				<div class="jr-description"><?php esc_html_e( "Use for whatsapp messages", 'instagram-slider-widget' ) ?></div>
			</div>

			<div id="wis-field-m_shopifeed_color" class="form-group">
				<label class="form-label form-inline" for="m_shopifeed_color">
					<?php esc_html_e( 'Buttons Color', 'instagram-slider-widget' ); ?>
				</label>
				<input type="color" class="shopifeed_color form-inline" id="m_shopifeed_color" name="m_shopifeed_color"
				       style="border: none !important;"
				       value="<?php echo esc_attr( $instance['m_shopifeed_color'] ); ?>"/>
			</div>
			<div id="wis-field-m_shopifeed_columns" class="form-group">
				<div class="input-group">
					<label class="form-label" for="m_shopifeed_columns">
						<?php esc_html_e( 'Columns count', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input type="number" min="1" max="6" id="m_shopifeed_columns" name="m_shopifeed_columns"
						       value="<?php echo esc_attr( $instance['m_shopifeed_columns'] ); ?>"/>
					</div>
				</div>
			</div>

		</div>
		<div class="m_slider_normal_settings" style="<?php echo ( 'slider' !== $instance['m_template'] && 'slider-overlay' !== $instance['m_template'] ) ? 'display:none;' : ''; ?>">
			<div id="wis-field-m_controls" class="form-group">
				<label class="form-label"><?php esc_html_e( 'Slider Navigation Controls:', 'instagram-slider-widget' ); ?></label>
				<label class="form-radio form-inline">
					<input type="radio" id="m_controls" name="m_controls" value="prev_next" <?php checked( 'prev_next', $instance['m_controls'] ); ?> />
					<i class="form-icon"></i><?php esc_html_e( 'Prev & Next', 'instagram-slider-widget' ); ?>
				</label>
				<label class="form-radio form-inline">
					<input type="radio" id="m_controls" name="m_controls"
					       value="numberless" <?php checked( 'numberless', $instance['m_controls'] ); ?> />
					<i class="form-icon"></i><?php esc_html_e( 'Dotted', 'instagram-slider-widget' ); ?>
				</label>
				<label class="form-radio form-inline">
					<input type="radio" id="m_controls" name="m_controls" value="none" <?php checked( 'none', $instance['m_controls'] ); ?> />
					<i class="form-icon"></i><?php esc_html_e( 'No Navigation', 'instagram-slider-widget' ); ?>
				</label>
			</div>
			<div id="wis-field-m_animation" class="form-group">
				<label class="form-label"><?php esc_html_e( 'Slider Animation:', 'instagram-slider-widget' ); ?></label>
				<label class="form-radio form-inline">
					<input type="radio" id="m_animation" name="m_animation" value="slide" <?php checked( 'slide', $instance['m_animation'] ); ?> />
					<i class="form-icon"></i><?php esc_html_e( 'Slide', 'instagram-slider-widget' ); ?>
				</label>
				<label class="form-radio form-inline">
					<input type="radio" id="m_animation" name="m_animation" value="fade" <?php checked( 'fade', $instance['m_animation'] ); ?> />
					<i class="form-icon"></i><?php esc_html_e( 'Fade', 'instagram-slider-widget' ); ?>
				</label>
			</div>
			<div id="wis-field-m_slidespeed" class="form-group">
				<div class="input-group">
					<label class="form-label" for="m_slidespeed">
						<?php esc_html_e( 'Slide Speed:', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input type="number" min="1000" step="100" class="form-input" id="m_slidespeed" name="m_slidespeed"
						       value="<?php echo esc_attr( $instance['m_slidespeed'] ); ?>"/>
						<span class="input-group-addon"><?php esc_html_e( 'ms', 'instagram-slider-widget' ); ?></span>
					</div>
				</div>
			</div>
			<div id="wis-field-m_description" class="form-group">
				<label class="form-label" for="m_description"><?php esc_html_e( 'Slider Text Description:', 'instagram-slider-widget' ); ?></label>
				<select size='3' class='form-select' id="m_description" name="m_description[]"
				        multiple="multiple">
					<option class="<?php echo esc_attr( 'hashtag' === $search_for ? 'hidden' : '' ); ?>"
					        value='username' <?php $_this->selected( $instance['m_description'], 'username' ); ?>><?php esc_html_e( 'Username', 'instagram-slider-widget' ); ?></option>
					<option value='time'<?php $_this->selected( $instance['m_description'], 'time' ); ?>><?php esc_html_e( 'Time', 'instagram-slider-widget' ); ?></option>
					<option value='caption'<?php $_this->selected( $instance['m_description'], 'caption' ); ?>><?php esc_html_e( 'Caption', 'instagram-slider-widget' ); ?></option>
				</select>
				<span class="jr-description"><?php esc_html_e( 'Hold ctrl and click the fields you want to show/hide on your slider. Leave all unselected to hide them all. Default all selected.', 'instagram-slider-widget' ) ?></span>
			</div>
		</div>
	</div>
</div>