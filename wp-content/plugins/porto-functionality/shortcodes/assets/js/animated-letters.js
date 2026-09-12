// Animated Letters
( function( theme, $ ) {

	theme = theme || {};

	var instanceName = '__animatedLetters';

	var PluginAnimatedLetters = function( $el, opts ) {
		return this.initialize( $el, opts );
	};

	PluginAnimatedLetters.defaults = {
		contentType: 'letter',
		animationName: 'typeWriter',
		animationSpeed: 50,
		startDelay: 500,
		minWindowWidth: 768,
		letterClass: '',
		wordClass: '',
		loop: false
	};

	PluginAnimatedLetters.prototype = {
		initialize: function( $el, opts ) {
			if ( $el.data( instanceName ) ) {
				return this;
			}

			var self = this;

			this.$el = $el;
			this.initialText = $el.text();
			this.timeoutId = null;
			this.count = 0;
			this
				.setData()
				.setOptions( opts )
				.build()
				.events();

			if ( this.options.loop ) {
				setInterval(() => {
					this.$el.trigger( 'animated.letters.destroy' );
					this.$el.trigger( 'animated.letters.initialize' );
				}, this.options.startDelay + this.options.animationSpeed * this.count + 2500 );
			}
			return this;
		},

		setData: function() {
			this.$el.data( instanceName, this );

			return this;
		},

		setOptions: function( opts ) {
			this.options = $.extend( true, {}, PluginAnimatedLetters.defaults, opts, {
				wrapper: this.$el
			} );

			return this;
		},

		build: function() {
			var self = this,
				letters = self.$el.text().split( '' );
			self.count = 0;

			if ( $( window ).width() < self.options.minWindowWidth ) {
				self.$el.addClass( 'initialized' );
				return this;
			}

			if ( self.options.firstLoadNoAnim ) {
				self.$el.css( {
					visibility: 'visible'
				} );

				// Inside Carousel
				if ( self.$el.closest( '.owl-carousel' ).get( 0 ) ) {
					setTimeout( function() {
						self.$el.closest( '.owl-carousel' ).on( 'change.owl.carousel', function() {
							self.options.firstLoadNoAnim = false;
							self.build();
						} );
					}, 500 );
				}

				return this;
			}

			// Add class to show
			self.$el.addClass( 'initialized' );

			// Set Min Height to avoid flicking issues
			self.setMinHeight();
			if ( self.options.contentType == 'letter' ) {
				self.count = letters.length;
				self.$el.text( '' );
				if ( self.options.animationName == 'typeWriter' ) {
					self.$el.append( '<span class="letters-wrapper"></span><span class="typeWriter"></pre>' );

					var index = 0;
					var timeout = function() {
						var st = setTimeout( function() {
							var letter = letters[index];

							self.$el.find( '.letters-wrapper' ).append( '<span class="letter ' + ( self.options.letterClass ? self.options.letterClass + ' ' : '' ) + '">' + letter + '</span>' );

							index++;
							timeout();
						}, self.options.animationSpeed );

						if ( index >= letters.length ) {
							clearTimeout( st );
						}
					};
					timeout();
				} else {
					this.timeoutId = setTimeout( function() {
						for ( var i = 0; i < letters.length; i++ ) {
							var letter = letters[i];

							self.$el.append( '<span class="letter ' + ( self.options.letterClass ? self.options.letterClass + ' ' : '' ) + self.options.animationName + ' animated" style="animation-delay: ' + ( i * self.options.animationSpeed ) + 'ms;">' + ( letter
								== ' ' ? '&nbsp;' : letter ) + '</span>' );

						}
					}, self.options.startDelay );
				}
			} else if ( self.options.contentType == 'word' ) {
				var words = self.$el.text().split( " " ),
					delay = self.options.startDelay;
				self.count = words.length;
				self.$el.empty();

				$.each( words, function( i, v ) {
					self.$el.append( $( '<span class="animated-words-wrapper">' ).html( '<span class="animated-words-item ' + self.options.wordClass + ' appear-animation" data-appear-animation="' + self.options.animationName + '" data-appear-animation-delay="' + delay + '">' + v + '&nbsp;</span>' ) );
					delay = delay + self.options.animationSpeed;
				} );

				if ( $.isFunction( $.fn['themeAnimate'] ) && self.$el.find( '.animated-words-item[data-appear-animation]' ).length ) {

					self.$el.find( '[data-appear-animation]' ).each( function() {
						var $this = $( this ),
							opts;

						var pluginOptions = theme.getOptions( $this.data( 'plugin-options' ) );
						if ( pluginOptions )
							opts = pluginOptions;

						$this.themeAnimate( opts );
					} );
				}

				self.$el.addClass( 'initialized' );
			}
			return this;
		},

		setMinHeight: function() {
			var self = this;

			// if it's inside carousel
			if ( self.$el.closest( '.owl-carousel' ).get( 0 ) ) {
				self.$el.closest( '.owl-carousel' ).addClass( 'd-block' );
				self.$el.css( 'min-height', self.$el.height() );
				self.$el.closest( '.owl-carousel' ).removeClass( 'd-block' );
			} else {
				self.$el.css( 'min-height', self.$el.height() );
			}

			return this;
		},

		destroy: function() {
			var self = this;

			self.$el
				.html( self.initialText )
				.css( 'min-height', '' );
			if ( this.timeoutId ) {
				clearTimeout( this.timeoutId );
				this.timeoutId = null;
			}
			return this;
		},

		events: function() {
			var self = this;

			// Destroy
			self.$el.on( 'animated.letters.destroy', function() {
				self.destroy();
			} );

			// Initialize
			self.$el.on( 'animated.letters.initialize', function() {
				self.build();
			} );

			return this;
		}
	};

	// expose to scope
	$.extend( theme, {
		PluginAnimatedLetters: PluginAnimatedLetters
	} );

	// jquery plugin
	$.fn.themePluginAnimatedLetters = function( opts ) {
		return this.map( function() {
			var $this = $( this );

			if ( $this.data( instanceName ) ) {
				return $this.data( instanceName );
			} else {
				return new PluginAnimatedLetters( $this, opts );
			}

		} );
	}

} ).apply( this, [window.theme, jQuery] );

( function( theme, $ ) {
    theme = theme || {};
    $( document.body ).on( 'porto_after_async_init', function() {
        // Animated Letters
        if ( $.fn.themePluginAnimatedLetters ) {
            if ( $( '[data-plugin-animated-letters]' ).length || $( '.animated-letters' ).length ) {
                theme.intObs( '[data-plugin-animated-letters]:not(.manual), .animated-letters', 'themePluginAnimatedLetters' );
            }
            if ( $( '[data-plugin-animated-words]' ).length || $( '.animated-words' ).length ) {
                theme.intObs( '[data-plugin-animated-words]:not(.manual), .animated-words', 'themePluginAnimatedLetters' );
            }
        }
    } );
} ).apply( this, [window.theme, jQuery] );