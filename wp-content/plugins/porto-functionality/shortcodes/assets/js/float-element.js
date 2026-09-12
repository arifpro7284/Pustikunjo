// Float Element
( function( theme, $ ) {

	'use strict';

	theme = theme || {};

	var instanceName = '__floatElement';

	var PluginFloatElement = function( $el, opts ) {
		return this.initialize( $el, opts );
	};

	PluginFloatElement.defaults = {
		startPos: 'top',
		speed: 3,
		horizontal: false,
		circle: false,
		transition: false,
		transitionDelay: 0,
		transitionDuration: 500
	};

	PluginFloatElement.prototype = {
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
			this.options = $.extend( true, {}, PluginFloatElement.defaults, opts, {
				wrapper: this.$el
			} );

			return this;
		},

		build: function() {
			var self = this,
				$el = this.options.wrapper,
				$window = $( window ),
				minus;

			if ( self.options.style ) {
				$el.attr( 'style', self.options.style );
			}

			if ( self.options.circle ) {
				// Set Transition
				if ( self.options.transition ) {
					$el.css( {
						transition: 'ease-out transform ' + self.options.transitionDuration + 'ms ' + self.options.transitionDelay + 'ms'
					} );
				}
				// Scroll
				var _scroll = function() {
					self.movement( minus );
				}
				window.addEventListener( 'scroll', _scroll, { passive: true } );
				self.$el.on( 'float_destroy', function() {
					window.removeEventListener( 'scroll', _scroll, { passive: true } );
					self.$el.css( 'transform', '' );
					self.$el.css( 'transition', '' );
					self.$el.removeData( instanceName );
				});
			} else if ( $window.width() > 767 ) {
				// Set Start Position
				if ( self.options.startPos == 'none' ) {
					minus = '';
				} else if ( self.options.startPos == 'top' ) {
					$el.css( {
						top: 0
					} );
					minus = '';
				} else {
					$el.css( {
						bottom: 0
					} );
					minus = '-';
				}

				// Set Transition
				if ( self.options.transition ) {
					$el.css( {
						transition: 'ease-out transform ' + self.options.transitionDuration + 'ms ' + self.options.transitionDelay + 'ms'
					} );
				}

				// First Load
				if ( ! self.$el.hasClass( 'marquee' ) ) {
					self.movement( minus );
				}
				// Scroll
				var _scroll = function() {
					self.movement( minus );
				}
				var _locomotive_scroll = function( instance ) {
					self.movement( minus, instance.scroll.y );
				}
				window.addEventListener( 'scroll', _scroll, { passive: true } );
				if ( theme.locomotiveScroll ) {
					theme.locomotiveScroll.on( 'scroll', _locomotive_scroll );
				}
				self.$el.on( 'float_destroy', function() {
					window.removeEventListener( 'scroll', _scroll, { passive: true } );
					self.$el.css( 'transform', '' );
					self.$el.css( 'transition', '' );
					self.$el.css( 'top', '' );
					self.$el.css( 'bottom', '' );
					self.$el.removeData( instanceName );
					if ( theme.locomotiveScroll ) {
						theme.locomotiveScroll.off( 'scroll', _locomotive_scroll );
					}
				});
			}

			return this;
		},

		movement: function( minus, isLocomotive = false ) {
			var self = this,
				$el = this.options.wrapper,
				$window = $( window ),
				scrollTop = isLocomotive === false ? $window.scrollTop() : isLocomotive,
				elementOffset = $el.offset().top,
				currentElementOffset = ( elementOffset - scrollTop );
			if ( isLocomotive !== false ) {
				currentElementOffset = $el.offset().top;
				elementOffset = currentElementOffset + scrollTop;
			}
			if ( self.options.circle ) {
				$el.css( {
					transform: 'rotate(' + ( scrollTop * 0.25 ) + 'deg)'
				} );
			} else {
				var scrollPercent = 100 * currentElementOffset / ( $window.height() );

				if ( elementOffset + $el.height() >= scrollTop && elementOffset <= scrollTop + window.innerHeight ) {

					if ( !self.options.horizontal ) {

						$el.css( {
							transform: 'translate3d(0, ' + minus + scrollPercent / self.options.speed + '%, 0)'
						} );

					} else {

						$el.css( {
							transform: 'translate3d(' + minus + scrollPercent / self.options.speed + '%, 0, 0)'
						} );

					}
				}
			}
		}
	};

	// expose to scope
	$.extend( theme, {
		PluginFloatElement: PluginFloatElement
	} );

	// jquery plugin
	$.fn.themePluginFloatElement = function( opts ) {
		return this.map( function() {
			var $this = $( this );

			if ( $this.data( instanceName ) ) {
				return $this.data( instanceName );
			} else {
				return new PluginFloatElement( $this, opts );
			}

		} );
	}

} ).apply( this, [window.theme, jQuery] );


( function( $, theme ) {
    if ( theme.isReady ) { // Finish init
        // Float Element
        if ( $.fn['themePluginFloatElement'] ) {

            $( function() {
                $( '[data-plugin-float-element]:not(.manual)' ).each( function() {
                    var $this = $( this ),
                        opts;

                    var pluginOptions = theme.getOptions( $this.data( 'plugin-options' ) );
                    if ( pluginOptions )
                        opts = pluginOptions;

                    $this.themePluginFloatElement( opts );
                } );
            } );
        }
    }
    $( document.body ).on( 'porto_init', function( e, $wrap ) {
        // Float Element
        if ( $.fn['themePluginFloatElement'] ) {

            $( function() {
                $wrap.find( '[data-plugin-float-element]:not(.manual)' ).each( function() {
                    var $this = $( this ),
                        opts;

                    var pluginOptions = theme.getOptions( $this.data( 'plugin-options' ) );
                    if ( pluginOptions )
                        opts = pluginOptions;

                    $this.themePluginFloatElement( opts );
                } );
            } );
        }
    } );
} )( window.jQuery, window.theme )
