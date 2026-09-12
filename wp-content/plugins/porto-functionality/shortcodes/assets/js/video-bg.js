/* Porto Video Background */
( function( theme, $ ) {
	'use strict';

	theme = theme || {};

	var instanceName = '__videobackground';

	var PluginVideoBackground = function( $el, opts ) {
		return this.initialize( $el, opts );
	};

	PluginVideoBackground.defaults = {
		overlay: true,
		volume: 1,
		playbackRate: 1,
		muted: true,
		loop: true,
		autoplay: true,
		position: '50% 50%',
		posterType: 'detect'
	};

	PluginVideoBackground.prototype = {
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
			this.options = $.extend( true, {}, PluginVideoBackground.defaults, opts, {
				path: this.$el.data( 'video-path' ),
				wrapper: this.$el
			} );

			return this;
		},

		build: function() {

			if ( !$.fn.vide || !this.options.path ) {
				return this;
			}

			if ( this.options.overlay ) {
				this.options.wrapper.prepend(
					$( '<div />' ).addClass( 'video-overlay' )
				);
			}

			this.options.wrapper.vide( this.options.path, this.options );

			return this;
		}
	};

	// expose to scope
	$.extend( theme, {
		PluginVideoBackground: PluginVideoBackground
	} );

	// jquery plugin
	$.fn.themePluginVideoBackground = function( opts ) {
		return this.map( function() {
			var $this = $( this );

			if ( $this.data( instanceName ) ) {
				return $this.data( instanceName );
			} else {
				return new PluginVideoBackground( $this, opts );
			}

		} );
	};

} ).apply( this, [window.theme, jQuery] );


( function( $, theme ) {
	theme = theme || {};
    if ( theme.isReady ) { // Finish init
        if ( $.fn.themePluginVideoBackground ) {
            $( '[data-plugin-video-background]:not(.manual)' ).each( function() {
                var $this = $( this ),
                    opts;
    
                var pluginOptions = theme.getOptions( $this.data( 'plugin-options' ) );
                if ( pluginOptions )
                    opts = pluginOptions;
    
                $this.themePluginVideoBackground( opts );
            } );
        }
    }
    $( document.body ).on( 'porto_init', function( e, $wrap ) {
        // Video Background
        if ( $.fn.themePluginVideoBackground ) {
            $wrap.find( '[data-plugin-video-background]:not(.manual)' ).each( function() {
                var $this = $( this ),
                    opts;

                var pluginOptions = theme.getOptions( $this.data( 'plugin-options' ) );
                if ( pluginOptions )
                    opts = pluginOptions;

                if ( $this.find( '.video-overlay' ).length ) {
					$this.find( '.video-overlay' ).prev().remove();
                    $this.find( '.video-overlay' ).remove();
                    $this.removeData( '__videobackground' );
                }
                $this.themePluginVideoBackground( opts );
            } );
        }
    } );

} )( window.jQuery, window.theme )
