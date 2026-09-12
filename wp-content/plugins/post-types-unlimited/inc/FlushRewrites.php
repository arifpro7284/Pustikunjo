<?php

namespace PTU;

\defined( 'ABSPATH' ) || exit;

/**
 * FlushRewrites.
 */
class FlushRewrites {

	/**
	 * The FlushRewrites class constructor.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		\add_action( 'admin_init', array( $this, 'flush_rewrite_rules' ) );
	}

	/**
	 * Flush rewrite rules as needed
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function flush_rewrite_rules() {
		if ( \get_option( 'ptu_flush_rewrite_rules' ) ) {
			\flush_rewrite_rules();
			\delete_option( 'ptu_flush_rewrite_rules' );
		}
	}

}

new FlushRewrites;
