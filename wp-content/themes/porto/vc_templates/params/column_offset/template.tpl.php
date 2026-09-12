<?php
/**
 * Template for element param column offset.
 *
 * @var array $settings
 * @var string $value
 * @var array $data
 * @var Vc_Column_Offset $param
 * @var Vc_Column_Offset $sizes ::$size_types
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

global $porto_settings;

$gutter_width = isset( $porto_settings['grid-gutter-width'] ) ? (int) $porto_settings['grid-gutter-width'] : 30;

if ( isset( $porto_settings['container-width'] ) && (int) $porto_settings['container-width'] >= 1360 ) {
	$xxl = (int) $porto_settings['container-width'];
	$xl  = 1140;
} else {
	$xxl = 1360;
	$xl  = isset( $porto_settings['container-width'] ) ? (int) $porto_settings['container-width'] : 1140;
}

// WPBakery 9.0 changed the markup of this param (rows instead of table, toggles instead of checkboxes) and renamed the viewport icons.
$is_wpb_new = defined( 'WPB_VC_VERSION' ) && version_compare( WPB_VC_VERSION, '9.0', '>=' );

if ( $is_wpb_new ) {
	$layouts = array(
		'xs' => 'mobile-portrait',
		'sm' => 'mobile-landscape',
		'md' => 'tablet-portrait',
		'lg' => 'tablet-landscape',
		'xl' => 'desktop',
	);
} else {
	$layouts = array(
		'xs' => 'portrait-smartphones',
		'sm' => 'portrait-tablets',
		'md' => 'landscape-tablets',
		'lg' => 'default',
		'xl' => 'large-desktop',
	);
}

$sizes      = array(
	'xl' => 'Extra Large (>= ' . ( $xxl + $gutter_width * 2 ) . 'px)',
	'lg' => 'Large (>= ' . ( $xl + $gutter_width ) . 'px)',
	'md' => 'Medium (>= 992px)',
	'sm' => 'Small (>= 768px)',
	'xs' => 'Extra small',
);
$custom_tag = 'script';
?>
<div class="vc_column-offset" data-column-offset="true">
	<?php if ( '1' === vc_settings()->get( 'not_responsive_css' ) ) : ?>
		<div class="wpb_alert wpb_content_element vc_alert_rounded wpb_alert-warning">
			<div class="messagebox_text">
				<?php /* translators: WPBakery admin settings page url */ ?>
				<p><?php printf( esc_html__( 'Responsive design settings are currently disabled. You can enable them in WPBakery Page Builder %1$ssettings page%2$s by unchecking "Disable responsive content elements".', 'js_composer' ), '<a href="' . esc_url( admin_url( 'admin.php?page=vc-general' ) ) . '">', '</a>' ); ?></p>
			</div>
		</div>
	<?php endif ?>
	<input name="<?php echo esc_attr( $settings['param_name'] ); ?>"
			class="wpb_vc_param_value <?php echo esc_attr( $settings['param_name'] ); ?>
	<?php echo esc_attr( $settings['type'] ); ?> '_field" type="hidden" value="<?php echo esc_attr( $value ); ?>"/>
	<?php if ( $is_wpb_new ) : ?>
	<div class="vc_column-offset-table">
		<?php foreach ( $sizes as $key => $size ) : ?>
			<div class="wpb-param-row">
				<div class="wpb-screen-size"></div>
				<div class="wpb-offset-control wpb-param-heading">
					<label for="vc_col_<?php echo esc_attr( $key ); ?>_offset_size" class="wpb_element_label"> <?php esc_html_e( 'Offset', 'js_composer' ); ?> </label>
					<?php
					vc_include_template( 'editors/partials/param-info.tpl.php', array( 'description' => esc_html__( 'Control an empty space (offset) being added before the column.', 'js_composer' ) ) );
					?>
				</div>
				<div class="wpb-offset-control wpb-param-heading">
					<label for="vc_col_<?php echo esc_attr( $key ); ?>_size" class="wpb_element_label"> <?php esc_html_e( 'Width', 'js_composer' ); ?> </label>
					<?php
					vc_include_template( 'editors/partials/param-info.tpl.php', array( 'description' => esc_html__( 'Control the width of the column per device.', 'js_composer' ) ) );
					?>
				</div>
				<div class="wpb-offset-hide wpb-offset-hide--hidden">
					<?php
					WPB_Form_Field_Toggle::render(
						array(
							'id'         => 'vc_hidden-offset-toggle',
							'is_checked' => false,
							'title'      => esc_html__( 'Hide', 'js_composer' ),
						)
					);
					?>
				</div>
			</div>
			<div class="wpb-param-row wpb-param-row--controls vc_size-<?php echo esc_attr( $key ); ?>">
				<div class="wpb-screen-size vc_screen-size vc_screen-size-<?php echo esc_attr( $key ); ?>">
					<span title="<?php echo esc_attr( $size ); ?>">
						<i class="vc-composer-icon vc-c-viewport-<?php echo isset( $layouts[ $key ] ) ? esc_attr( $layouts[ $key ] ) : esc_attr( $key ); ?>"></i>
					</span>
				</div>
				<div class="wpb-offset-control">
					<?php
					// @codingStandardsIgnoreLine
					print $param->offsetControl( $key );
					?>
				</div>
				<div class="wpb-offset-control">
					<?php
					// @codingStandardsIgnoreLine
					print $param->sizeControl( $key );
					?>
				</div>
				<div class="wpb-offset-hide">
					<?php
					WPB_Form_Field_Toggle::render(
						array(
							'id'              => 'vc_hidden-' . $key,
							'name'            => 'vc_hidden-' . $key,
							'is_checked'      => in_array( 'vc_hidden-' . $key, $data, true ),
							'classes'         => 'wpb_toggle-input wpb_link-target-toggle vc_column_offset_field',
							'title'           => __( 'Hide', 'js_composer' ),
							'data_attributes' => array(
								'type' => 'toggle-' . $key,
							),
						)
					);
					?>
				</div>
			</div>
		<?php endforeach ?>
	</div>
	<?php else : ?>
	<table class="vc_table vc_column-offset-table">
		<tr>
			<th>
				<?php esc_html_e( 'Device', 'js_composer' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Offset', 'js_composer' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Width', 'js_composer' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Hide on device?', 'js_composer' ); ?>
			</th>
		</tr>
		<?php foreach ( $sizes as $key => $size ) : ?>
			<tr class="vc_size-<?php echo esc_attr( $key ); ?>">
				<td class="vc_screen-size vc_screen-size-<?php echo esc_attr( $key ); ?>">
					<span title="<?php echo esc_attr( $size ); ?>">
						<i class="vc-composer-icon vc-c-icon-layout_<?php echo isset( $layouts[ $key ] ) ? esc_attr( $layouts[ $key ] ) : esc_attr( $key ); ?>"></i>
					</span>
				</td>
				<td>
					<?php
					// @codingStandardsIgnoreLine
					print $param->offsetControl( $key );
					?>
				</td>
				<td>
					<?php
					// @codingStandardsIgnoreLine
					print $param->sizeControl( $key );
					?>
				</td>
				<td>
					<label>
						<input type="checkbox" name="vc_hidden-<?php echo esc_attr( $key ); ?>"
								value="yes"<?php echo in_array( 'vc_hidden-' . $key, $data, true ) ? ' checked="true"' : ''; ?>
								class="vc_column_offset_field">
					</label>
				</td>
			</tr>
		<?php endforeach ?>
	</table>
	<?php endif ?>
</div>
<<?php echo esc_attr( $custom_tag ); ?>>
	window.VcI8nColumnOffsetParam =
	<?php
	echo wp_json_encode(
		array(
			'inherit'         => esc_html__( 'Inherit: ', 'js_composer' ),
			'inherit_default' => esc_html__( 'Inherit from default', 'js_composer' ),
		)
	)
	?>
	;
</<?php echo esc_attr( $custom_tag ); ?>>
