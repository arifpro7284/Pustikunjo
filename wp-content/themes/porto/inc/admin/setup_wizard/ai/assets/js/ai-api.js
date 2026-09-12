/**
 * Porto AI — shared REST client.
 *
 * Thin wrapper over fetch that attaches the wp_rest nonce and normalizes the
 * { ok, data } / { ok:false, error } envelope the PHP REST layer returns.
 * Exposes window.PortoAIApi for the assistant + title-generator scripts.
 */
( function ( wp ) {
	'use strict';

	var cfg = window.portoAI || {};
	var __ = ( wp && wp.i18n && wp.i18n.__ ) ? wp.i18n.__ : function ( s ) { return s; };

	var MESSAGES = {
		not_configured:  __( 'The AI assistant is not available right now.', 'porto' ),
		unauthorized:    __( 'Your Porto license could not be verified.', 'porto' ),
		license_invalid: __( 'Activate your Porto license to use the AI assistant.', 'porto' ),
		license_inactive: __( 'Your license has no active plan.', 'porto' ),
		rate_limited:    __( 'Too many requests — please wait a moment.', 'porto' ),
		quota_exceeded:  __( 'Daily AI limit reached. Please try again later.', 'porto' ),
		byok_required:   __( 'The shared Porto AI service has reached its usage limit. Add your own OpenAI API key in Porto → Theme Options → AI Features to keep using AI features without limits.', 'porto' ),
		upstream_error:  __( 'The AI service is busy. Please try again.', 'porto' ),
		upstream_timeout: __( 'The AI service timed out. Please try again.', 'porto' ),
		network:         __( 'Network error. Check your connection and try again.', 'porto' )
	};

	function friendlyError( payload ) {
		var err = ( payload && payload.error ) || {};
		var base;
		if ( err.code && MESSAGES[ err.code ] ) {
			base = MESSAGES[ err.code ];
		} else if ( err.message ) {
			base = err.message;
		} else {
			base = __( 'Something went wrong. Please try again.', 'porto' );
		}
		// Surface the upstream detail (e.g. the real OpenAI error) when present.
		return err.detail ? base + ' — ' + err.detail : base;
	}

	function escHtml( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	/**
	 * Like friendlyError(), but returns safe HTML. Appends a clickable action
	 * link when the error carries action_url (e.g. byok_required → Theme Options).
	 */
	function friendlyErrorHtml( payload ) {
		var err  = ( payload && payload.error ) || {};
		var html = escHtml( friendlyError( payload ) );
		if ( err.action_url ) {
			html += ' <a class="porto-ai-error-action" target="_blank" rel="noopener" href="' +
				escHtml( err.action_url ) + '">' +
				escHtml( err.action_label || __( 'Open settings', 'porto' ) ) + '</a>';
		}
		return html;
	}

	window.PortoAIApi = {
		cfg: cfg,
		__: __,

		/**
		 * POST JSON to a REST url. Resolves to { httpOk, status, payload }.
		 */
		post: function ( url, body ) {
			return fetch( url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cfg.nonce || ''
				},
				body: JSON.stringify( body || {} )
			} ).then( function ( r ) {
				return r.json().catch( function () {
					return { ok: false, error: { code: 'bad_json' } };
				} ).then( function ( j ) {
					return { httpOk: r.ok, status: r.status, payload: j };
				} );
			} ).catch( function () {
				return { httpOk: false, status: 0, payload: { ok: false, error: { code: 'network' } } };
			} );
		},

		error: friendlyError,
		errorHtml: friendlyErrorHtml
	};
}( window.wp ) );