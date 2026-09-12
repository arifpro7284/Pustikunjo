

// Mouse Hover Split
( function( theme, $ ) {

	theme = theme || {};

	var instanceName = '__mousehoversplit';

	var PluginHoverSplit = function( $el ) {
		return this.initialize( $el );
	}

	PluginHoverSplit.prototype = {
		initialize: function( $el ) {
			if ( $el.data( instanceName ) ) {
				return this;
			}
			this.$el = $el.addClass( 'slide-wrapper' );

			this
				.setData()
				.event();

			return this;
		},

		setData: function() {
			this.$el.data( instanceName, this );
			this.$el.addClass( 'initialized' );
			var $columns = this.$el.find( '>.split-slide' );
			for ( var index = 0; index < $columns.length; index++ ) {
				var column = $columns[index];
				if ( index == 0 ) {
					column.classList.add( 'slide-left' );
					this.left = column;
				} else if ( index == 1 ) {
					column.classList.add( 'slide-right' );
					break;
				}
			}

			return this;
		},

		event: function() {
			// Refresh
			this.refresh();
			this.refreshFunc = this.refresh.bind( this );
			$( window ).on( 'resize', this.refreshFunc );

			//Hover
			this.handleMoveFunc = this.handleMove.bind( this );
			$( document.body ).on( 'mousemove', this.handleMoveFunc );
		},
		handleMove: function( e ) {
			if ( e.clientX < this.$el.offset().left ) {
				this.left.style.width = '0';
			} else {
				this.left.style.width = `calc( ${ ( e.clientX - this.$el.offset().left ) / ( this.$el.innerWidth() ) * 100 }% - 3px ) `;
			}
		},
		refresh: function( e ) {
			if ( e && e.type == 'resize' ) {
				this.$el.css( 'min-height', $( this.left ).height() );
			}
			this.$el.find( '>.split-slide>*' ).css( 'width', this.$el.innerWidth() );
		},
		clearData: function() {
			// Remove class and instance
			this.$el.removeClass( 'slide-wrapper' ).removeData( instanceName ).css( 'min-height', '' );
			this.$el.find( '>*' ).removeClass( 'slide-left slide-right' ).css( 'width', '' );

			// Clear Event
			$( window ).off( 'resize', this.refreshFunc );
			$( document.body ).off( 'mousemove', this.handleMoveFunc );
		}
	}

	// expose to scope
	$.extend( theme, {
		PluginHoverSplit: PluginHoverSplit
	} );

	$.fn.themePluginHoverSplit = function() {
		return this.map( function() {
			var $this = $( this ),
				$splitColumns = $this.find( '>.split-slide' );
			if ( $splitColumns.length >= 2 ) {
				if ( $this.data( instanceName ) ) {
					return $this.data( instanceName );
				} else {
					return new PluginHoverSplit( $this );
				}
			}
		} );
	}
} ).apply( this, [window.theme, jQuery] );

    
jQuery( document ).ready( function( $ ) {
    // Hover Split
	if ( $.fn.themePluginHoverSplit ) {
		$( '.mouse-hover-split' ).each( function() {
			var $this = $( this ),
				// Elmentor
				$splitSlide = $this.find( '>.split-slide' );
			if ( $splitSlide.length >= 2 ) {
				$this.themePluginHoverSplit();
			}
		} );
	}
} );

