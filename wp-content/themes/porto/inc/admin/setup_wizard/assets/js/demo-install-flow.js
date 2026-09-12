/**
 * Porto Setup Wizard — demo install flow.
 *
 * When a demo card is clicked and demo content is already installed, show a
 * styled prompt (instead of the browser's native confirm):
 *   - "Remove first, then install" → remove the installed demo automatically,
 *     then close the remove popup and open the demo install popup.
 *   - "No, just install"           → open the demo install popup as usual.
 * If nothing is installed, the install popup opens directly, as before.
 *
 * Runs WITHOUT modifying admin.js: a direct (non-delegated) handler on the card
 * fires before admin.js's document-delegated handler, so we can take over or
 * defer to it as needed.
 */
( function ( $ ) {
	'use strict';

	var MFP = {
		type: 'inline',
		mainClass: 'mfp-with-zoom',
		zoom: { enabled: true, duration: 300 }
	};

	var pending = null; // the clicked .theme-wrapper awaiting an install decision

	// Disable ESC while the demo install/remove popup is open, so it can't be
	// closed (and an import interrupted) by accident. Magnific closes on a
	// document keyup; a capture-phase listener runs first, and
	// stopImmediatePropagation prevents Magnific's handler from firing.
	function blockEscForInstall( e ) {
		if ( ( e.key === 'Escape' || e.keyCode === 27 || e.which === 27 )
			&& document.querySelector( '.mfp-wrap .porto-install-demo, .mfp-wrap .porto-remove-demo' ) ) {
			e.preventDefault();
			e.stopImmediatePropagation();
		}
	}
	document.addEventListener( 'keydown', blockEscForInstall, true );
	document.addEventListener( 'keyup', blockEscForInstall, true );

	function removeAvailable() {
		var $btn = $( '.porto-remove-demo .btn' );
		return $btn.length && ! $btn.prop( 'disabled' );
	}

	// Porto lazy-loads admin images, so the grid screenshot cloned into the
	// install popup can be stuck on its placeholder. Force it to load using
	// Porto's own lazy-load helper.
	function forceLoadInstallImage() {
		if ( ! $.fn.portoAdminLazyLoadImages ) {
			return;
		}
		$( '.porto-install-demo .theme-img img[data-original]' ).each( function () {
			$.fn.portoAdminLazyLoadImages( this );
		} );
	}

	function openPopup( src ) {
		$.magnificPopup.open( $.extend( {}, MFP, { items: { src: src } } ) );
	}

	// Accept: open the remove popup, delete the installed demo, then auto-open
	// the demo install popup once removal finishes.
	function removeThenInstall() {
		var $wrapper = pending;
		openPopup( '.porto-remove-demo' ); // replaces the confirm popup

		var $status = $( '.porto-remove-demo .remove-status' );
		if ( ! $status.length ) {
			$( '.porto-remove-demo .btn' ).trigger( 'click' );
			return;
		}
		$status.html( '' );

		// admin.js updates .remove-status as it deletes each content type, ending
		// with "Removed successfully." (no spinner). Watch for that, then swap to
		// the install popup.
		var finished = false;
		var observer = new MutationObserver( function () {
			if ( finished ) {
				return;
			}
			var $s = $( '.porto-remove-demo .remove-status' );
			// Still working while a spinner shows or the status is empty.
			if ( $s.find( '.porto-ajax-loader' ).length || ! $s.text().trim() ) {
				return;
			}
			finished = true;
			observer.disconnect();
			if ( /fail|refresh/i.test( $s.text() ) ) {
				return; // failure → leave the popup open showing the error
			}
			// Success → brief beat to show "Removed successfully", then open the
			// demo install popup for the originally-clicked demo.
			setTimeout( function () {
				if ( $wrapper && $wrapper.length ) {
					installViaCore( $wrapper );
				} else {
					$.magnificPopup.close();
				}
			}, 800 );
		} );
		observer.observe( $status.get( 0 ), { childList: true, subtree: true } );

		// admin.js performs the deletion (recursive AJAX) + updates remove-status.
		$( '.porto-remove-demo .btn' ).trigger( 'click' );
	}

	// Open the demo install popup by handing the click back to admin.js. Disabling
	// the remove button makes admin.js skip its own native confirm; the bypass
	// flag stops our handler from re-intercepting the re-dispatch.
	function installViaCore( $wrapper ) {
		$.magnificPopup.close();
		var $rm = $( '.porto-remove-demo .btn' ),
			wasEnabled = $rm.length && ! $rm.prop( 'disabled' );

		if ( wasEnabled ) {
			$rm.prop( 'disabled', true );
		}
		$wrapper.data( 'portoBypass', 1 );
		$wrapper.trigger( 'click' );
		$wrapper.removeData( 'portoBypass' );
		if ( wasEnabled ) {
			$rm.prop( 'disabled', false );
		}
	}

	$( function () {
		// Whenever the install popup's screenshot is (re)populated by admin.js,
		// force-load it — covers every open path (direct, skip, remove-then-install).
		var themeImg = document.querySelector( '.porto-install-demo .theme-img' );
		if ( themeImg && window.MutationObserver ) {
			new MutationObserver( forceLoadInstallImage ).observe( themeImg, { childList: true, subtree: true } );
		}

		// Direct bind → fires before admin.js's delegated handler on document.
		$( '.porto-install-demos .theme .theme-wrapper' ).on( 'click', function ( e ) {
			var $wrapper = $( this );

			if ( $wrapper.data( 'portoBypass' ) ) {
				return; // re-dispatch from installViaCore(): let it bubble to admin.js
			}
			var $theme = $wrapper.closest( '.theme' );
			if ( $theme.hasClass( 'open-classic' ) || $theme.hasClass( 'open-shop' ) || $theme.hasClass( 'open-blog' ) ) {
				return; // grouped category tiles: admin.js switches the filter
			}
			if ( ! removeAvailable() ) {
				return; // nothing installed → admin.js opens install directly
			}

			// Take over: show the styled prompt instead of the native confirm.
			e.preventDefault();
			e.stopImmediatePropagation();
			pending = $wrapper;
			openPopup( '.porto-confirm-install' );
		} );

		$( document ).on( 'click', '.porto-confirm-install .btn-confirm-remove', function ( e ) {
			e.preventDefault();
			removeThenInstall();
		} );

		$( document ).on( 'click', '.porto-confirm-install .btn-confirm-skip', function ( e ) {
			e.preventDefault();
			if ( pending ) {
				installViaCore( pending );
			}
		} );
	} );
}( jQuery ) );