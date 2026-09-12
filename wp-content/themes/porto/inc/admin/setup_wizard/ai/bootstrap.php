<?php
/**
 * Porto AI — module bootstrap.
 *
 * Loads the AI Setup-Wizard classes and initializes the manager. Required once
 * from setup_wizard.php. Safe to include multiple times (guarded class defs +
 * singletons).
 *
 * @package Porto
 */
defined( 'ABSPATH' ) || exit;

$porto_ai_dir = __DIR__;

require_once $porto_ai_dir . '/class-ai-demo-repository.php';
require_once $porto_ai_dir . '/class-ai-proxy-client.php';
require_once $porto_ai_dir . '/class-ai-prompt-builder.php';
require_once $porto_ai_dir . '/class-ai-demo-assistant.php';
require_once $porto_ai_dir . '/class-ai-title-generator.php';
require_once $porto_ai_dir . '/class-ai-logo-generator.php';
require_once $porto_ai_dir . '/class-ai-rest-api.php';
require_once $porto_ai_dir . '/class-ai-ui.php';
require_once $porto_ai_dir . '/class-ai-manager.php';

unset( $porto_ai_dir );

Porto_AI_Manager::instance();