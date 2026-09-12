/**
 * Porto AI — Demo Import Assistant (vanilla jQuery + Magnific Popup).
 *
 * Reuses the wizard's existing demo cards: "Use this demo" resets the grid
 * filter, scrolls to the matching card, and clicks it — triggering the native
 * install popup. No new import path.
 */
( function ( $ ) {
	'use strict';

	var api = window.PortoAIApi;
	if ( ! api ) {
		return;
	}
	var __ = api.__, cfg = api.cfg;
	var conversation = [];
	var pendingDemo = null; // demo id queued by "Use this demo", run after the panel closes

	function esc( s ) {
		return $( '<div/>' ).text( s == null ? '' : s ).html();
	}

	function openPanel() {
		$.magnificPopup.open( {
			items: { src: '#porto-ai-panel', type: 'inline' },
			mainClass: 'porto-ai-mfp porto-ai-dock',
			removalDelay: 300, // let the fade/slide-out animation finish
			showCloseBtn: false,
			callbacks: {
				// Run a queued "Use this demo" only AFTER the panel is fully gone —
				// Magnific is a singleton, so opening the install popup while this
				// one is still closing corrupts it (null inline error).
				afterClose: function () {
					if ( pendingDemo ) {
						var id = pendingDemo;
						pendingDemo = null;
						gotoDemo( id );
					}
				}
			}
		} );
	}

	function closePanel() {
		$.magnificPopup.close();
	}

	function scrollBottom() {
		var m = document.getElementById( 'porto-ai-messages' );
		if ( m ) {
			m.scrollTop = m.scrollHeight;
		}
	}

	function addMessage( who, html ) {
		$( '#porto-ai-messages' ).append( '<div class="porto-ai-msg porto-ai-msg--' + who + '">' + html + '</div>' );
		scrollBottom();
	}

	function renderChips( items, onPick ) {
		var $c = $( '#porto-ai-chips' ).empty();
		( items || [] ).forEach( function ( label ) {
			$( '<button type="button" class="porto-ai-chip"></button>' )
				.text( label )
				.appendTo( $c )
				.on( 'click', function () { onPick( label ); } );
		} );
	}

	function showTyping() {
		addMessage( 'bot', '<span class="porto-ai-typing"><span></span><span></span><span></span></span>' );
	}
	function removeTyping() {
		$( '#porto-ai-messages .porto-ai-typing' ).closest( '.porto-ai-msg' ).remove();
	}

	function renderRecommendations( recs ) {
		if ( ! recs || ! recs.length ) {
			return;
		}
		var $wrap = $( '<div class="porto-ai-cards"></div>' );
		recs.forEach( function ( r ) {
			var d = r.demo || {};
			var meta = esc( d.builder ) +
				( d.woocommerce ? ' &middot; WooCommerce' : '' ) +
				( d.is_new ? ' &middot; ' + esc( __( 'New', 'porto' ) ) : '' );
			$wrap.append(
				'<div class="porto-ai-card">' +
					'<div class="porto-ai-card__thumb"><img src="' + esc( d.thumbnail ) + '" alt="' + esc( d.name ) + '" loading="lazy"/></div>' +
					'<div class="porto-ai-card__body">' +
						'<div class="porto-ai-card__name">' + esc( d.name ) + '</div>' +
						'<div class="porto-ai-card__meta">' + meta + '</div>' +
						( r.reason ? '<div class="porto-ai-card__reason">' + esc( r.reason ) + '</div>' : '' ) +
						'<div class="porto-ai-card__actions">' +
							'<a class="btn btn-quaternary btn-sm" href="' + esc( d.preview_url ) + '" target="_blank" rel="noopener noreferrer">' + esc( __( 'Preview', 'porto' ) ) + '</a>' +
							'<button type="button" class="btn btn-primary btn-sm porto-ai-use" data-demo="' + esc( r.id ) + '">' + esc( __( 'Use this demo', 'porto' ) ) + '</button>' +
						'</div>' +
					'</div>' +
				'</div>'
			);
		} );
		$( '#porto-ai-messages' ).append( $( '<div class="porto-ai-msg porto-ai-msg--bot"></div>' ).append( $wrap ) );
		scrollBottom();
	}

	function useDemo( id ) {
		// Queue the action and close the panel; gotoDemo() runs from afterClose,
		// once this popup is fully gone, so it never overlaps the install popup.
		pendingDemo = id;
		closePanel();
	}

	function gotoDemo( id ) {
		// Ensure the target card isn't hidden by the active filter.
		var $all = $( '.demo-sort-filters [data-filter-by="all"] a' );
		if ( $all.length ) {
			$all.trigger( 'click' );
		}
		var sel = ( $.escapeSelector ? $.escapeSelector( id ) : id );
		var $card = $( '#theme-install-demos #' + sel );
		if ( ! $card.length ) {
			return;
		}
		var $tile = $card.closest( '.theme' );
		$( 'html, body' ).animate( { scrollTop: $tile.offset().top - 120 }, 350 );
		$tile.addClass( 'porto-ai-highlight' );
		setTimeout( function () { $tile.removeClass( 'porto-ai-highlight' ); }, 2500 );
		$card.trigger( 'click' );
	}

	function send( text ) {
		text = ( text || '' ).trim();
		if ( ! text ) {
			return;
		}
		addMessage( 'user', esc( text ) );
		conversation.push( { role: 'user', content: text } );
		$( '#porto-ai-input' ).val( '' );
		$( '#porto-ai-chips' ).empty();
		$( '.porto-ai-send' ).prop( 'disabled', true );
		showTyping();

		api.post( cfg.rest.recommend, { conversation: conversation } ).then( function ( res ) {
			removeTyping();
			$( '.porto-ai-send' ).prop( 'disabled', false );

			var p = res.payload || {};
			if ( ! p.ok ) {
				addMessage( 'bot', api.errorHtml( p ) );
				return;
			}
			var data = p.data || {};
			if ( data.message ) {
				addMessage( 'bot', esc( data.message ) );
				conversation.push( { role: 'assistant', content: data.message } );
			}
			renderRecommendations( data.recommendations );
			if ( data.followup && data.followup.question ) {
				addMessage( 'bot', esc( data.followup.question ) );
				renderChips( data.followup.options, function ( label ) { send( label ); } );
			}
		} );
	}

	$( function () {
		$( document ).on( 'click', '[data-porto-ai-open]', function ( e ) { e.preventDefault(); openPanel(); } );
		$( document ).on( 'click', '[data-porto-ai-close]', function ( e ) { e.preventDefault(); closePanel(); } );
		$( document ).on( 'submit', '#porto-ai-form', function ( e ) { e.preventDefault(); send( $( '#porto-ai-input' ).val() ); } );
		// Enter sends; Shift+Enter inserts a newline.
		$( document ).on( 'keydown', '#porto-ai-input', function ( e ) {
			if ( ( e.key === 'Enter' || e.which === 13 ) && ! e.shiftKey ) {
				e.preventDefault();
				send( $( this ).val() );
			}
		} );
		$( document ).on( 'click', '.porto-ai-use', function () { useDemo( $( this ).data( 'demo' ) ); } );

		// Hide the floating button whenever ANY popup is open (the assistant panel,
		// title generator, or the demo install/remove popups all sit in the corner
		// and would overlap it). Magnific adds .mfp-wrap to the page while open.
		function syncFab() {
			$( '#porto-ai-fab' ).toggleClass( 'porto-ai-fab--hidden', document.getElementsByClassName( 'mfp-wrap' ).length > 0 );
		}
		if ( window.MutationObserver ) {
			new MutationObserver( syncFab ).observe( document.body, { childList: true } );
		}

		// Seed the initial suggestion chips.
		renderChips( cfg.suggestions, function ( label ) { send( label ); } );
	} );
}( jQuery ) );