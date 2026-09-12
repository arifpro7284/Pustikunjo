<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class WPBakeryCssEditor
 *
 * Overrides WPBakery's `css_editor` param (include/params/css_editor/css_editor.php)
 * to add responsive (per breakpoint) margin / border-width / padding options to the
 * default "Design Options" panel.
 *
 * The responsive markup is consumed by the theme scripts:
 * - porto/js/admin/vc-backend-editor.js  -> portoCssEditorResponsive()
 * - porto/js/admin/vc-frontend-editor.js -> portoCssEditorResponsive()
 * and rendered on frontend by Porto_Shortcodes::generate_shortcode_css() through the
 * `custom_css_response` / `css_params` shortcode attributes.
 *
 * @since 7.2.0
 * @since 8.0.0 Updated for WPBakery 9.0 (template based rendering, unit selectors,
 *              linked values toggle, per-corner border radius, scoped field ids).
 */
if ( ! class_exists( 'WPBakeryCssEditor' ) ) {
    /**
     * Class WPBakeryCssEditor
     */
    class WPBakeryCssEditor {
        /**
         * Holds the settings array for the CSS editor.
         *
         * @var array
         */
        protected $settings = [];
        /**
         * Stores the current value for the CSS editor.
         *
         * @var string
         */
        protected $value = '';

        /**
         * Contains the positions for the CSS properties.
         *
         * @var array
         */
        protected $positions = [
            'top',
            'right',
            'bottom',
            'left',
        ];

        /**
         * Stores the parameters passed to the CSS editor.
         *
         * @var array
         */
        public $params = [];

        /**
         * Setters/Getters
         *
         * @param null $settings
         *
         * @return array
         */
        public function settings( $settings = null ) {
            if ( is_array( $settings ) ) {
                $this->settings = $settings;
            }

            return $this->settings;
        }

        /**
         * Retrieves a specific setting by key.
         *
         * @param string $key
         *
         * @return string
         */
        public function setting( $key ) {
            return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : '';
        }

        /**
         * Sets or gets the current value of the CSS editor.
         *
         * @param null $value
         *
         * @return string
         */
        public function value( $value = null ) {
            if ( is_string( $value ) ) {
                $this->value = $value;
            }

            return $this->value;
        }

        /**
         * Sets or gets the parameters array.
         *
         * @param null $values
         *
         * @return array
         */
        public function params( $values = null ) {
            if ( is_array( $values ) ) {
                $this->params = $values;
            }

            return $this->params;
        }

        /**
         * Whether the installed WPBakery renders this param with the 9.0 markup.
         *
         * WPBakery 9.0 moved the css_editor markup into a template
         * (include/templates/params/css_editor/template.php) and introduced the
         * WPB_Form_Field_* classes, the unit selectors and the border radius controls.
         * None of them exist in older versions, so the legacy markup is rendered instead.
         *
         * @return bool
         * @since 8.0.0
         */
        protected function is_wpb_new() {
            return defined( 'WPB_VC_VERSION' ) && version_compare( WPB_VC_VERSION, '9.0', '>=' );
        }

        /**
         * Renders param form field output.
         *
         * @return mixed
         * @see vc_filter: vc_css_editor - hook to override output of this method
         */
        public function render() {
            if ( $this->is_wpb_new() ) {
                $output = vc_get_template( 'params/css_editor/template.php', [
                    'css_editor' => $this,
                ] );
            } else {
                $output = $this->porto_render_legacy();
            }

            return apply_filters( 'vc_css_editor', $output );
        }

        /**
         * Renders the param form field output for WPBakery below 9.0.
         *
         * Mirrors the markup of WPBakeryCssEditor::render() of WPBakery 8.x so the
         * editor scripts and styles of those versions keep working.
         *
         * @return string
         * @since 8.0.0
         */
        protected function porto_render_legacy() {
            $output = '<div class="vc_css-editor vc_row vc_ui-flex-row" data-css-editor="true">';
            $output .= $this->onionLayout();
            $output .= sprintf(
                '<div class="vc_col-xs-5 vc_settings"><label>%s</label><div class="color-group"><div class="wpb-color-picker"></div><input type="text" name="border_color" value="" data-default-value="" class="vc_color-control vc_ui-hidden"></div><label>%s</label><div class="vc_border-style"><select name="border_style" class="vc_border-style">%s</select></div><label>%s</label><div class="vc_border-radius"><select name="border_radius" class="vc_border-radius">%s</select></div><label>%s</label><div class="color-group"><div class="wpb-color-picker"></div><input type="text" name="background_color" value="" data-default-value="" class="vc_color-control vc_ui-hidden"></div><div class="vc_background-image">%s<div class="vc_clearfix"></div></div><div class="vc_background-style"><select name="background_style" class="vc_background-style">%s</select></div><label>%s</label><label class="vc_checkbox"><input type="checkbox" name="simply" class="vc_simplify" value=""> %s</label></div>',
                esc_html__( 'Border color', 'js_composer' ),
                esc_html__( 'Border style', 'js_composer' ),
                $this->porto_get_options_html( $this->get_border_style_options(), esc_html__( 'Theme defaults', 'js_composer' ) ),
                esc_html__( 'Border radius', 'js_composer' ),
                $this->porto_get_options_html( $this->get_border_radius_options() ),
                esc_html__( 'Background', 'js_composer' ),
                $this->get_background_image_control(),
                $this->porto_get_options_html( $this->get_background_style_options(), esc_html__( 'Theme defaults', 'js_composer' ) ),
                esc_html__( 'Box controls', 'js_composer' ),
                esc_html__( 'Simplify controls', 'js_composer' )
            );

            $output .= sprintf( '<input name="%s" class="wpb_vc_param_value  %s %s_field" type="hidden" value="%s"/>', esc_attr( $this->setting( 'param_name' ) ), esc_attr( $this->setting( 'param_name' ) ), esc_attr( $this->setting( 'type' ) ), esc_attr( $this->value() ) );

            $output .= '</div><div class="vc_clearfix"></div>';
            $custom_tag = 'script';
            $output .= '<' . $custom_tag . ' type="text/html" id="vc_css-editor-image-block"><li class="added"><div class="inner" style="width: 80px; height: 80px; overflow: hidden;text-align: center;"><img src="{{ img.url }}?id={{ img.id }}" data-image-id="{{ img.id }}" class="vc_ce-image<# if (!_.isUndefined(img.css_class)) {#> {{ img.css_class }}<# }#>">  </div><a href="#" class="vc_icon-remove"><i class="vc-composer-icon vc-c-icon-close"></i></a></li></' . $custom_tag . '>';

            return $output;
        }

        /**
         * Builds `<option>` markup out of the option arrays used by WPBakery 9.0.
         *
         * @param array  $options Options as returned by get_*_options().
         * @param string $placeholder Optional label of an additional empty first option.
         *
         * @return string
         * @since 8.0.0
         */
        protected function porto_get_options_html( $options, $placeholder = '' ) {
            $output = '';
            if ( '' !== $placeholder ) {
                $output .= '<option value="">' . esc_html( $placeholder ) . '</option>';
            }
            foreach ( $options as $option ) {
                $output .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr( $option['value'] ),
                    empty( $option['selected'] ) ? '' : ' selected',
                    esc_html( $option['label'] )
                );
            }

            return $output;
        }

        /**
         * Generates the HTML for the background image control.
         *
         * @return string
         */
        public function get_background_image_control() {
            $add_image_label = esc_html__( 'Add image', 'js_composer' );

            if ( $this->is_wpb_new() ) {
                $value = sprintf( '<div class="gallery_widget_attached_image_wrapper"></div><button type="button" class="gallery_widget_add_images vc_add-image" use-single="true" data-is-do="true" title="' . $add_image_label . '" aria-label="' . $add_image_label . '"><i class="vc-composer-icon vc-c-image"></i>%s</button>', $add_image_label );
            } else {
                $value = sprintf( '<ul class="vc_image"></ul><a href="#" class="vc_add-image"><i class="vc-composer-icon vc-c-icon-add"></i>%s</a>', $add_image_label );
            }

            return apply_filters( 'vc_css_editor_background_image_control', $value );
        }

        /**
         * Generates the HTML for the background image control.
         *
         * @deprecated since 8.0.0. Use WPBakeryCssEditor::get_background_image_control() instead.
         * @return string
         */
        public function getBackgroundImageControl() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
            return $this->get_background_image_control();
        }

        /**
         * Get border radius options.
         *
         * @since 9.0
         * @return array
         */
        public function get_border_radius_options() {
            $radius_list = apply_filters( 'vc_css_editor_border_radius_options_data', [
                '' => esc_html__( 'None', 'js_composer' ),
                '1px' => '1px',
                '2px' => '2px',
                '3px' => '3px',
                '4px' => '4px',
                '5px' => '5px',
                '10px' => '10px',
                '15px' => '15px',
                '20px' => '20px',
                '25px' => '25px',
                '30px' => '30px',
                '35px' => '35px',
            ] );

            $options = [];
            foreach ( $radius_list as $radius => $title ) {
                $options[] = [
                    'value' => $radius,
                    'label' => $title,
                ];
            }

            return $options;
        }

        /**
         * Get border style options.
         *
         * @since 9.0
         * @return array
         */
        public function get_border_style_options() {
            $styles = apply_filters( 'vc_css_editor_border_style_options_data', [
                esc_html__( 'solid', 'js_composer' ),
                esc_html__( 'dotted', 'js_composer' ),
                esc_html__( 'dashed', 'js_composer' ),
                esc_html__( 'none', 'js_composer' ),
                esc_html__( 'hidden', 'js_composer' ),
                esc_html__( 'double', 'js_composer' ),
                esc_html__( 'groove', 'js_composer' ),
                esc_html__( 'ridge', 'js_composer' ),
                esc_html__( 'inset', 'js_composer' ),
                esc_html__( 'outset', 'js_composer' ),
                esc_html__( 'initial', 'js_composer' ),
                esc_html__( 'inherit', 'js_composer' ),
            ] );

            $options = [];
            foreach ( $styles as $style ) {
                $options[] = [
                    'value' => $style,
                    'label' => ucfirst( $style ),
                ];
            }

            return $options;
        }

        /**
         * Get background style options.
         *
         * @since 9.0
         * @return array
         */
        public function get_background_style_options() {
            $styles = apply_filters( 'vc_css_editor_background_style_options_data', [
                esc_html__( 'Cover', 'js_composer' ) => 'cover',
                esc_html__( 'Contain', 'js_composer' ) => 'contain',
                esc_html__( 'No Repeat', 'js_composer' ) => 'no-repeat',
                esc_html__( 'Repeat', 'js_composer' ) => 'repeat',
            ] );

            $default = $this->setting( 'background_style_default' );

            $options = [];
            foreach ( $styles as $name => $style ) {
                $option = [
                    'value' => $style,
                    'label' => $name,
                ];
                if ( $default && $default === $style ) {
                    $option['selected'] = true;
                }
                $options[] = $option;
            }

            return $options;
        }

        /**
         * Generates the HTML options for the border radius dropdown.
         *
         * @deprecated since 9.0. Use WPBakeryCssEditor::get_border_radius_options() instead.
         * @return string
         */
        public function getBorderRadiusOptions() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
            _deprecated_function(
                __METHOD__,
                '9.0',
                'WPBakeryCssEditor::get_border_radius_options'
            );
            $radiuses = apply_filters( 'vc_css_editor_border_radius_options_data', [
                '' => esc_html__( 'None', 'js_composer' ),
                '1px' => '1px',
                '2px' => '2px',
                '3px' => '3px',
                '4px' => '4px',
                '5px' => '5px',
                '10px' => '10px',
                '15px' => '15px',
                '20px' => '20px',
                '25px' => '25px',
                '30px' => '30px',
                '35px' => '35px',
            ] );

            $output = '';
            foreach ( $radiuses as $radius => $title ) {
                $output .= '<option value="' . $radius . '">' . $title . '</option>';
            }

            return $output;
        }

        /**
         * Generates the HTML options for the border style dropdown.
         *
         * @deprecated since 9.0. Use WPBakeryCssEditor::get_border_style_options() instead.
         * @return string
         */
        public function getBorderStyleOptions() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
            _deprecated_function(
                __METHOD__,
                '9.0',
                'WPBakeryCssEditor::get_border_style_options()'
            );
            $output = '<option value="">' . esc_html__( 'Theme defaults', 'js_composer' ) . '</option>';
            $styles = apply_filters( 'vc_css_editor_border_style_options_data', [
                esc_html__( 'solid', 'js_composer' ),
                esc_html__( 'dotted', 'js_composer' ),
                esc_html__( 'dashed', 'js_composer' ),
                esc_html__( 'none', 'js_composer' ),
                esc_html__( 'hidden', 'js_composer' ),
                esc_html__( 'double', 'js_composer' ),
                esc_html__( 'groove', 'js_composer' ),
                esc_html__( 'ridge', 'js_composer' ),
                esc_html__( 'inset', 'js_composer' ),
                esc_html__( 'outset', 'js_composer' ),
                esc_html__( 'initial', 'js_composer' ),
                esc_html__( 'inherit', 'js_composer' ),
            ] );
            foreach ( $styles as $style ) {
                $output .= '<option value="' . $style . '">' . ucfirst( $style ) . '</option>';
            }

            return $output;
        }

        /**
         * Generates the HTML options for the background style dropdown.
         *
         * @deprecated since 9.0. Use WPBakeryCssEditor::get_background_style_options() instead.
         * @return string
         */
        public function getBackgroundStyleOptions() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
            _deprecated_function(
                __METHOD__,
                '9.0',
                'WPBakeryCssEditor::get_background_style_options()'
            );
            $output = '<option value="">' . esc_html__( 'Theme defaults', 'js_composer' ) . '</option>';
            $styles = apply_filters( 'vc_css_editor_background_style_options_data', [
                esc_html__( 'Cover', 'js_composer' ) => 'cover',
                esc_html__( 'Contain', 'js_composer' ) => 'contain',
                esc_html__( 'No Repeat', 'js_composer' ) => 'no-repeat',
                esc_html__( 'Repeat', 'js_composer' ) => 'repeat',
            ] );
            foreach ( $styles as $name => $style ) {
                $output .= '<option value="' . $style . '">' . $name . '</option>';
            }

            return $output;
        }

        /**
         * Returns a unique ID prefix for this css_editor instance, scoped to its param_name.
         *
         * Falls back to the legacy prefix when param_name is not set, preserving backward compatibility.
         *
         * @return string
         */
        protected function get_id_prefix() {
            $param_name = $this->setting( 'param_name' );
            if ( $param_name ) {
                return 'vc_css-editor-' . sanitize_html_class( $param_name );
            }
            return 'vc_css-editor';
        }

        /**
         * Returns a unique element ID for a named field within this instance.
         *
         * @param string $field Field identifier (e.g. 'border-style', 'background-style').
         * @return string
         */
        public function get_field_id( $field ) {
            return $this->get_id_prefix() . '-' . $field;
        }

        /**
         * Generates the onion layout structure for the CSS editor.
         *
         * Porto wraps WPBakery's onion layout with a responsive tab switcher and adds one
         * extra (input only) onion layout per breakpoint.
         *
         * @return string
         */
        public function onionLayout() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
            if ( ! $this->is_wpb_new() ) {
                return $this->porto_legacy_onion_layout();
            }

            $link_toggle = '<button type="button" class="vc_css-editor-link-toggle" title="' . esc_attr__( 'Link values', 'js_composer' ) . '" aria-label="' . esc_attr__( 'Link values', 'js_composer' ) . '"><i class="vc-composer-icon vc-c-not-linked"></i><i class="vc-composer-icon vc-c-linked"></i></button>';

            $margin_label = $this->layer_label( 'margin' );
            $border_controls = $this->layerControls( 'border', 'width' );
            $padding_controls = $this->layerControls( 'padding' );
            $radius_controls = function_exists( 'wpb_border_radius_controls' ) ? wpb_border_radius_controls() : '';

            $output = sprintf(
                '<div class="vc_layout-onion active" data-width="desktop"><div class="vc_margin">%s%s%s%s<div class="vc_border">%s%s<div class="vc_padding">%s<div class="vc_content"></div></div></div></div></div>',
                $margin_label,
                $this->get_layer_unit_selector( 'margin' ),
                $link_toggle,
                $this->layer_inputs( 'margin' ),
                $border_controls,
                $radius_controls,
                $padding_controls
            );

            $output = apply_filters( 'vc_css_editor_onion_layout', $output );

            foreach ( $this->porto_get_responsive_devices() as $device => $data ) {
                $output .= $this->porto_responsive_layout( $device, $data );
            }

            return $this->porto_device_tabs() . '<div class="porto-layout-tab-content">' . $output . '</div>';
        }

        /**
         * Generates the onion layout structure for WPBakery below 9.0.
         *
         * Same responsive tab switcher as onionLayout(), built on the WPBakery 8.x markup
         * (no unit selectors, no link toggle, no border radius controls - those are 9.0 only)
         * and keeping the `vc_col-xs-7` grid class the old flex row layout relies on.
         *
         * @return string
         * @since 8.0.0
         */
        protected function porto_legacy_onion_layout() {
            $output = sprintf(
                '<div class="vc_layout-onion active" data-width="desktop"><div class="vc_margin">%s<div class="vc_border">%s<div class="vc_padding">%s<div class="vc_content"><i></i></div></div></div></div></div>',
                $this->layerControls( 'margin' ),
                $this->layerControls( 'border', 'width' ),
                $this->layerControls( 'padding' )
            );

            $output = apply_filters( 'vc_css_editor_onion_layout', $output );

            foreach ( $this->porto_get_responsive_devices() as $device => $data ) {
                $output .= $this->porto_responsive_layout( $device, $data );
            }

            return $this->porto_device_tabs() . '<div class="porto-layout-tab-content vc_col-xs-7">' . $output . '</div>';
        }

        /**
         * Generates only the label for a specific layer.
         *
         * @param string $name The layer name (margin, border, padding).
         *
         * @return string
         */
        protected function layer_label( $name ) {
            $label = $this->porto_layer_label_text( $name );
            $for_id = $this->get_id_prefix() . '-' . esc_attr( $name ) . '-top';

            return '<label class="vc_layout-onion-label" for="' . $for_id . '">' . esc_html( $label ) . '</label>';
        }

        /**
         * Generates only the input fields for a specific layer (no label).
         *
         * @param string $name The layer name (margin, border, padding).
         * @param string $prefix Optional prefix for the input names.
         *
         * @return string
         */
        protected function layer_inputs( $name, $prefix = '' ) {
            $output = '';
            foreach ( $this->positions as $pos ) {
                $id_attr = 'top' === $pos ? ' id="' . $this->get_id_prefix() . '-' . esc_attr( $name ) . '-top"' : '';
                $output .= sprintf( '<input type="text"%s name="%s_%s%s" data-name="%s%s-%s" class="vc_%s" placeholder="" data-attribute="%s" value="">', $id_attr, esc_attr( $name ), esc_attr( $pos ), '' !== $prefix ? '_' . esc_attr( $prefix ) : '', esc_attr( $name ), '' !== $prefix ? '-' . esc_attr( $prefix ) : '', esc_attr( $pos ), esc_attr( $pos ), esc_attr( $name ) );
            }

            return apply_filters( 'vc_css_editor_layer_inputs', $output );
        }

        /**
         * Generates the controls for a specific layer (e.g., margin, border).
         *
         * @param string $name The layer name.
         * @param string $prefix Optional prefix for the input names.
         *
         * @return string
         */
        protected function layerControls( $name, $prefix = '' ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
            $label = $this->porto_layer_label_text( $name );
            $for_id = $this->get_id_prefix() . '-' . esc_attr( $name ) . '-top';
            $output = '<label for="' . $for_id . '">' . esc_html( $label ) . '</label>';
            $output .= $this->get_layer_unit_selector( $name );
            foreach ( $this->positions as $pos ) {
                $id_attr = 'top' === $pos ? ' id="' . $for_id . '"' : '';
                $output .= sprintf( '<input type="text"%s name="%s_%s%s" data-name="%s%s-%s" class="vc_%s" placeholder="" data-attribute="%s" value="">', $id_attr, esc_attr( $name ), esc_attr( $pos ), '' !== $prefix ? '_' . esc_attr( $prefix ) : '', esc_attr( $name ), '' !== $prefix ? '-' . esc_attr( $prefix ) : '', esc_attr( $pos ), esc_attr( $pos ), esc_attr( $name ) );
            }

            return apply_filters( 'vc_css_editor_layer_controls', $output );
        }

        /**
         * Generates a unit selector dropdown for a layer.
         *
         * @param string $name The layer name (margin, border, padding).
         *
         * @return string
         * @since 9.0
         */
        protected function get_layer_unit_selector( $name ) {
            // Unit selectors (and their template) were introduced in WPBakery 9.0.
            if ( ! $this->is_wpb_new() ) {
                return '';
            }

            $default_units = 'border' === $name ? [ 'px', 'em', 'rem' ] : [ 'px', '%', 'em', 'rem', 'vw', 'vh' ];
            $units = apply_filters( 'vc_css_editor_units', $default_units, $name );

            return vc_get_template( 'editors/partials/param-unit-selector.tpl.php', [
                'units'           => $units,
                'selected_unit'   => 'px',
                'data_attributes' => [ 'layer' => $name ],
            ] );
        }

        /**
         * Returns the translated label of a layer.
         *
         * @param string $name The layer name (margin, border, padding).
         *
         * @return string
         */
        protected function porto_layer_label_text( $name ) {
            if ( 'margin' === $name ) {
                return esc_html__( 'Margin', 'js_composer' );
            } elseif ( 'padding' === $name ) {
                return esc_html__( 'Padding', 'js_composer' );
            } elseif ( 'border' === $name ) {
                return esc_html__( 'Border and radius', 'js_composer' );
            }

            return $name;
        }

        /**
         * Responsive breakpoints handled by the design options panel.
         *
         * `size` is used to build the media query in the editor scripts
         * (`@media(max-width: {size}px)`). The `porto-xxl` / `porto-xl` placeholders are
         * replaced with the theme container breakpoints when the shortcode css is generated.
         *
         * @return array
         * @since 7.2.0
         */
        protected function porto_get_responsive_devices() {
            $xl_width  = function_exists( 'porto_get_xl_width' ) ? porto_get_xl_width() : 1200;
            $xxl_width = function_exists( 'porto_get_xl_width' ) ? porto_get_xl_width( false ) : 1600;

            return apply_filters(
                'porto_css_editor_responsive_devices',
                [
                    'xxl' => [
                        'size'  => 'porto-xxl',
                        'max'   => $xxl_width - 1,
                        'icon'  => 'vc-c-icon-layout_default',
                        'label' => __( 'Desktop', 'porto-functionality' ),
                        /* translators: %d: breakpoint width in pixels */
                        'title' => sprintf( __( '< %dpx', 'porto-functionality' ), $xxl_width ),
                    ],
                    'xl' => [
                        'size'  => 'porto-xl',
                        'max'   => $xl_width - 1,
                        'icon'  => 'vc-c-icon-layout_landscape-tablets',
                        'label' => __( 'Landscape Tablet', 'porto-functionality' ),
                        /* translators: %d: breakpoint width in pixels */
                        'title' => sprintf( __( '< %dpx', 'porto-functionality' ), $xl_width ),
                    ],
                    'lg' => [
                        'size'  => '991',
                        'max'   => 991,
                        'icon'  => 'vc-c-icon-layout_portrait-tablets',
                        'label' => __( 'Portrait Tablet', 'porto-functionality' ),
                        'title' => __( '< 992px', 'porto-functionality' ),
                    ],
                    'md' => [
                        'size'  => '767',
                        'max'   => 767,
                        'icon'  => 'vc-c-icon-layout_landscape-smartphones',
                        'label' => __( 'Landscape Mobile', 'porto-functionality' ),
                        'title' => __( '< 768px', 'porto-functionality' ),
                    ],
                    'xs' => [
                        'size'  => '575',
                        'max'   => 575,
                        'icon'  => 'vc-c-icon-layout_portrait-smartphones',
                        'label' => __( 'Portrait Mobile', 'porto-functionality' ),
                        'title' => __( '< 576px', 'porto-functionality' ),
                    ],
                ]
            );
        }

        /**
         * Renders the breakpoint switcher shown above the onion layouts.
         *
         * @return string
         * @since 7.2.0
         */
        protected function porto_device_tabs() {
            $output = '<div class="porto-layout-onion-tabs">';
            $output .= sprintf(
                '<span class="active" data-width="desktop" title="%1$s" aria-label="%1$s"><i class="vc-composer-icon vc-c-icon-layout_large-desktop"></i>%2$s</span>',
                esc_attr__( 'Default (Desktop and above)', 'porto-functionality' ),
                esc_html__( 'Default', 'porto-functionality' )
            );

            foreach ( $this->porto_get_responsive_devices() as $device => $data ) {
                $label = $data['label'] . ' (' . $data['title'] . ')';
                $output .= sprintf(
                    '<span data-width="%1$s" title="%2$s" aria-label="%2$s"><i class="vc-composer-icon %3$s"></i>%4$s</span>',
                    esc_attr( $device ),
                    esc_attr( $label ),
                    esc_attr( $data['icon'] ),
                    esc_html( $this->porto_device_tab_text( $data ) )
                );
            }
            $output .= '</div>';

            return $output;
        }

        /**
         * Text of a breakpoint tab, e.g. `<=991px`.
         *
         * Falls back to the device label when a custom device (added through the
         * `porto_css_editor_responsive_devices` filter) has no numeric breakpoint.
         *
         * @param array $data Breakpoint definition, @see porto_get_responsive_devices().
         *
         * @return string
         * @since 8.0.0
         */
        protected function porto_device_tab_text( $data ) {
            $max = 0;
            if ( isset( $data['max'] ) ) {
                $max = (int) $data['max'];
            } elseif ( isset( $data['size'] ) && is_numeric( $data['size'] ) ) {
                $max = (int) $data['size'];
            }

            if ( ! $max ) {
                return isset( $data['label'] ) ? $data['label'] : '';
            }

            /* translators: %d: breakpoint width in pixels */
            return sprintf( __( '<=%dpx', 'porto-functionality' ), $max );
        }

        /**
         * Renders one onion layout for a given breakpoint.
         *
         * Only margin / border width / padding are responsive - colors, background and
         * border radius stay in the shared settings column.
         *
         * @param string $device Breakpoint key (xxl, xl, lg, md, xs).
         * @param array  $data Breakpoint definition, @see porto_get_responsive_devices().
         *
         * @return string
         * @since 7.2.0
         */
        protected function porto_responsive_layout( $device, $data ) {
            return sprintf(
                '<div class="vc_layout-onion" data-width="%s" data-size="%s"><div class="vc_margin">%s<div class="vc_border">%s<div class="vc_padding">%s<div class="vc_content"></div></div></div></div></div>',
                esc_attr( $device ),
                esc_attr( $data['size'] ),
                $this->porto_responsive_layer_controls( 'margin', '', $device ),
                $this->porto_responsive_layer_controls( 'border', 'width', $device ),
                $this->porto_responsive_layer_controls( 'padding', '', $device )
            );
        }

        /**
         * Generates the label and the four inputs of a responsive layer.
         *
         * The inputs are intentionally rendered without `data-name` / `data-attribute` so
         * WPBakery's own css editor view keeps reading the default (desktop) values only.
         * They are collected by name in portoCssEditorResponsive().
         *
         * @param string $name The layer name (margin, border, padding).
         * @param string $prefix Optional prefix for the input names.
         * @param string $device Breakpoint key (xxl, xl, lg, md, xs).
         *
         * @return string
         * @since 7.2.0
         */
        protected function porto_responsive_layer_controls( $name, $prefix, $device ) {
            $param_name = $this->setting( 'param_name' );
            $for_id = $this->get_id_prefix() . '-' . esc_attr( $device ) . '-' . esc_attr( $name ) . '-top';

            $output = '<label for="' . $for_id . '">' . esc_html( $this->porto_layer_label_text( $name ) ) . '</label>';
            foreach ( $this->positions as $pos ) {
                $id_attr = 'top' === $pos ? ' id="' . $for_id . '"' : '';
                $output .= sprintf(
                    '<input type="text"%s name="%s_%s_%s%s_%s" class="vc_%s" placeholder="" value="">',
                    $id_attr,
                    esc_attr( $param_name ),
                    esc_attr( $name ),
                    esc_attr( $pos ),
                    '' !== $prefix ? '_' . esc_attr( $prefix ) : '',
                    esc_attr( $device ),
                    esc_attr( $pos )
                );
            }

            return apply_filters( 'porto_css_editor_responsive_layer_controls', $output, $name, $prefix, $device );
        }
    }
}
