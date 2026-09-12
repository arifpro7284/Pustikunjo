<?php

use WBCR\Factory_Logger_150\Logger;

/**
 * Factory Logger
 *
 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
 * @copyright (c) 17.01.2017, CreativeMotion
 * @package       factory-logger
 *
 * @version       1.2.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'FACTORY_LOGGER_150_LOADED' ) || ( defined( 'FACTORY_LOGGER_STOP' ) && FACTORY_LOGGER_STOP ) ) {
	return;
}

define( 'FACTORY_LOGGER_150_LOADED', true );
define( 'FACTORY_LOGGER_150_VERSION', '1.5.0' );

define( 'FACTORY_LOGGER_150_DIR', dirname( __FILE__ ) );
define( 'FACTORY_LOGGER_150_URL', plugins_url( '', __FILE__ ) );

load_plugin_textdomain( 'wbcr_factory_logger_150', false, dirname( plugin_basename( __FILE__ ) ) . '/langs' );

require_once( FACTORY_LOGGER_150_DIR . '/includes/class-logger.php' );

if ( is_admin() ) {
	require_once( FACTORY_LOGGER_150_DIR . '/includes/class-log-export.php' );
	require_once( FACTORY_LOGGER_150_DIR . '/pages/class-logger-impressive-page.php' );
	require_once( FACTORY_LOGGER_150_DIR . '/pages/class-logger-impressive-lite.php' );
	require_once( FACTORY_LOGGER_150_DIR . '/pages/class-logger-admin-page.php' );
}

/**
 * @param Wbcr_Factory481_Plugin $plugin
 */
add_action( 'wbcr_factory_logger_150_plugin_created', function ( $plugin ) {
	/* @var Wbcr_Factory481_Plugin $plugin */
	$plugin->set_logger( "WBCR\Factory_Logger_150\Logger" );
} );
