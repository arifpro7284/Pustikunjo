// Visual Composer Image Zoom
( function( theme, $ ) {
	'use strict';

	theme = theme || {};

	var instanceName = '__zoom';

	var VcImageZoom = function( $el, opts ) {
		return this.initialize( $el, opts );
	};

	VcImageZoom.defaults = {

	};

	VcImageZoom.prototype = {
		initialize: function( $el, opts ) {
			if ( $el.data( instanceName ) ) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions( opts )
				.build();

			return this;
		},

		setData: function() {
			this.$el.data( instanceName, this );

			return this;
		},

		setOptions: function( opts ) {
			this.options = $.extend( true, {}, VcImageZoom.defaults, opts, {
				wrapper: this.$el
			} );

			return this;
		},

		build: function() {
			var self = this,
				$el = this.options.container;
			if ( ! $.fn.magnificPopup ) {
				return;
			}
			$el.parent().magnificPopup( $.extend( true, {}, theme.mfpConfig, {
				delegate: ".porto-vc-zoom",
				gallery: {
					enabled: true
				},
				mainClass: 'mfp-with-zoom',
				zoom: {
					enabled: true,
					duration: 300
				},
				type: 'image'
			} ) );

			return this;
		}
	};

	// expose to scope
	$.extend( theme, {
		VcImageZoom: VcImageZoom
	} );

	// jquery plugin
	$.fn.themeVcImageZoom = function( opts ) {
		return this.map( function() {
			var $this = $( this );

			if ( $this.data( instanceName ) ) {
				return $this.data( instanceName );
			} else {
				return new theme.VcImageZoom( $this, opts );
			}

		} );
	}

} ).apply( this, [window.theme, jQuery] );

jQuery( document ).ready( function( $ ) {
    // Visual Composer Image Zoom
	if ( $.fn.themeVcImageZoom ) {

		$( function() {
			var $galleryParent = null;
			$( '.porto-vc-zoom:not(.manual)' ).each( function() {
				var $this = $( this ),
					opts,
					gallery = $this.attr( 'data-gallery' );

				var pluginOptions = $this.data( 'plugin-options' );
				if ( pluginOptions )
					opts = pluginOptions;

				if ( typeof opts == "undefined" ) {
					opts = {};
				}
				opts.container = $this.parent();

				if ( gallery == 'true' ) {
					var container = 'vc_row';

					if ( $this.attr( 'data-container' ) )
						container = $this.attr( 'data-container' );

					var $parent = $( $this.closest( '.' + container ).get( 0 ) );
					if ( $parent.length > 0 && $galleryParent != null && $galleryParent.is( $parent ) ) {
						return;
					} else if ( $parent.length > 0 ) {
						$galleryParent = $parent;
					}
					if ( $galleryParent != null && $galleryParent.length > 0 ) {
						opts.container = $galleryParent;
					}
				}

				$this.themeVcImageZoom( opts );
			} );
		} );
	}
} );