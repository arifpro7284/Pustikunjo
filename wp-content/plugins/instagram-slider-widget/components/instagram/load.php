<?php
global $wis_compatibility;

define('WIG_COMPONENT_VERSION', $wis_compatibility->get_plugin_version());
define('WIG_COMPONENT_URL', WIS_COMPONENTS_URL . '/instagram');
define('WIG_COMPONENT_DIR', WIS_COMPONENTS_DIR . '/instagram');
define('WIG_COMPONENT_VIEWS_DIR', WIG_COMPONENT_DIR . '/html_templates');


// Функция для получения ID приложений из прослойки
function get_app_ids_from_proxy()
{
    $url = 'https://instagram.cm-wp.com/get-id-for-app';

    $args = array(
        'timeout' => 15,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);

    $data = json_decode($body, true);

    if (!is_array($data) || empty($data['instagram_client_id']) || empty($data['facebook_client_id'])) {
        return false;
    }

    return array(
        'instagram_client_id' => $data['instagram_client_id'],
        'facebook_client_id' => $data['facebook_client_id'],
    );
}

// Функция для получения ID приложений
function get_app_ids()
{
    $app_ids = get_app_ids_from_proxy();

    if (!$app_ids) {
        return array(
            'instagram_client_id' => '1172683874187860',
            'facebook_client_id' => '2302390870204694',
        );
    }

    return $app_ids;
}

// Функция для получения ID приложений из кэша
function get_cached_app_ids()
{
    return get_transient('app_ids_cache');
}

$app_ids = get_cached_app_ids();

// Если ID приложений не найдены в кэше, обновляем их
if (!$app_ids) {
    $app_ids = get_app_ids();
    // Сохраняем полученные ID приложений в кэш
    if ($app_ids) {
        set_transient('app_ids_cache', $app_ids, DAY_IN_SECONDS); // Кэшируем на сутки
    }
}

if ($app_ids) {
    define('WIS_INSTAGRAM_CLIENT_ID', $app_ids['instagram_client_id']);
    define('WIS_FACEBOOK_CLIENT_ID', $app_ids['facebook_client_id']);
}
//define( 'WIS_INSTAGRAM_CLIENT_ID', '1883092561894221' );  // test APP ID
//define( 'WIS_FACEBOOK_CLIENT_ID', '852118372457929' ); // test APP ID


define('WIG_PROFILES_OPTION', 'account_profiles');
define('WIG_BUSINESS_PROFILES_OPTION', 'account_profiles_new');

define('WIG_USERS_SELF_URL', 'https://graph.instagram.com/me');
define('WIG_USERS_SELF_MEDIA_URL', 'https://graph.instagram.com/');

require_once WIG_COMPONENT_DIR . '/includes/functions.php';
require_once WIG_COMPONENT_DIR . '/includes/class-instagram-feed.php';
require_once WIG_COMPONENT_DIR . '/includes/class-instagram-profiles.php';
require_once WIG_COMPONENT_DIR . '/includes/class-instagram-widget.php';
require_once WIG_COMPONENT_DIR . '/includes/class-instagram-pro.php';

new WIS_Instagram_Pro();
