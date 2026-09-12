<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class is responsible for the operation of the logs page for a lite interface
 *
 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
 * @copyright (c) 18.01.2025, CreativeMotion
 * @version       1.0
 */
class Wbcr_FactoryLogger150_Lite extends \WBCR\Factory_Templates_135\ImpressiveLite {

	/**
	 * {@inheritdoc}
	 */
	public $id;

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-admin-tools';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * Represents the plugin instance.
	 *
	 * This variable may hold the main plugin functionality, configurations, or settings
	 * required across different parts of the system where the plugin is used.
	 *
	 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
	 */
	public $plugin;

	/**
	 * @param Wbcr_Factory481_Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->id = $plugin->getPrefix() . "logger";

		$this->page_menu_short_description = __( 'Plugin debug report', 'wbcr_factory_logger_150' );

		add_action( 'wp_ajax_wbcr_factory_logger_150_' . $plugin->getPrefix() . 'logs_cleanup', [
			$this,
			'ajax_cleanup'
		] );

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->styles->add( FACTORY_LOGGER_150_URL . '/assets/css/logger.css' );
		$this->scripts->add( FACTORY_LOGGER_150_URL . '/assets/js/logger.js', [ 'jquery' ], 'wbcr_factory_logger_150', FACTORY_LOGGER_150_VERSION );
		$this->scripts->localize( 'wbcr_factory_logger_150', [
			'clean_logs_nonce' => wp_create_nonce( 'wbcr_factory_logger_150_clean_logs' ),
			'plugin_prefix'    => $this->plugin->getPrefix(),
		] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMenuTitle() {
		return __( 'Plugin Log', 'wbcr_factory_logger_150' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function showPageContent() {
		$buttons = "
            <div class='btn-group'>
                <a href='" . wp_nonce_url( $this->getActionUrl( 'export' ), 'export-' . $this->plugin->getPluginName() ) . "'
                   class='button button-primary'>" . __( 'Export Debug Information', 'wbcr_factory_logger_150' ) . "</a>
                <a href='#'
                   class='button button-secondary'
                   onclick='wbcr_factory_logger_150_LogCleanup(this);return false;'
                   data-working='" . __( 'Working...', 'wbcr_factory_logger_150' ) . "'>" . sprintf( __( 'Clean-up Logs (<span id="wbcr-log-size">%s</span>)', 'wbcr_factory_logger_150' ), $this->get_log_size_formatted() ) . "
                   </a>
            </div>";
		?>
		<div class="wbcr-factory-page-group-header" style="margin-top:0;">
			<strong><?php _e( 'Plugin Log', 'wbcr_factory_logger_150' ) ?></strong>
			<p>
				<?php _e( 'In this section, you can track how the plugin works. Sending this log to the developer will help you resolve possible issues.', 'wbcr_factory_logger_150' ) ?>
			</p>
		</div>
		<div class="wbcr-factory-page-group-body" style="padding: 0 20px">
			<?php echo $buttons; ?>
			<div class="wbcr-log-viewer" id="wbcr-log-viewer">
				<?php echo $this->plugin->logger->prettify() ?>
			</div>
			<?php echo $buttons; ?>
		</div>
		<?php
	}

	public function ajax_cleanup() {
		check_admin_referer( 'wbcr_factory_logger_150_clean_logs', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1 );
		}

		if ( ! $this->plugin->logger->clean_up() ) {
			wp_send_json_error( [
				'message' => esc_html__( 'Failed to clean-up logs. Please try again later.', 'wbcr_factory_logger_150' ),
				'type'    => 'danger',
			] );
		}

		wp_send_json( [
			'message' => esc_html__( 'Logs clean-up successfully', 'wbcr_factory_logger_150' ),
			'type'    => 'success',
		] );
	}

	/**
	 * Processing log export action in form of ZIP archive.
	 */
	public function exportAction() {
		if ( ! ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'export-' . $this->plugin->getPluginName() ) )
		     || ! $this->plugin->current_user_can() ) {
			wp_die( __( 'You do not have sufficient permissions to perform this action!', 'wbcr_factory_logger_150' ) );
		}

		$export = new WBCR\Factory_Logger_150\Log_Export( $this->plugin->logger );

		if ( $export->prepare() ) {
			$export->download( true );
		}
	}

	/**
	 * Get log size formatted.
	 *
	 * @return false|string
	 */
	private function get_log_size_formatted() {

		try {
			return size_format( $this->plugin->logger->get_total_size() );
		} catch ( \Exception $exception ) {
			$this->plugin->logger->error( sprintf( 'Failed to get total log size as exception was thrown: %s', $exception->getMessage() ) );
		}

		return '';
	}
}
