<?php

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Основной класс плагина Social Slider Feed
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class WIS_Plugin extends \Wbcr_Factory481_Plugin {

	/**
	 * @see self::app()
	 * @var \Wbcr_Factory481_Plugin
	 */
	private static $app;

	/**
	 * @var array Список слайдеров
	 */
	public $sliders = [];

	/**
	 * Статический метод для быстрого доступа к интерфейсу плагина.
	 *
	 * Позволяет разработчику глобально получить доступ к экземпляру класса плагина в любом месте
	 * плагина, но при этом разработчик не может вносить изменения в основной класс плагина.
	 *
	 * Используется для получения настроек плагина, информации о плагине, для доступа к вспомогательным
	 * классам.
	 *
	 * @return \Wbcr_Factory481_Plugin
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 * Статический метод для быстрого доступа к классу соцсети.
	 *
	 * @param string $class
	 *
	 * @return $class
	 */
	public static function social( $class ) {
		return new $class();
	}

	/**
	 * Конструктор
	 *
	 * Подробнее о свойстве $app см. self::app()
	 *
	 * @param string $plugin_path
	 * @param array $data
	 *
	 * @throws \Exception
	 */
	public function __construct( $plugin_path, $data ) {
		$this->load_components();

		parent::__construct( $plugin_path, $data );
		self::$app = $this;

		if ( is_admin() ) {
			// Регистрации класса активации/деактивации плагина
			$this->init_activation();

			// Инициализация скриптов для бэкенда
			$this->admin_scripts();
		} else {
			$this->front_scripts();
		}

		$this->global_scripts();

        add_filter( 'themeisle_sdk_products', [ __CLASS__, 'register_sdk' ] );
		add_filter( 'themeisle_sdk_blackfriday_data', [ $this, 'add_black_friday_data' ] );
    }

    /**
     * Register product into SDK.
     *
     * @param array $products All products.
     *
     * @return array Registered product.
     */
    public static function register_sdk( $products ) {
        $products[] = WIS_PLUGIN_FILE;

        return $products;
    }

	protected function init_activation() {
		include_once WIS_PLUGIN_DIR . '/admin/class-wis-activation.php';
		$this->registerActivation( 'WIS_Activation' );
	}

	public function load_components() {
		$components = scandir( WIS_COMPONENTS_DIR );
		foreach ( $components as $key => $value ) {
			if ( ! in_array( $value, [ '.', '..' ] ) ) {
				$comp = WIS_COMPONENTS_DIR . '/' . $value;
				if ( is_dir( $comp ) ) {
					if ( file_exists( $comp . '/load.php' ) ) {
						require_once $comp . '/load.php';
					}
				}
			}
		}
	}

	/**
	 * Регистрирует классы страниц в плагине
	 */
	private function register_pages() {
		self::app()->registerPage( 'WIS_FeedsPage', WIS_PLUGIN_DIR . '/admin/pages/feeds.php' );
		self::app()->registerPage( 'WIS_ProfilesPage', WIS_PLUGIN_DIR . '/admin/pages/profiles.php' );
		self::app()->registerPage( 'WIS_LogPage', WIS_PLUGIN_DIR . '/admin/pages/log.php' );
		self::app()->registerPage( 'Manual', WIS_PLUGIN_DIR . '/admin/pages/manual.php' );

		self::app()->registerPage( 'WIS_AboutPage', WIS_PLUGIN_DIR . '/admin/pages/about.php' );

	}

	/**
	 * Выполняет php сценарии, когда все WordPress плагины будут загружены
	 *
	 * @throws \Exception
	 * @since  1.0.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	public function plugins_loaded() {
		//if ( is_admin() ) {
		//$this->register_pages();
		//}
	}

	/**
	 * Код для админки
	 */
	private function admin_scripts() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );

		require_once WIS_PLUGIN_DIR . '/admin/class-page.php';
		add_action( 'init', function () {
			$this->register_pages();
		}, 30 );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'mark_internal_page' ] );
		add_action( 'admin_notices', [ $this, 'new_api_admin_notice' ] );
		add_action( 'admin_notices', [ $this, 'check_token_admin_notice' ] );
	}

	/**
	 * Код для админки и фронтенда
	 */
	private function global_scripts() {
		/**
		 * On widgets Init register Widget
		 */
		add_action( 'plugins_loaded', function () {
			add_action( 'widgets_init', [ 'WIG_Widget', 'register_widget' ] );
			add_action( 'widgets_init', [ 'WFB_Widget', 'register_widget' ] );
			add_action( 'widgets_init', [ 'WYT_Widget', 'register_widget' ] );
		} );
	}

	/**
	 * Код для фронтенда
	 */
	private function front_scripts() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function admin_enqueue_assets( $hook ) {
		if ( 'widgets.php' == $hook || 'post.php' == $hook ) {
			//wp_enqueue_style( 'jr-insta-admin-styles', WIS_PLUGIN_URL . '/admin/assets/css/jr-insta-admin.css', array(), WIS_PLUGIN_VERSION );
			//wp_enqueue_script( 'jr-insta-admin-script', WIS_PLUGIN_URL . '/admin/assets/js/jr-insta-admin.js', array( 'jquery' ), WIS_PLUGIN_VERSION, true );
			wp_enqueue_script( 'jr-tinymce-button', WIS_PLUGIN_URL . '/admin/assets/js/tinymce_button.js', [ 'jquery' ], WIS_PLUGIN_VERSION, false );

			$wis_shortcodes = json_encode( $this->get_isw_widgets() );
			wp_add_inline_script( 'jr-tinymce-button', "var wis_shortcodes = $wis_shortcodes;" );

			/*
			$account_nonce = json_encode( [ 'nonce' => wp_create_nonce( "addAccountByToken" ) ] );
			$wis_nonce     = json_encode( [
				'nonce'          => wp_create_nonce( 'wis_nonce' ),
				'remove_account' => __( 'Are you sure you want to delete this account?', 'instagram-slider-widget' ),
			] );
			wp_add_inline_script( 'jr-insta-admin-script', "var add_account_nonce = $account_nonce; var wis = $wis_nonce;" );
			*/
		}

	}

	/**
	 * Mark internal plugin pages for the Themeisle SDK (e.g. announcements, notices).
	 *
	 * @param string $hook The current admin page hook suffix.
	 */
	public function mark_internal_page( $hook ) {
		$is_internal = false;
		if ( strpos( $hook, 'wis_logger-wisw' ) !== false ) {
			do_action( 'themeisle_internal_page', WIS_PRODUCT_SLUG, 'logger' );
			$is_internal = true;
		} elseif ( strpos( $hook, 'feeds-wisw' ) !== false ) {
			do_action( 'themeisle_internal_page', WIS_PRODUCT_SLUG, 'feeds' );
			$is_internal = true;
		} elseif ( strpos( $hook, 'settings-wisw' ) !== false ) {
			do_action( 'themeisle_internal_page', WIS_PRODUCT_SLUG, 'settings' );
			$is_internal = true;
		}
		if ( $is_internal ) {
			wp_enqueue_script(
				'wis-tsdk-banner',
				WIS_PLUGIN_URL . '/admin/assets/js/tsdk-banner.js',
				[],
				WIS_PLUGIN_VERSION,
				true
			);
		}
	}

	public function enqueue_assets() {

	}

	/**
	 * Получает все виджеты этого плагина
	 *
	 * @return array
	 */
	public function get_isw_widgets() {
		$settings = WIG_Widget::app()->get_settings();
		$result   = [];
		foreach ( $settings as $key => $widget ) {
			$result[] = [
				'title' => $widget['title'],
				'id'    => $key,
			];
		}

		return $result;
	}

	/**
	 * Выводит нотис о том, что изменилось в новой версии
	 */
	public function new_api_admin_notice() {
		$text     = '';
		$accounts = $this->getOption( WIG_PROFILES_OPTION, [] );
		if ( count( $accounts ) ) {
			foreach ( $accounts as $account ) {
				if ( strlen( $account['token'] ) < 55 ) {
					$text .= '<p><b>@' . $account['username'] . '</b></p>';
				}
			}
		}
		if ( ! empty( $text ) ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<b>Social Slider Feed:</b><br>
					The plugin has moved to the new Instagram Basic Display API.<br>
					To make your widgets work again, reconnect your instagram accounts in the plugin settings.
					<a href="https://cm-wp.com/important-update-social-slider-widget/" class="">Read more about the
					                                                                            changes</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Выводит нотис о том, что нужно обновить токены
	 */
	public function check_token_admin_notice() {
		$text     = '';
		$accounts = $this->getOption( WIG_PROFILES_OPTION, [] );
		if ( count( $accounts ) ) {
			foreach ( $accounts as $account ) {
				if ( strlen( $account['token'] ) < 55 ) {
					$text .= '<p><b>@' . $account['username'] . '</b></p>';
				}
			}
		}
		if ( ! empty( $text ) ) {
			echo '<div class="notice notice-warning">
					<p><b>Social Slider Feed:</b><br>You need to reconnect this accounts in the <a href="' . admin_url( 'admin.php?page=settings-wisw&tab=instagram' ) . '">plugin settings</a>' . $text . '</p>
				  </div>';
		}
	}

	public function add_black_friday_data( $configs ) {
		$config = $configs['default'];

		if ( defined( 'ROP_LITE_VERSION' ) || defined( 'ROP_PRO_VERSION' ) ) {
			return $configs;
		}

		// translators: 1. Number of free licenses, 2. The price of the product.
		$config['message']             = sprintf( __( 'You’re using Social Slider Feed, and the team behind it is celebrating Black Friday by giving away %1$s licences of Revive Social Pro worth %2$s. Automatically share your WordPress content to X, Facebook, LinkedIn, and more with scheduling and analytics. Claim yours before they run out.', 'instagram-slider-widget' ), 100, '$99' );
		$config['cta_label'] = __( 'Get Revive Social free', 'instagram-slider-widget' );
		$config['plugin_meta_message'] = __( 'Black Friday Sale - Get Revive Social free', 'instagram-slider-widget' );
		$config['sale_url'] = add_query_arg(
			array(
				'utm_term' => 'free',
			),
			tsdk_translate_link( tsdk_utmify( 'https://themeisle.link/rs-claim-bf', 'bfcm', 'social-slider' ) )
		);

		$configs[ WIS_PRODUCT_SLUG ] = $config;

		return $configs;
	}
}
