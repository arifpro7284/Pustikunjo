/**
 * Porto AI — Logo Generator (vanilla jQuery + Magnific Popup).
 *
 * "Generate" asks the Worker (OpenAI Images API) for a logo, shows it, and
 * "Use this logo" sideloads it to the Media Library and wires it into the
 * wizard's logo-save flow (#new_logo_id + the .site-logo preview).
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
			items: { src: '#porto-ai-logo-popup', type: 'inline' },
			mainClass: 'porto-ai-mfp porto-ai-dock', // dock bottom-right like the assistant
			removalDelay: 300,
			showCloseBtn: false
		} );
	}
	function closePopup() {
		$.magnificPopup.close();
	}

	function generate() {
		var body = {
			business_name: $( '#porto-ai-logo-name' ).val(),
			keywords: $( '#porto-ai-logo-keywords' ).val(),
			style: $( '#porto-ai-logo-style' ).val()
		};
		var $btn = $( '.porto-ai-logo-run' ).prop( 'disabled', true ).addClass( 'is-loading' );
		$( '#porto-ai-logo-results .porto-ai-logo-error' ).remove();
		var $loading = $(
			'<div class="porto-ai-logo-loading"><div class="porto-ai-spinner"></div>' +
			'<span>' + esc( __( 'Generating your logo… this can take ~15 seconds', 'porto' ) ) + '</span></div>'
		);
		$( '#porto-ai-logo-results' ).prepend( $loading );

		api.post( cfg.rest.logos, body ).then( function ( res ) {
			$btn.prop( 'disabled', false ).removeClass( 'is-loading' );
			$loading.remove();
			var p = res.payload || {};
			if ( ! p.ok ) {
				$( '#porto-ai-logo-results' ).prepend( '<p class="porto-ai-logo-error porto-ai-error">' + api.errorHtml( p ) + '</p>' );
				return;
			}
			var images = ( p.data && p.data.images ) || [];
			if ( ! images.length ) {
				$( '#porto-ai-logo-results' ).prepend( '<p class="porto-ai-logo-error porto-ai-error">' + esc( __( 'No logo was returned. Try again.', 'porto' ) ) + '</p>' );
				return;
			}
			images.forEach( renderLogo );
		} );
	}

	function renderLogo( src ) {
		var $card = $(
			'<div class="porto-ai-logo-card">' +
				'<div class="porto-ai-logo-thumb"><img alt="' + esc( __( 'Generated logo', 'porto' ) ) + '" /></div>' +
				'<button type="button" class="btn btn-primary btn-sm porto-ai-logo-use">' + esc( __( 'Use this logo', 'porto' ) ) + '</button>' +
			'</div>'
		);
		$card.find( 'img' ).attr( 'src', src );
		$card.find( '.porto-ai-logo-use' ).data( 'src', src );
		$( '#porto-ai-logo-results' ).prepend( $card ); // newest first
	}

	function useLogo( $card ) {
		var src = $card.find( '.porto-ai-logo-use' ).data( 'src' );
		var $use = $card.find( '.porto-ai-logo-use' ).prop( 'disabled', true ).text( __( 'Saving…', 'porto' ) );

		api.post( cfg.rest.saveLogo, { image: src } ).then( function ( res ) {
			var p = res.payload || {};
			if ( ! p.ok ) {
				$use.prop( 'disabled', false ).text( __( 'Use this logo', 'porto' ) );
				$card.append( '<p class="porto-ai-error">' + api.errorHtml( p ) + '</p>' );
				return;
			}
			var data = p.data || {};
			// Wire into the wizard's logo-save flow (same as the media uploader).
			$( '#new_logo_id' ).val( data.id );
			if ( data.url ) {
				var $preview = $( '.site-logo' );
				if ( $preview.length ) {
					$preview.attr( 'src', data.url );
				} else {
					$( '#current-logo' ).html( '<img class="site-logo" src="' + esc( data.url ) + '" style="max-width:250px;height:auto" />' );
				}
			}
			$card.addClass( 'is-selected' );
			closePopup();
		} );
	}

	$( function () {
		$( document ).on( 'click', '[data-porto-ai-logo-open]', function ( e ) { e.preventDefault(); openPopup(); } );
		$( document ).on( 'click', '[data-porto-ai-logo-close]', function ( e ) { e.preventDefault(); closePopup(); } );
		$( document ).on( 'submit', '#porto-ai-logo-form', function ( e ) { e.preventDefault(); generate(); } );
		$( document ).on( 'click', '.porto-ai-logo-use', function () { useLogo( $( this ).closest( '.porto-ai-logo-card' ) ); } );
	} );
}( jQuery ) );