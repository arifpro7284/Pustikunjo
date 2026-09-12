/**
 * Porto AI — Website Title Generator (vanilla jQuery + Magnific Popup).
 *
 * "Generate" opens a popup of questions → titles. "Use" fills the title field;
 * "Save" persists it to blogname via the REST endpoint.
 */
( function ( $ ) {
	'use strict';

	var api = window.PortoAIApi;
	if ( ! api ) {
		return;
	}
	var __ = api.__, cfg = api.cfg;

	function esc( s ) {
		return $( '<div/>' ).text( s == null ? '' : s ).html();
	}

	function openPopup() {
		$.magnificPopup.open( {
			items: { src: '#porto-ai-title-popup', type: 'inline' },
			mainClass: 'porto-ai-mfp porto-ai-dock', // dock bottom-right like the assistant panel
			removalDelay: 300, // let the fade-out animation finish
			showCloseBtn: false
		} );
	}
	function closePopup() {
		$.magnificPopup.close();
	}

	function setMsg( text, ok ) {
		$( '.porto-ai-title-msg' )
			.text( text || '' )
			.toggleClass( 'is-error', ok === false )
			.toggleClass( 'is-ok', ok === true );
	}

	function generate() {
		var body = {
			business_type: $( '#porto-ai-title-type' ).val(),
			keywords: $( '#porto-ai-title-keywords' ).val(),
			count: 10
		};
		var $btn = $( '.porto-ai-title-run' ).prop( 'disabled', true ).addClass( 'is-loading' );
		$( '#porto-ai-title-results' ).html( '<div class="porto-ai-spinner"></div>' );

		api.post( cfg.rest.titles, body ).then( function ( res ) {
			$btn.prop( 'disabled', false ).removeClass( 'is-loading' );
			var p = res.payload || {};
			if ( ! p.ok ) {
				$( '#porto-ai-title-results' ).html( '<p class="porto-ai-error">' + api.errorHtml( p ) + '</p>' );
				return;
			}
			var titles = ( p.data && p.data.titles ) || [];
			if ( ! titles.length ) {
				$( '#porto-ai-title-results' ).html( '<p>' + esc( __( 'No suggestions. Try different inputs.', 'porto' ) ) + '</p>' );
				return;
			}
			var $list = $( '<div class="porto-ai-title-list"></div>' );
			titles.forEach( function ( t ) {
				$( '<div class="porto-ai-title-item"></div>' )
					.append( $( '<span class="porto-ai-title-item__name"></span>' ).text( t ) )
					.append(
						$( '<button type="button" class="btn btn-quaternary btn-sm porto-ai-title-use"></button>' )
							.text( __( 'Use', 'porto' ) )
							.data( 'title', t )
					)
					.appendTo( $list );
			} );
			$( '#porto-ai-title-results' ).empty().append( $list );
		} );
	}

	function useTitle( t ) {
		$( '#porto-ai-site-title' ).val( t );
		closePopup();
		setMsg( __( 'Title applied. Click Save to keep it.', 'porto' ), true );
	}

	function save() {
		var title = ( $( '#porto-ai-site-title' ).val() || '' ).trim();
		if ( ! title ) {
			setMsg( __( 'Enter a title first.', 'porto' ), false );
			return;
		}
		var $btn = $( '.porto-ai-title-save' ).prop( 'disabled', true );
		api.post( cfg.rest.siteTitle, { title: title } ).then( function ( res ) {
			$btn.prop( 'disabled', false );
			var p = res.payload || {};
			if ( ! p.ok ) {
				setMsg( api.error( p ), false );
				return;
			}
			setMsg( __( 'Saved.', 'porto' ), true );
		} );
	}

	$( function () {
		$( document ).on( 'click', '[data-porto-ai-title-open]', function ( e ) { e.preventDefault(); openPopup(); } );
		$( document ).on( 'click', '[data-porto-ai-title-close]', function ( e ) { e.preventDefault(); closePopup(); } );
		$( document ).on( 'submit', '#porto-ai-title-form', function ( e ) { e.preventDefault(); generate(); } );
		$( document ).on( 'click', '.porto-ai-title-use', function () { useTitle( $( this ).data( 'title' ) ); } );
		$( document ).on( 'click', '.porto-ai-title-save', function ( e ) { e.preventDefault(); save(); } );
	} );
}( jQuery ) );