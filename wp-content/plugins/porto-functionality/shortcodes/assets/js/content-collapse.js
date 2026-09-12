( function( theme, $ ) {
	// Content Collapse
	$( document ).on( 'click', '.content-collapse-wrap .btn-read-more-wrap', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '.content-collapse-wrap' );
		if ( $wrap.hasClass( 'opened' ) ) {
			$wrap.removeClass( 'opened' );
		} else {
			$wrap.addClass( 'opened' );
		}
	} );
} ).apply( this, [window.theme, jQuery] );