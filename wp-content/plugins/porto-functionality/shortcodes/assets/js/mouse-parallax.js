// Mouse Parallax
( function( theme, $ ) {
	'use strict';

	theme = theme || {};

	var instanceName = '__parallax';

	var Mouseparallax = function( $el, opts ) {
		return this.initialize( $el, opts );
	};

	Mouseparallax.prototype = {
		initialize: function( $el, opts ) {
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
			this.options = $.extend( true, {}, {
				wrapper: this.$el,
				opts: opts
			} );
			return this;
		},

		build: function() {
			if ( !$.fn.parallax ) {
				return this;
			}

			var $el = this.options.wrapper,
				opts = this.options.opts

			$el.parallax( opts );
		}
	};

	//expose to scope
	$.extend( theme, {
		Mouseparallax: Mouseparallax
	} );

	// jquery plugin
	$.fn.themeMouseparallax = function( opts ) {
		var obj = this.map( function() {
			var $this = $( this );

			if ( $this.data( instanceName ) ) {
				return $this.data( instanceName );
			} else {
				return new theme.Mouseparallax( $this, opts );
			}
		} );
		return obj;
	}
} ).apply( this, [window.theme, jQuery] );

jQuery( document ).ready( function( $ ) {
	// Mouse Parallax
	if ( $.fn.themeMouseparallax ) {
		$( function() {
			$( '[data-plugin="mouse-parallax"]' ).each( function() {
				var $this = $( this ),
					opts;
				if ( $this.data( 'parallax' ) ) {
					$this.parallax( 'disable' );
					$this.removeData( 'parallax' );
					$this.removeData( 'options' );
				}
				if ( $this.hasClass( 'elementor-element' ) ) {
					if ( $this.attr( 'data-widget_type' ) && $this.children( '.elementor-widget-container' ).length == 0 ) {
						$this.wrapInner( '<div class="layer"></div>' );
						$this.children( '.layer' ).attr( 'data-depth', $this.attr( 'data-floating-depth' ) );
					} else {
						$this.children( '.elementor-widget-container, .elementor-container, .elementor-widget-wrap, .elementor-column-wrap' ).addClass( 'layer' ).attr( 'data-depth', $this.attr( 'data-floating-depth' ) );
					}
				} else {
					$this.children( '.layer' ).attr( 'data-depth', $this.attr( 'data-floating-depth' ) );
				}

				var pluginOptions = $this.data( 'options' );
				if ( pluginOptions )
					opts = pluginOptions;

				$this.themeMouseparallax( opts );
			} );
		} );
	}
} );