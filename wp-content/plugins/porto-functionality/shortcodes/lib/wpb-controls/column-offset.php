<?php
/**
 * Column offset param - WPBakery 9.0 compatibility.
 *
 * WPBakery 9.0 rewrote the "Responsiveness" tab of the column elements. It added an `xl` viewport and
 * moved the backward compatibility of the legacy `width` attribute to the `md` row, both in
 * Vc_Column_Offset::sizeControl() and in the `vc.atts.column_offset` javascript.
 *
 * The rendering side was not moved along with it: wpb_translateColumnWidthToSpan() still turns the
 * width attribute into a `vc_col-sm-*` class, and vc_column_offset_class_merge() still looks for
 * `/vc_col-sm-\d+/` to decide whether an offset value replaces it. In the grid css `vc_col-sm-*` is
 * `min-width: 768px` while `vc_col-md-*` is `min-width: 992px`.
 *
 * So the width of an existing column shows up on the 992px row, and saving the popup writes it out as
 * `vc_col-md-*` plus a `vc_col-sm-inherit` marker - which drops the `vc_col-sm-*` class and moves the
 * column from 768px to 992px.
 *
 * This restores the width to the row it is actually rendered at. See assets/js/porto-wpb-column-offset.js
 * for the matching javascript.
 *
 * @since 7.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Vc_Column_Offset' ) || class_exists( 'Porto_Vc_Column_Offset' ) ) {
	return;
}

/**
 * Column offset param with the `width` attribute bound to the viewport it is rendered at.
 */
class Porto_Vc_Column_Offset extends Vc_Column_Offset {

	/**
	 * Viewport of the legacy `width` attribute.
	 *
	 * `sm` is `min-width: 768px`, which is what wpb_translateColumnWidthToSpan() outputs.
	 *
	 * @var string
	 */
	const WIDTH_SIZE = 'sm';

	/**
	 * Generates the HTML select element for size control.
	 *
	 * Same as the parent, except that the `width` attribute preselects the WIDTH_SIZE row instead of
	 * the `md` one.
	 *
	 * @param string $size Viewport key.
	 *
	 * @return string
	 */
	public function sizeControl( $size ) {
		$empty_label = 'xs' === $size ? esc_html__( 'Default', 'js_composer' ) : esc_html__( 'Inherit', 'js_composer' );
		$options     = array(
			array(
				'label' => $empty_label,
				'value' => '',
			),
		);

		// For non-xs viewports, check if user explicitly set "Inherit".
		$has_explicit_inherit = in_array( 'vc_col-' . $size . '-inherit', $this->data, true );

		// Fall back to the width param for backward compatibility.
		// Only if no value for this viewport exists in the offset AND no explicit inherit marker.
		$selected_bc_index = null;
		if ( self::WIDTH_SIZE === $size && ! $has_explicit_inherit ) {
			$has_size_in_offset = false;
			foreach ( $this->column_width_list as $index ) {
				if ( in_array( 'vc_col-' . self::WIDTH_SIZE . '-' . $index, $this->data, true ) ) {
					$has_size_in_offset = true;
					break;
				}
			}
			if ( ! $has_size_in_offset && isset( $this->settings['width'] ) ) {
				$selected_bc_index = $this->getColumnIndexFromWidth( $this->settings['width'] );
			}
		}

		foreach ( $this->column_width_list as $label => $index ) {
			$value       = 'vc_col-' . $size . '-' . $index;
			$is_selected = in_array( $value, $this->data, true );

			if ( null !== $selected_bc_index && $index === $selected_bc_index ) {
				$is_selected = true;
			}

			$options[] = array(
				'label'    => $label,
				'value'    => $value,
				'selected' => $is_selected,
			);
		}

		return WPB_Form_Field_Dropdown::get(
			array(
				'id'              => "vc_col_{$size}_size",
				'name'            => "vc_col_{$size}_size",
				'classes'         => 'vc_column_offset_field',
				'options'         => $options,
				'data_attributes' => array(
					'type' => "size-{$size}",
				),
			)
		);
	}
}

/**
 * Renders the form field for column offset settings.
 *
 * @param array  $settings Param settings.
 * @param string $value    Param value.
 *
 * @return string
 */
function porto_wpb_column_offset_form_field( $settings, $value ) {
	$column_offset = new Porto_Vc_Column_Offset( $settings, $value );

	return $column_offset->render();
}
