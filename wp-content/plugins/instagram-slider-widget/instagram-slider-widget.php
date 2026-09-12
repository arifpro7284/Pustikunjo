<?php
/**
* Plugin Name: Social Slider Feed
* Plugin URI: https://cm-wp.com/instagram-slider-widget
* Version: 2.3.5
* Description: Shows Instagram, Facebook and YouTube responsive feeds in widgets, posts, pages, or anywhere else using shortcodes
* Author: Themeisle
* Author URI: https://themeisle.com
* Requires PHP: 7.4
* Text Domain: instagram-slider-widget
* Domain Path: /languages
* License:     GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* WordPress Available: yes
* Requires License: no
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Подключаем класс проверки совместимости
require_once dirname( __FILE__ ) . '/libs/factory/core/includes/class-factory-requirements.php';

$plugin_info = [
	'prefix'             => 'wis_',
	'plugin_name'        => 'wisw',
	'plugin_title'       => 'Social Slider Feed',
	'plugin_text_domain' => 'instagram-slider-widget',

	// Служба поддержки
	'support_details'    => [
		'url'       => 'https://wordpress.org/support/plugin/instagram-slider-widget/', // Ссылка на сайт плагина
		'pages_map' => [
			'features' => 'premium-features', // {site}/premium-features "страница возможности"
			'pricing'  => 'pricing', // {site}/prices страница "цены"
			'support'  => 'support', // {site}/support страница "служба поддержки"
			'docs'     => 'docs', // {site}/docs страница "документация"
		],
	],

	// Настройка обновлений плагина
	'has_updates'        => true,
	'updates_settings'   => [
		'repository'        => 'wordpress',
		'slug'              => 'instagram-slider-widget',
		'maybe_rollback'    => true,
		'rollback_settings' => [
			'prev_stable_version' => '0.0.0',
		],
	],

	// PLUGIN SUBSCRIBE FORM
	'subscribe_widget'   => false,

	'load_factory_modules' => [
		[ 'libs/factory/bootstrap', 'factory_bootstrap_483', 'admin' ],
		[ 'libs/factory/forms', 'factory_forms_481', 'admin' ],
		[ 'libs/factory/pages', 'factory_pages_481', 'admin' ],
		[ 'libs/factory/templates', 'factory_templates_135', 'admin' ],
		[ 'libs/factory/logger', 'factory_logger_150', 'all' ],
	],
];

global $wis_compatibility;

$wis_compatibility = new Wbcr_Factory481_Requirements( __FILE__, array_merge( $plugin_info, [
	'plugin_already_activate'          => defined( 'WIS_PLUGIN_ACTIVE' ),
	'required_php_version'             => '7.4',
	'required_wp_version'              => '6.0.0',
	'required_clearfy_check_component' => false,
] ) );

/**
 * If the plugin is compatible, then it will continue its work, otherwise it will be stopped,
 * and the user will throw a warning.
 */
if ( ! $wis_compatibility->check() ) {
	return;
}
/********************************************/
define( 'WIS_PLUGIN_ACTIVE', true );
define( 'WIS_PLUGIN_VERSION', $wis_compatibility->get_plugin_version() );
define( 'WIS_PLUGIN_FILE', __FILE__ );
define( 'WIS_ABSPATH', dirname( __FILE__ ) );
define( 'WIS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WIS_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );
define( 'WIS_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'WIS_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'WIS_PRODUCT_SLUG', basename( dirname( __FILE__ ) ) );

define( 'WIS_COMPONENTS_URL', WIS_PLUGIN_URL . '/components' );
define( 'WIS_COMPONENTS_DIR', WIS_PLUGIN_DIR . '/components' );

define( 'WIS_FEEDS_OPTION', 'feeds' );

/********************************************/



/**
 * -----------------------------------------------------------------------------
 * PLUGIN INIT
 * -----------------------------------------------------------------------------
 */
require_once WIS_PLUGIN_DIR . '/libs/factory/core/boot.php';
require_once WIS_PLUGIN_DIR . '/includes/class-helper.php';
require_once WIS_PLUGIN_DIR . '/includes/class-feed.php';
require_once WIS_PLUGIN_DIR . '/includes/class-feeds.php';
require_once WIS_PLUGIN_DIR . '/includes/class-profiles.php';
require_once WIS_PLUGIN_DIR . '/includes/class-plugin.php';
require_once WIS_PLUGIN_DIR . '/includes/class-wis-plugin-temp.php';

/**
 * Deactivate PRO plugin.
 *
 * @param WIS_Plugin $disable_admin_notice_obj
 */
function wisw_deactivate_pro_plugin( $disable_admin_notice_obj ) {
	$pro_slug = 'instagram-slider-widget-premium/instagram-slider-widget-premium.php';

	// If plugin isn't active, we stop immediately.
	if ( ! is_plugin_active( $pro_slug ) ) {
		return;
	}

	// Delete premium option
	$disable_admin_notice_obj->deletePopulateOption( 'premium_package' );
	
	remove_action( 'plugins_loaded', 'wisw_premium_load', 20 );

	// As register_deactivation_hook called, deactivate the pro plugin silently.
	deactivate_plugins( $pro_slug, true );

}

try {

	require_once WIS_PLUGIN_DIR . '/vendor/autoload.php';
	$instagram_slider_widget = new WIS_Plugin( __FILE__, array_merge( $plugin_info, [
		'plugin_version' => WIS_PLUGIN_VERSION,
	] ) );

	wisw_deactivate_pro_plugin( $instagram_slider_widget );
} catch ( Exception $e ) {
	// Plugin wasn't initialized due to an error
	define( 'WIS_PLUGIN_THROW_ERROR', true );

	$wis_plugin_error_func = function () use ( $e ) {
		$error = sprintf( 'The %s plugin has stopped. <b>Error:</b> %s Code: %s', 'Social Slider Feed', $e->getMessage(), $e->getCode() );
		echo '<div class="notice notice-error"><p>' . $error . '</p></div>';
	};

	add_action( 'admin_notices', $wis_plugin_error_func );
	add_action( 'network_admin_notices', $wis_plugin_error_func );
}
