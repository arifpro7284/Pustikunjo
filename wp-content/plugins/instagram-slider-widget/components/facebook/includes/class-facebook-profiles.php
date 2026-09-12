<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIS_Facebook_Profiles extends WIS_Profiles {
	/**
	 * @var string
	 */
	public $social = 'facebook';

	/**
	 * @var string
	 */
	public $profiles_option_name = WIS_FACEBOOK_ACCOUNT_PROFILES_OPTION_NAME;

	/**
	 * WIS_Facebook_Profiles constructor.
	 *
	 * @param $page WIS_ProfilesPage
	 */
	public function __construct( $page = null ) {
		if ( $page ) {
			parent::__construct( $page );
		}

		//AJAX
		add_action( 'wp_ajax_wfb_add_account_by_token', [ $this, 'add_account_by_token' ] );
	}

	/**
	 * @throws Exception
	 */
	public function add_account_by_token() {
		if ( isset( $_POST['account'] ) && ! empty( $_POST['account'] ) && isset( $_POST['_ajax_nonce'] ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 2 );
			}

			// Security: Verify nonce properly
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), 'wfb_nonce' ) ) {
				wp_die( -1 );
			}

			$account = json_decode( wp_unslash( $_POST['account'] ), true );

			$connected_profiles                     = WIS_Plugin::app()->getOption( $this->profiles_option_name, [] );
			$connected_profiles[ $account['name'] ] = [
				'name'   => $account['name'],
				'avatar' => $account['picture']['data']['url'],
				'id'     => $account['id'],
				'token'  => $account['access_token'],
				'is_me'  => $account['is_me'],
			];
			WIS_Plugin::app()->updateOption( $this->profiles_option_name, $connected_profiles );

			wp_die( 'Ok' );
		}
	}

	/**
	 * @param $access_token
	 * @param string $username
	 *
	 * @return string
	 */
	public function update_profiles( $access_token, $username = "" ) {
		$html = '';
		if ( $access_token ) {
			$args = [
				'access_token' => $access_token,
				'fields'       => 'name,picture,access_token',
				'limit'        => 200,
			];

			$connected_profiles = [];

            update_option( 'instagram_slider_widget_logger_flag', 'yes' );
			$url      = WFB_FACEBOOK_SELF_URL . "me/accounts";
			$response = wp_remote_get( esc_url_raw( add_query_arg( $args, $url ) ) );
			if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
				$pages = json_decode( wp_remote_retrieve_body( $response ), true );
				foreach ( $pages['data'] as $profile ) {
					$profile['is_me']                       = false;
					$connected_profiles[ $profile['name'] ] = $profile;
				}
			}

			foreach ( $connected_profiles as $profile ) {
				$html .= "<div class='wis-row wis-row-style' id='wis-facebook-row' data-account='" . esc_attr( json_encode( $profile ) ) . "'>";
				$html .= "<div class='wis-col-1 wis-col1-style'><img src='" . esc_url( $profile['picture']['data']['url'] ) . "' width='50' alt='" . esc_attr( $profile['name'] ) . "'></div>";
				$html .= "<div class='wis-col-2 wis-col2-style'>" . esc_html( $profile['name'] ) . "</div>";
				$html .= "</div>";
			}
		}

		return $html;
	}

	/**
	 * Логика на вкладке facebook
	 *
	 * @return string
	 */
	public function content() {
		if ( isset( $_GET['tab'] ) && 'facebook' === $_GET['tab'] ) {
			if ( isset( $_GET['token_error'] ) ) {
				$_SERVER['REQUEST_URI'] = str_replace( '#_', '', esc_url( remove_query_arg( 'token_error' ) ) );
			} else {
				if ( isset( $_GET['access_token'] ) ) {
					// Security: Sanitize access token from URL
					$token                  = sanitize_text_field( wp_unslash( $_GET['access_token'] ) );
					$choose_account_html    = $this->update_profiles( $token );
					$_SERVER['REQUEST_URI'] = esc_url( remove_query_arg( 'access_token' ) );
					?>
					<div id="wis_accounts_modal" class="wis_accounts_modal">
						<div class="wis_modal_header">
							Choose Account:
						</div>
						<div class="wis_modal_content">
							<?php echo ( $choose_account_html ); ?>
						</div>
					</div>
					<div id="wis_modal_overlay" class="wis_modal_overlay"></div>
					<span class="wis-overlay-spinner is-active">&nbsp;</span>
					<?php
				}
			}
		}

		$accounts = WIS_Plugin::app()->getPopulateOption( WIS_FACEBOOK_ACCOUNT_PROFILES_OPTION_NAME, [] );

		$data = [
			'authorize_url' => "https://instagram.cm-wp.com/facebook?" . http_build_query( [
					"app_id" => WIS_FACEBOOK_CLIENT_ID,
					"state"  => $this->getSocialUrl(),
				] ),
			'accounts'      => $accounts,
			'social'        => $this->social,
		];

		$result = $this->page->render( WFB_COMPONENT_VIEWS_DIR . '/accounts', $data );

		return $result;
	}

	public function delete_account( $name ) {
		check_admin_referer( 'wis_delete_profile', 'nonce' );

		$accounts = WIS_Plugin::app()->getOption( $this->profiles_option_name, [] );
		if ( isset( $accounts[ $name ] ) ) {
			unset( $accounts[ $name ] );
		}
		WIS_Plugin::app()->updateOption( $this->profiles_option_name, $accounts );
	}

}