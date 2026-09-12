
// In Viewport Style
!function( o, e ) { "object" == typeof exports && "undefined" != typeof module ? e( exports ) : "function" == typeof define && define.amd ? define( ["exports"], e ) : e( o.observeElementInViewport = {} ) }( this, function( o ) { function e( o, e, t, r ) { if ( void 0 === t && ( t = function() { } ), void 0 === r && ( r = {} ), !o ) throw new Error( "Target element to observe should be a valid DOM Node" ); var n = Object.assign( {}, { viewport: null, modTop: "0px", modRight: "0px", modBottom: "0px", modLeft: "0px", threshold: [0] }, r ), i = n.viewport, f = n.modTop, s = n.modLeft, u = n.modBottom, a = n.modRight, d = n.threshold; if ( !Array.isArray( d ) && "number" != typeof d ) throw new Error( "threshold should be a number or an array of numbers" ); var p = Array.isArray( d ) ? d.map( function( o ) { return Math.floor( o % 101 ) / 100 } ) : [Math.floor( d ? d % 101 : 0 ) / 100], c = Math.min.apply( Math, p ), m = { root: i instanceof Node ? i : null, rootMargin: f + " " + a + " " + u + " " + s, threshold: p }, h = new IntersectionObserver( function( r, n ) { var i = r.filter( function( e ) { return e.target === o } )[0], f = function() { return n.unobserve( o ) }; i && ( i.isInViewport = i.isIntersecting && i.intersectionRatio >= c, i.isInViewport ? e( i, f, o ) : t( i, f, o ) ) }, m ); return h.observe( o ), function() { return h.unobserve( o ) } } o.observeElementInViewport = e, o.isInViewport = function( o, t ) { return void 0 === t && ( t = {} ), new Promise( function( r, n ) { try { e( o, function( o, e ) { e(), r( !0 ) }, function( o, e ) { e(), r( !1 ) }, t ) } catch ( o ) { n( o ) } } ) } } );
//# sourceMappingURL=index.umd.js.map
( function( theme, $ ) {

	theme = theme || {};

	var instanceName = '__inviewportstyle';

	var PluginInViewportStyle = function( $el, opts ) {
		return this.initialize( $el, opts );
	};

	PluginInViewportStyle.defaults = {
		viewport: window,
		scroll_bg_scale: false,
		scale_extra_class: '',
		set_round: '',
		scale_bg: '#08c',
		threshold: [0],
		modTop: '-200px',
		modBottom: '-200px',
		style: { 'transition': 'all 1s ease-in-out' },
		styleIn: { 'background-color': '#08c' },
		styleOut: { 'background-color': '#fff' },
	};

	PluginInViewportStyle.prototype = {
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
			this.options = $.extend( true, {}, PluginInViewportStyle.defaults, opts, {} );

			return this;
		},

		build: function() {
			var self = this,
				el = self.$el.get( 0 );

			if ( self.options.scroll_bg_scale && 'undefined' != typeof gsap ) {
				self.$scaleObject = $( '<div class="scale-expand position-absolute"></div>' ).addClass( self.options.scale_extra_class );
				self.$el.addClass( 'view-scale-wrapper' ).append( self.$scaleObject );
				self.$scaleObject.css( 'background-color', self.options.scale_bg );
				self.scale = true;
				self.scaleEventFunc = self.scaleEvent.bind( this );
				self.scaleEventFunc();
				$( window ).on( 'scroll', self.scaleEventFunc );
			} else {
				self.$el.css( self.options.style );
				if ( typeof window.IntersectionObserver === 'function' ) {
					self.viewPort = observeElementInViewport.observeElementInViewport(
						el, function() {
							self.$el.css( self.options.styleIn );
						}, function() {
							self.$el.css( self.options.styleOut );
						}, {
						viewport: self.options.viewport,
						threshold: self.options.threshold,
						modTop: self.options.modTop,
						modBottom: self.options.modBottom
					}
					)
				};
			}

			return this;
		},
		scaleEvent: function() {
			var self = this,
				position = self.$el[0].getBoundingClientRect();

			if ( self.scale && position.top < window.innerHeight && position.bottom >= 0 ) {
				gsap.set( self.$scaleObject[0], {
					width: "150vmax",
					height: "150vmax",
					xPercent: -50,
					yPercent: -50,
					top: "50%",
					left: "50%"
				} );
				let _start = "-50%";
				if ( self.$el.height() < 600 ) {
					_start = "-600px";
				}
				var scaleGsap = gsap.timeline( {
					scrollTrigger: {
						trigger: self.$el[0],
						start: _start,
						end: "0%",
						scrub: 2,
						invalidateOnRefresh: true,
					},
					defaults: {
						ease: "none"
					}
				} );

				scaleGsap.fromTo( self.$scaleObject[0], {
					scale: 0
				}, {
					x: 0,
					y: 0,
					ease: "power3.in",
					scale: 1
				} );
				self.scale = false;
			}
		},
		disable: function() {
			var self = this;
			if ( self.options.scroll_bg_scale ) {
				self.$el.find( '.scale-expand' ).remove();
				self.$el.removeClass( 'view-scale-wrapper' )
			} else {
				self.$el.css( { 'background-color': '', 'transition': '' } );
				self.viewPort();
			}
		}
	};

	// expose to scope
	$.extend( theme, {
		PluginInViewportStyle: PluginInViewportStyle
	} );

	// jquery plugin
	$.fn.themePluginInViewportStyle = function( opts ) {
		return this.map( function() {
			var $this = $( this );

			if ( $this.data( instanceName ) ) {
				return $this.data( instanceName );
			} else {
				return new PluginInViewportStyle( $this, opts );
			}

		} );
	}
} ).apply( this, [window.theme, jQuery] );



( function( $, theme ) {
    if ( theme.isReady ) { // Finish init
        // Scroll InViewPort
        if ( $.fn.themePluginInViewportStyle ) {
            $( function() {
                $( '[data-inviewport-style]:not(.manual)' ).each( function() {
                    var $this = $( this ),
                        opts = $this.data( 'plugin-options' );

                    $this.themePluginInViewportStyle( opts );
                } );
            } );
        }
    }
    $( document.body ).on( 'porto_init', function( e, $wrap ) {
        // Scroll InViewPort
        if ( $.fn.themePluginInViewportStyle ) {
            $( function() {
                $wrap.find( '[data-inviewport-style]:not(.manual)' ).each( function() {
                    var $this = $( this ),
                        opts = $this.data( 'plugin-options' );

                    $this.themePluginInViewportStyle( opts );
                } );
            } );
        }
    } );

} )( window.jQuery, window.theme )