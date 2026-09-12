/**
 * Number form field - WPBakery 9.0 compatibility.
 *
 * `vc.formComponents.number.initInput()` clamps the field to its min / max on every change:
 *
 *     let value = Number( input.value );
 *     isNaN( value ) || ( ..., input.value = value );
 *
 * The `isNaN()` guard is meant to leave an empty field alone, but `Number( '' )` is `0`, not `NaN`.
 * So clearing any number based option - font size, line height, the unit selector fields of
 * `font_container`, `range`, `linked_fields` - writes a `0` back into the input, and
 * `vc.formComponents.unitSelector.buildValue()` then stores "0" / "0rem" instead of an empty value.
 *
 * This replaces `initInput()` with the same behaviour, except that an empty field stays empty.
 *
 * @since 7.6.0
 */
jQuery( function( $ ) {
	'use strict';

	if ( 'undefined' === typeof window.vc || ! window.vc.formComponents || ! window.vc.formComponents.number ) {
		return;
	}

	/**
	 * @param {HTMLInputElement} input  The number input.
	 * @param {jQuery}           $up    Step up chevron.
	 * @param {jQuery}           $down  Step down chevron.
	 */
	window.vc.formComponents.number.initInput = function( input, $up, $down ) {
		var min = '' !== input.min ? Number( input.min ) : null,
			max = '' !== input.max ? Number( input.max ) : null,
			delayTimer = null,
			repeatTimer = null;

		/**
		 * Keep the value within min / max, leaving an empty field empty.
		 */
		function clamp() {
			var value;

			if ( '' === String( input.value ).trim() ) {
				return;
			}

			value = Number( input.value );

			if ( isNaN( value ) ) {
				return;
			}
			if ( null !== min && value < min ) {
				value = min;
			}
			if ( null !== max && value > max ) {
				value = max;
			}

			input.value = value;
		}

		function stop() {
			clearTimeout( delayTimer );
			clearInterval( repeatTimer );
			delayTimer = null;
			repeatTimer = null;
		}

		function step( direction ) {
			if ( '' === input.value && null !== min ) {
				input.value = min;
			} else {
				clamp();

				if ( direction > 0 ) {
					input.stepUp();
				} else {
					input.stepDown();
				}
			}

			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			$( input ).trigger( 'input' ).trigger( 'change' );
		}

		function startStepping( direction ) {
			step( direction );

			delayTimer = setTimeout( function() {
				repeatTimer = setInterval( function() {
					step( direction );
				}, 60 );
			}, 350 );
		}

		function bindChevron( $chevron, direction ) {
			if ( ! $chevron || ! $chevron.length ) {
				return;
			}

			$chevron.on( 'mousedown touchstart', function( e ) {
				if ( 'mousedown' === e.type && 0 !== e.button ) {
					return;
				}
				e.preventDefault();
				stop();
				startStepping( direction );
			} );
			$chevron.on( 'mouseup mouseleave touchend touchcancel', stop );
		}

		bindChevron( $up, 1 );
		bindChevron( $down, -1 );

		$( document ).on( 'mouseup touchend touchcancel', stop );
		$( input ).on( 'change', clamp );
	};
} );
