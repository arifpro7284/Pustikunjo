/**
 * Reposition Themeisle SDK notice into #tsdk_banner inside .wbcr-factory-content.
 * The SDK may render the notice elsewhere; this moves it into our banner container.
 */
if ( typeof window.tsdk_reposition_notice === 'function' ) {
	document.addEventListener( 'DOMContentLoaded', function() {
		setTimeout( function() {
			window.tsdk_reposition_notice();
			var themeisleSale = document.getElementById( 'tsdk_banner' ) && document.getElementById( 'tsdk_banner' ).querySelector( '.themeisle-sale' );
			if ( themeisleSale ) {
				themeisleSale.style.setProperty( 'display', 'block', 'important' );
				themeisleSale.style.setProperty( 'margin-left', '15px' );
				themeisleSale.style.setProperty( 'margin-right', '15px' );
			}
		}, 0 );
	} );
}
