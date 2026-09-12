<?php

namespace PTU\Vendor;

\defined( 'ABSPATH' ) || exit;

/**
 * ACF Support.
 */
class ACF {

	/**
	 * ACF Class Constructor.
	 *
	 * @since 1.2.5
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		\add_filter( 'acf/settings/enable_post_types', [ $this, 'maybe_disable_post_types' ] );
	}

	/**
	 * Disable ACF post types if no ACF items exist.
	 *
	 * @since 1.2.5
	 *
	 * @access public
	 * @return bool
	 */
	public function maybe_disable_post_types( $check ) {
		$option = \get_option( 'ptu_disable_acf_post_types', null );
		if ( null === $option ) {
			if ( \is_admin() && \current_user_can( 'manage_options' ) ) {
				$acf_items = \get_posts( [
					'post_type'        => [ 'acf-post-type', 'acf-taxonomy' ],
					'posts_per_page'   => 1,
					'post_status'      => 'any',
					'fields'           => 'ids',
					'suppress_filters' => true,
				] );
				$updated = \update_option( 'ptu_disable_acf_post_types', (int) empty( $acf_items ), false );
				if ( $updated && empty( $acf_items ) ) {
					return false;
				}
			}
		} else {
			$check = ! $option;
		}
		return $check;
	}

}

new ACF;
