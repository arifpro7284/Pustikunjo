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
			<label class="form-label" for="m_fbimages_link">
				<?php esc_html_e( 'Link to', 'instagram-slider-widget' ); ?>
			</label>
			<select class="form-select" name="m_fbimages_link" id="m_fbimages_link">
				<?php
				if ( count( $options_linkto ) ) {
					foreach ( $options_linkto as $key => $option ) {
						$selected = selected( $instance['m_fbimages_link'], $key, false );
						echo "<option value='" . esc_attr( $key ) . "' " . esc_attr( $selected ) . ">" . esc_attr( $option ) . "</option>\n";
					}
				}
				?>
			</select>
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
						echo "<option value='" . esc_attr( $key ) . "' " . esc_attr( $selected ) .">" . esc_attr( $slider ) . "</option>\n";
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
		<div class="m_masonry_settings" style="<?php echo 'masonry' != $instance['m_template'] ? 'display:none;' : ''; ?>">
			<div id="wis-field-gutter" class="form-group">
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
			<div id="wis-field-masonry_image_width" class="form-group">
				<div class="input-group">
					<label class="form-label form-inline" for="m_masonry_post_width">
						<?php esc_html_e( 'Post width:', 'instagram-slider-widget' ); ?>
					</label>
					<div class="input-group">
						<input class="form-input" id="m_masonry_post_width" name="m_masonry_post_width" type="number" min="0" max=""
						       value="<?php echo esc_attr( $instance['m_masonry_post_width'] ); ?>"/>
						<span class="input-group-addon"> <?php esc_html_e( 'px', 'instagram-slider-widget' ); ?> </span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
