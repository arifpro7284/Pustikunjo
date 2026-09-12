/**
 * Paste control for the Porto container elements in the WPBakery backend editor.
 *
 * Container elements never reach the `controls_list` of their shortcode class:
 * `WPBakeryShortCodesContainer::contentAdmin()` renders its controls through `getColumnControls()`,
 * which builds them from `get_column_control_settings()` - add, clone, edit, delete, move - and has
 * no filter. That is why vc_row and vc_section solve it by overriding `getColumnControls()` and
 * hardcoding the copy / paste markup, and why a php filter cannot add the control to a container.
 *
 * Only the markup is missing though. The base backend view `vc.shortcode_view` already binds
 * `click .column_paste, .vc_control-btn-paste` to its `paste` handler, which calls the generic
 * `vc.pasteShortcode( this.model )`. So the button is added to the controls bar of the Porto
 * containers here, matching the markup of editors/partials/backend_single_control.tpl.php.
 *
 * The placement helper of the paste module then needs the containers to be flagged as such, see
 * markContainers() below.
 *
 * The frontend editor needs none of this, there the container elements are rendered from the
 * `vc_controls-template-container` template which already carries the copy and paste dropdown.
 *
 * @since 7.6.0
 */
jQuery( function( $ ) {
	'use strict';

	if ( 'undefined' === typeof window.vc || ! window.vc.shortcode_view || 'undefined' === typeof window._ ) {
		return;
	}

	var settings = window.porto_wpb_container_paste || {},
		prefix = settings.prefix || 'porto_',
		title = settings.title || 'Paste';

	/**
	 * Whether the element takes the paste control.
	 *
	 * Pasting makes the target the parent of the pasted element, so only containers qualify. The
	 * container test is the one WPBakery uses itself. Keep in sync with
	 * porto_wpb_element_has_paste_control() in lib/wpb-functions.php.
	 *
	 * @param {string} tag
	 * @return {boolean}
	 */
	function isPasteTarget( tag ) {
		if ( ! tag || 0 !== tag.indexOf( prefix ) ) {
			return false;
		}

		var map = window.vc.map ? window.vc.map[ tag ] : null;

		if ( ! _.isObject( map ) ) {
			return false;
		}

		return ( _.isBoolean( map.is_container ) && true === map.is_container ) || ! _.isEmpty( map.as_parent );
	}

	/**
	 * Flag the `as_parent` only elements as containers in the editor map.
	 *
	 * WPBakery tests container elements as
	 * `_.isBoolean( map.is_container ) && true === map.is_container || ! _.isEmpty( map.as_parent )`
	 * everywhere - the storage parser, the drop placeholders, the shortcode builder - except in the
	 * placement helper of the paste module, which reads `vc.map[ tag ].is_container` on its own:
	 *
	 *     isModelContainer: vc.map[ e ].is_container
	 *     ...
	 *     } else if ( isModelContainer && ! isModelRow ) {
	 *         p.parent_id = e.get( 'id' );   // paste inside
	 *     }
	 *
	 * An element that declares only `as_parent` - Porto Animation and Porto Ultimate Content Box for
	 * instance - therefore never gets that branch, the pasted element keeps the `parent_id: false`
	 * that `parseContent()` gave it and lands next to the container instead of inside it.
	 *
	 * Setting the flag brings that one check in line with all the others. It changes nothing for the
	 * checks that already accept `as_parent`, they evaluate to true either way.
	 */
	function markContainers() {
		if ( ! window.vc.map ) {
			return;
		}

		_.each( window.vc.map, function( map, tag ) {
			if ( isPasteTarget( tag ) && true !== map.is_container ) {
				map.is_container = true;
			}
		} );
	}

	/**
	 * Add the paste control to the controls bar of a container element.
	 *
	 * @param {Object} view Backbone view of the element.
	 */
	function addPasteControl( view ) {
		if ( ! view || ! view.$el || ! view.$el.length || ! view.model ) {
			return;
		}
		if ( ! isPasteTarget( view.model.get( 'shortcode' ) ) ) {
			return;
		}

		// The bottom bar only holds the append control, and a container renders the controls of its
		// children as well, so only keep the bar that belongs to this view.
		var element = view.$el.get( 0 ),
			$controls = view.$el.find( '.vc_controls.controls_column' ).not( '.bottom-controls' ).filter( function() {
				return $( this ).closest( '[data-model-id]' ).get( 0 ) === element;
			} ).first();

		if ( ! $controls.length || $controls.find( '.column_paste' ).length ) {
			return;
		}

		var $paste = $( '<a class="vc_control column_paste" data-vc-control="paste" role="button" tabindex="0"><i class="vc-composer-icon vc-c-icon-paste"></i></a>' ).attr( 'title', title ),
			$clone = $controls.find( '.column_clone' ).first();

		if ( $clone.length ) {
			$paste.insertAfter( $clone );
		} else {
			$paste.appendTo( $controls );
		}
	}

	/**
	 * Run addPasteControl() after the `ready` of a view.
	 *
	 * @param {Object} prototype Backbone view prototype.
	 */
	function hookReady( prototype ) {
		var original = prototype.ready;

		prototype.ready = function() {
			var result = _.isFunction( original ) ? original.apply( this, arguments ) : this;

			addPasteControl( this );

			return result;
		};
	}

	markContainers();

	hookReady( window.vc.shortcode_view.prototype );

	// Porto containers use VcColumnView. Hook it as well in case it does not call its parent `ready`.
	if ( window.VcColumnView && window.VcColumnView.prototype.hasOwnProperty( 'ready' ) ) {
		hookReady( window.VcColumnView.prototype );
	}
} );
