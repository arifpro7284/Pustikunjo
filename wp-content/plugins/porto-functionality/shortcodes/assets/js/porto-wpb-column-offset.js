/**
 * Column offset param - WPBakery 9.0 compatibility.
 *
 * WPBakery 9.0 binds the legacy `width` attribute of a column to the `md` row of the Responsiveness
 * tab, while the rendering side still outputs that width as a `vc_col-sm-*` class. `vc_col-sm-*` is
 * `min-width: 768px` and `vc_col-md-*` is `min-width: 992px`, so saving the popup moves the column
 * to the wrong breakpoint.
 *
 * This replaces `vc.atts.column_offset` with the same behaviour bound to the `sm` row instead.
 * Keep in sync with lib/wpb-controls/column-offset.php, which preselects the same row server side.
 *
 * @since 7.6.0
 */
jQuery( function( $ ) {
	'use strict';

	if ( 'undefined' === typeof window.vc || ! window.vc.atts || ! window.vc.atts.column_offset ) {
		return;
	}
	if ( 'undefined' === typeof window.Backbone ) {
		return;
	}

	// Viewport of the legacy `width` attribute, see Porto_Vc_Column_Offset::WIDTH_SIZE.
	var WIDTH_SIZE = 'sm',
		WIDTH_SELECT = 'select.vc_column_offset_field[data-type="size-' + WIDTH_SIZE + '"]',
		WIDTHS = {
			1: '1/12',
			2: '1/6',
			3: '1/4',
			4: '1/3',
			5: '5/12',
			6: '1/2',
			7: '7/12',
			8: '2/3',
			9: '3/4',
			10: '5/6',
			11: '11/12',
			12: '1/1',
			'1/5': '1/5',
			'2/5': '2/5',
			'3/5': '3/5',
			'4/5': '4/5'
		};

	/**
	 * Turn a `vc_col-sm-*` class into the fraction the `width` attribute stores.
	 *
	 * @param {string} value
	 * @return {string}
	 */
	function toWidth( value ) {
		var matches = value && value.match( /vc_col-sm-(.+)/ );

		return ( matches && matches[1] && WIDTHS[ matches[1] ] ) || '1/1';
	}

	/**
	 * Collects the value of every control of the param, unchanged from WPBakery.
	 */
	var ColumnOffsetView = Backbone.View.extend( {
		render: function() {
			return this;
		},
		save: function() {
			var values = [];

			this.$el.find( '.vc_column_offset_field' ).each( function() {
				var $field = $( this ),
					value = $field.val(),
					type = $field.data( 'type' ),
					size;

				if ( ! type ) {
					return;
				}

				size = type.match( /^size-(xl|lg|md|sm)$/ );

				if ( size ) {
					values.push( '' === value ? 'vc_col-' + size[1] + '-inherit' : value );
				} else if ( $field.is( ':checkbox:checked' ) ) {
					values.push( $field.attr( 'name' ) );
				} else if ( $field.is( 'select' ) && '' !== value ) {
					values.push( value );
				}
			} );

			return values;
		}
	} );

	/**
	 * Keep the `width` param in sync with the width select of the WIDTH_SIZE row.
	 *
	 * @param {jQuery} $wrap
	 */
	function bindWidthParam( $wrap ) {
		var $size = $wrap.find( WIDTH_SELECT ),
			$width;

		if ( ! $size.length ) {
			return;
		}

		$width = $wrap.closest( '.vc_ui-panel-content-container' ).find( 'input.wpb_vc_param_value[name="width"]' );

		if ( ! $width.length ) {
			return;
		}

		$size.on( 'change', function() {
			$width.val( toWidth( $size.val() ) ).trigger( 'change' );
		} );

		$width.val( toWidth( $size.val() ) ).trigger( 'change' );
	}

	window.vc.atts.column_offset = {
		parse: function( param ) {
			var values = this.content().find( 'input.wpb_vc_param_value.' + param.param_name ).data( 'vcColumnOffset' ).save(),
				$size = this.content().find( WIDTH_SELECT ),
				$width = this.content().find( 'input.wpb_vc_param_value[name="width"]' );

			if ( $size.length && $width.length ) {
				$width.val( toWidth( $size.val() ) ).trigger( 'change' );
			}

			return values.join( ' ' );
		},
		init: function( param, $content ) {
			var paramName = param && param.param_name ? param.param_name : 'offset';

			$( '[data-column-offset="true"]', $content ).each( function() {
				var $wrap = $( this );

				$wrap.find( '.wpb_vc_param_value.' + paramName ).data( 'vcColumnOffset', new ColumnOffsetView( { el: $wrap } ).render() );

				bindWidthParam( $wrap );
			} );
		}
	};
} );
