// Read More
( function( theme, $ ) {

	theme = theme || {};

	var instanceName = '__readmore';

	var PluginReadMore = function( $el, opts ) {
		return this.initialize( $el, opts );
	};

	PluginReadMore.defaults = {
		buttonOpenLabel: 'Read More <i class="fas fa-chevron-down text-2 ms-1"></i>',
		buttonCloseLabel: 'Read Less <i class="fas fa-chevron-up text-2 ms-1"></i>',
		enableToggle: true,
		maxHeight: 300,
		overlayColor: '#43a6a3',
		overlayHeight: 100,
		startOpened: false,
		align: 'left'
	};

	PluginReadMore.prototype = {
		initialize: function( $el, opts ) {
			var self = this;

			this.$el = $el;

			this
				.setData()
				.setOptions( opts )
				.build()
				.events()
				.resize();

			if ( self.options.startOpened ) {
				self.options.wrapper.find( '.readmore-button-wrapper > button' ).trigger( 'click' );
			}

			return this;
		},

		setData: function() {
			this.$el.data( instanceName, this );

			return this;
		},

		setOptions: function( opts ) {
			this.options = $.extend( true, {}, PluginReadMore.defaults, opts, {
				wrapper: this.$el
			} );

			return this;
		},

		build: function() {
			var self = this;

			self.options.wrapper.addClass( 'position-relative' );

			// Overlay
			self.options.wrapper.append( '<div class="readmore-overlay"></div>' );

			// Check if is Safari
			var backgroundCssValue = 'linear-gradient(180deg, rgba(2, 0, 36, 0) 0%, ' + self.options.overlayColor + ' 100%)';
			if ( $( 'html' ).hasClass( 'safari' ) ) {
				backgroundCssValue = '-webkit-linear-gradient(top, rgba(2, 0, 36, 0) 0%, ' + self.options.overlayColor + ' 100%)'
			}

			self.options.wrapper.find( '.readmore-overlay' ).css( {
				background: backgroundCssValue,
				position: 'absolute',
				bottom: 0,
				left: 0,
				width: '100%',
				height: self.options.overlayHeight,
				'z-index': 1
			} );

			// Read More Button
			self.options.wrapper.find( '.readmore-button-wrapper' ).removeClass( 'd-none' ).css( {
				position: 'absolute',
				bottom: 0,
				left: 0,
				width: '100%',
				'z-index': 2
			} );

			// Button Label
			self.options.wrapper.find( '.readmore-button-wrapper > button' ).html( self.options.buttonOpenLabel );

			self.options.wrapper.css( {
				'height': self.options.maxHeight,
				'overflow-y': 'hidden'
			} );

			// Alignment
			switch ( self.options.align ) {
				case 'center':
					self.options.wrapper.find( '.readmore-button-wrapper' ).addClass( 'text-center' );
					break;

				case 'right':
					self.options.wrapper.find( '.readmore-button-wrapper' ).addClass( 'text-end' );
					break;

				case 'left':
				default:
					self.options.wrapper.find( '.readmore-button-wrapper' ).addClass( 'text-start' );
					break;
			}

			return this;

		},

		events: function() {
			var self = this;

			// Read More
			self.readMore = function() {
				self.options.wrapper.find( '.readmore-button-wrapper > button:not(.readless)' ).on( 'click', function( e ) {
					e.preventDefault();
					self.options.wrapper.addClass( 'opened' );

					var $this = $( this );

					setTimeout( function() {
						self.options.wrapper.animate( {
							'height': self.options.wrapper[0].scrollHeight
						}, function() {
							if ( !self.options.enableToggle ) {
								$this.fadeOut();
							}

							$this.html( self.options.buttonCloseLabel ).addClass( 'readless' ).off( 'click' );

							self.readLess();

							self.options.wrapper.find( '.readmore-overlay' ).fadeOut();
							self.options.wrapper.css( {
								'max-height': 'none',
								'overflow': 'visible'
							} );

							self.options.wrapper.find( '.readmore-button-wrapper' ).animate( {
								bottom: -20
							} );
						} );
					}, 200 );
				} );
			}

			// Read Less
			self.readLess = function() {
				self.options.wrapper.find( '.readmore-button-wrapper > button.readless' ).on( 'click', function( e ) {
					e.preventDefault();
					self.options.wrapper.removeClass( 'opened' );

					var $this = $( this );

					// Button
					self.options.wrapper.find( '.readmore-button-wrapper' ).animate( {
						bottom: 0
					} );

					// Overlay
					self.options.wrapper.find( '.readmore-overlay' ).fadeIn();

					setTimeout( function() {
						self.options.wrapper.height( self.options.wrapper[0].scrollHeight ).animate( {
							'height': self.options.maxHeight
						}, function() {
							$this.html( self.options.buttonOpenLabel ).removeClass( 'readless' ).off( 'click' );

							self.readMore();

							self.options.wrapper.css( {
								'overflow': 'hidden'
							} );
						} );
					}, 200 );
				} );
			}

			// First Load
			self.readMore();

			return this;
		},

		resize: function() {
			var self = this;
			window.addEventListener( 'resize', function() {
				self.options.wrapper.hasClass( 'opened' ) ? self.options.wrapper.css( { 'height': 'auto' } ) : self.options.wrapper.css( { 'height': self.options.maxHeight } );
			} )
		}
	};

	// expose to scope
	$.extend( theme, {
		PluginReadMore: PluginReadMore
	} );

	// jquery plugin
	$.fn.themePluginReadMore = function( opts ) {
		return this.map( function() {
			var $this = $( this );

			if ( $this.data( instanceName ) ) {
				return $this.data( instanceName );
			} else {
				return new PluginReadMore( $this, $this.data( 'plugin-options' ) );
			}

		} );
	}

} ).apply( this, [window.theme, jQuery] );

jQuery( document ).ready( function( $ ) {
    if ( $.fn['themePluginReadMore'] && $( '[data-plugin-readmore]' ).length ) {
		$( '[data-plugin-readmore]:not(.manual)' ).themePluginReadMore();
	}
} );