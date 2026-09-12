<?php
/* @var array $args */
/* @var WIS_Feeds $feeds */

$instance = $args['instance'];
$feeds    = $args['feeds'];
?>

<div class="jr-container">
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'instagram-slider-widget' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
		       value="<?php echo esc_attr( $instance['title'] ); ?>"/>
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'feed_id' ) ); ?>"><?php _e( 'Facebook feed', 'instagram-slider-widget' ); ?>:</label>
		<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'feed_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'feed_id' ) ); ?>">
			<?php foreach ( $feeds->feeds as $key => $feed ) {
				$selected = $instance['feed_id'] == $feed->instance['id'] ? "selected='selected'" : "";
				/* @var WIS_Facebook_Feed $feed */ ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $feed->title ); ?></option>
			<?php } ?>
		</select>
	</p>
</div>