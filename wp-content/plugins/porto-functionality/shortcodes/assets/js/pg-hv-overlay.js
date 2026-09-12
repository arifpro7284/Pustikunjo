jQuery( document ).ready( function( $ ) {
    // Image Hover Overlay on Posts Grid widget
	function portoSetHoverImage( $this, enter = true ) {
		var $item = $this.find( '[data-hoverlay-image]' ),
			$postsGrid = $this.closest( '.porto-posts-grid' );
		if ( $item.length ) {
			var $target = $postsGrid.find( '#himg-' + $item.data( 'hoverlay-id' ) );
			if ( enter ) {
				$target.addClass( 'active' );
				$postsGrid.addClass( 'active' );
			} else {
				$target.removeClass( 'active' );
				$postsGrid.removeClass( 'active' );
			}
		}
	}

	function InsertHoverImage( $this ) {
		var $option = $this.data( 'hoverlay-image' ),
			$postsGrid = $this.closest( '.porto-posts-grid' ),
			$postsWrap = $this.closest( '.posts-wrap' );

		// Overlay Image
		$postsGrid.append( '<div class="thumb-info-full" style="background-image: url(' + $option.src + '); --porto-himg-height:' + $postsWrap.innerHeight() + 'px;" id="himg-' + $option.id + '"></div>' );
		$postsGrid.addClass( 'image-hover-overlay' );


		// Resize
		if ( $postsWrap.hasClass( 'owl-carousel' ) ) {
			$postsWrap.on( 'refreshed.owl.carousel resized.owl.carousel', function() {
				$postsGrid.find( '.thumb-info-full' ).css( '--porto-himg-height', ( $postsWrap.innerHeight() + 'px' ) );
			} )
		} else {
			$( window ).on( 'resize', function() {
				$postsGrid.find( '.thumb-info-full' ).css( '--porto-himg-height', ( $postsWrap.innerHeight() + 'px' ) );
			} );
		}

		// Hover
		$( '.image-hover-overlay' ).on( 'mouseenter touchstart', '.porto-tb-item', function( e ) {
			portoSetHoverImage( $( this ) );
		} );
		$( '.image-hover-overlay' ).on( 'mouseleave touchend', '.porto-tb-item', function( e ) {
			portoSetHoverImage( $( this ), false );
		} );
	}

	// expose to scope
	$.extend( theme, {
		InsertHoverImage: InsertHoverImage
	} );

	$( '.porto-posts-grid [data-hoverlay-image]' ).each( function() {
		theme.InsertHoverImage( $( this ) );
	} );
} );
