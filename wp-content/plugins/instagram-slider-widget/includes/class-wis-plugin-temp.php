<?php

namespace Instagram\Includes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Temporary class to migrate to 2.0.0
 * */

class WIS_Plugin {

	public static function app() {
		return \WIS_Plugin::app();
	}
}
