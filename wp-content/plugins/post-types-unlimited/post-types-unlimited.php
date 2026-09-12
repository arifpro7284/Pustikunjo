<?php
/**
 * Plugin Name:       Post Types Unlimited
 * Plugin URI:        https://wordpress.org/plugins/post-types-unlimited/
 * Description:       Create unlimited custom post types and custom taxonomies.
 * Version:           1.2.9
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            WPExplorer
 * Author URI:        https://www.wpexplorer.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       post-types-unlimited
 * Domain Path:       /languages/
 */

/*
Post Types Unlimited is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Post Types Unlimited is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Post Types Unlimited. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

defined( 'ABSPATH' ) || exit;

/**
 * Main Post_Types_Unlimited Class.
 *
 * @since 1.0
 */
if ( ! class_exists( 'Post_Types_Unlimited' ) ) {

	final class Post_Types_Unlimited {

		/**
		 * Curent plugin version.
		 */
		public const VERSION = '1.2.8';

		/**
		 * Post_Types_Unlimited constructor.
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Define plugin version.
			define( 'PTU_VERSION', self::VERSION );

			// Define main file path.
			define( 'PTU_MAIN_FILE_PATH', __FILE__ );

			// Define plugin directory path.
			define( 'PTU_PLUGIN_DIR_PATH', plugin_dir_path( PTU_MAIN_FILE_PATH ) );

			// Register activation and deactivation hooks.
			register_activation_hook( PTU_MAIN_FILE_PATH, [ self::class, 'on_activation' ] );
			register_deactivation_hook( PTU_MAIN_FILE_PATH, [ self::class, 'on_deactivation' ] );

			// Load Text Domain
			add_action( 'init', self::class . '::load_text_domain' );

			// Register Custom Post Types.
			require_once PTU_PLUGIN_DIR_PATH . 'inc/PostTypes.php';

			// Register Custom Taxonomies.
			require_once PTU_PLUGIN_DIR_PATH . 'inc/Taxonomies.php';

			// Create custom metaboxes.
			require_once PTU_PLUGIN_DIR_PATH . 'inc/Metaboxes.php';

			// Flush Rewrite Rules as needed.
			require_once PTU_PLUGIN_DIR_PATH . 'inc/FlushRewrites.php';

			// Vendor Support.
			require_once PTU_PLUGIN_DIR_PATH . 'vendor/WPBakery.php';
			require_once PTU_PLUGIN_DIR_PATH . 'vendor/ACF.php';
		}

		/**
		 * Plugin activation callback.
		 * 
		 * @since  1.2.5
		 * @access public
		 * @return void
		 */
		public static function on_activation() {
			flush_rewrite_rules();
		}

		/**
		 * Plugin deactivation callback.
		 * 
		 * @since  1.2.5
		 * @access public
		 * @return void
		 */
		public static function on_deactivation() {
			flush_rewrite_rules();
			delete_option( 'ptu_disable_acf_post_types' );
			delete_option( 'ptu_flush_rewrite_rules' );
		}

		/**
		 * Load text domain.
		 *
		 * @since  1.0.6
		 * @access public
		 * @return void
		 */
		public static function load_text_domain() {
			load_plugin_textdomain(
				'post-types-unlimited',
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);
		}

	}

	new Post_Types_Unlimited;

}