<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIS_Instagram_Pro {

    /**
	 * @see self::app()
	 * @var self
	 */
	private static $instance;

	/**
	 * @return self
	 */
	public static function instance() {
		return self::$instance;
	}

    public function __construct() {
        self::$instance = $this;

        if ( is_admin() ) {
			add_filter( 'wis/sliders', [ $this, 'sliders' ], 10, 1 );
			add_filter( 'wis/options/link_to', [ $this, 'link' ], 10, 1 );
			add_filter( 'wis/mob_settings', [ $this, 'settings_instagram' ], 10, 6 );
        } else {
			add_filter( 'wis/pro/display_images', [ $this, 'display_images' ], 10, 3 );
		}
		
		add_filter( 'wis/account/profiles', [ $this, 'get_accounts' ], 10, 2 );
		add_filter( 'wis/images/count', [ $this, 'get_images_count_pro' ], 10, 4 );
		add_action( 'wp_ajax_wis-get-more', [ $this, 'wis_get_more' ] );
		add_action( 'wp_ajax_nopriv_wis-get-more', [ $this, 'wis_get_more' ] );
    }

	/**
	 * Получает аккаунты
	 *
	 * @param array $profiles
	 * @param bool $is_business
	 *
	 * @return array
	 */
	public function get_accounts( $profiles, $is_business = false ) {
		if ( $is_business ) {
			$ar = WIS_Plugin::app()->getOption( 'account_profiles_new', [] );
		} else {
			$ar = WIS_Plugin::app()->getOption( 'account_profiles', [] );
		}
		if ( ! is_array( $ar ) ) {
			$ar = [];
		}

		return $ar;
	}

	/**
	 * Получает указанное количество постов
	 *
	 * @param $results
	 * @param $media
	 * @param $count
	 * @param $is_business
	 *
	 * @return array
	 */
	public function get_images_count_pro( $results, $media, $count, $is_business ) {
		while ( count( $results ) > 0 && count( $results ) < $count ) {

			if ( $is_business ) {
				$url = isset( $media['paging']['next'] ) ? $media['paging']['next'] : [];
			} else {
				$url = isset( $media['media']['paging']['next'] ) ? $media['media']['paging']['next'] : [];
			}

			$response = wp_remote_get( $url );
			if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
				$media = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $media['data'] ) ) {
					$results = array_merge( $results, $media['data'] );
				} else {
					break;
				}
			} else {
				break;
			}
		}

		return $results;
	}

	/**
	 * Добавляет слайдеры
	 *
	 * @param $profiles
	 *
	 * @return array
	 */
	public function sliders( $sliders ) {
		$sliders2                 = $sliders;
		$sliders2["slick_slider"] = __( 'Slick Slider', 'instagram-slider-widget' );
		$sliders2["masonry"]      = __( 'Masonry', 'instagram-slider-widget' );
		$sliders2["highlight"]    = __( 'Highlight', 'instagram-slider-widget' );
		$sliders2["showcase"]     = __( 'Shopifeed - Thumbnails', 'instagram-slider-widget' );
		$sliders2["masonry_lite"] = __( 'Masonry Lite', 'instagram-slider-widget' );

		return $sliders2;
	}

	public function link( $options ) {
		$new_options          = $options;
		$new_options['popup'] = __( 'Pop Up', 'instagram-slider-widget' );

		return $new_options;
	}

	public function settings_instagram( $content, $_this, $instance, $sliders, $options_linkto, $w_id ) {
		return WIS_Page::instance()->render( WIG_COMPONENT_VIEWS_DIR . '/form-feed-mobile', [
			'_this'          => $_this,
			'instance'       => $instance,
			'sliders'        => $sliders,
			'options_linkto' => $options_linkto,
			'w_id'           => $w_id,
		] );
	}

	/**
	 * Dispaly images.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function display_images( $content, $args, $wisw ) {

		$account          = isset( $args['account'] ) && ! empty( $args['account'] ) ? $args['account'] : false;
		$account_business = isset( $args['account_business'] ) && ! empty( $args['account_business'] ) ? $args['account_business'] : false;
		$username         = isset( $args['username'] ) && ! empty( $args['username'] ) ? str_replace( '@', '', $args['username'] ) : false;
		$hashtag          = isset( $args['hashtag'] ) && ! empty( $args['hashtag'] ) ? str_replace( '#', '', $args['hashtag'] ) : false;
		$blocked_users    = isset( $args['blocked_users'] ) && ! empty( $args['blocked_users'] ) ? $args['blocked_users'] : false;
		$attachment       = isset( $args['attachment'] ) ? true : false;
		$custom_url       = isset( $args['custom_url'] ) ? $args['custom_url'] : '';
		$refresh_hour     = isset( $args['refresh_hour'] ) ? absint( $args['refresh_hour'] ) : 5;
		$image_size       = isset( $args['image_size'] ) ? $args['image_size'] : 'standard';
		$image_link_rel   = isset( $args['image_link_rel'] ) ? $args['image_link_rel'] : '';
		$no_pin           = isset( $args['no_pin'] ) ? $args['no_pin'] : 0;
		$image_link_class = isset( $args['image_link_class'] ) ? $args['image_link_class'] : '';
		// $widget_id        = isset( $args['widget_id'] ) ? $args['widget_id'] : preg_replace( '/[^0-9]/', '', $this->id );

		if ( ! WIS_Feed::isMobile() ) {
			$template               = isset( $args['template'] ) ? $args['template'] : 'slider';
			$images_number          = isset( $args['images_number'] ) ? absint( $args['images_number'] ) : 20;
			$columns                = isset( $args['columns'] ) ? absint( $args['columns'] ) : 4;
			$controls               = isset( $args['controls'] ) ? $args['controls'] : 'prev_next';
			$animation              = isset( $args['animation'] ) ? $args['animation'] : 'slide';
			$slidespeed             = isset( $args['slidespeed'] ) ? $args['slidespeed'] : 7000;
			$description            = isset( $args['description'] ) ? $args['description'] : [];
			$caption_words          = isset( $args['caption_words'] ) ? $args['caption_words'] : 20;
			$enable_control_buttons = isset( $args['enable_control_buttons'] ) ? $args['enable_control_buttons'] : 0;
			$show_feed_header       = isset( $args['show_feed_header'] ) ? $args['show_feed_header'] : 0;
			$enable_stories         = isset( $args['enable_stories'] ) ? $args['enable_stories'] : 0;
			$keep_ratio             = isset( $args['keep_ratio'] ) ? $args['keep_ratio'] : 0;
			$slick_img_size         = isset( $args['slick_img_size'] ) ? absint( $args['slick_img_size'] ) : 300;
			$slick_slides_to_show   = isset( $args['slick_slides_to_show'] ) ? $args['slick_slides_to_show'] : 3;
			$slick_sliding_speed    = isset( $args['slick_sliding_speed'] ) ? $args['slick_sliding_speed'] : 5000;
			$slick_slides_padding   = isset( $args['slick_slides_padding'] ) ? $args['slick_slides_padding'] : 0;
			$gutter                 = isset( $args['gutter'] ) ? $args['gutter'] : 0;
			$masonry_image_width    = isset( $args['masonry_image_width'] ) ? $args['masonry_image_width'] : 200;
			$highlight_offset       = isset( $args['highlight_offset'] ) ? $args['highlight_offset'] : 1;
			$highlight_pattern      = isset( $args['highlight_pattern'] ) ? $args['highlight_pattern'] : 6;
			$enable_ad              = isset( $args['enable_ad'] ) ? $args['enable_ad'] : 0;
			$enable_icons           = isset( $args['enable_icons'] ) ? $args['enable_icons'] : 0;
			$orderby                = isset( $args['orderby'] ) ? $args['orderby'] : 'rand';
			$images_link            = isset( $args['images_link'] ) ? $args['images_link'] : 'image_url';
			$blocked_words          = isset( $args['blocked_words'] ) && ! empty( $args['blocked_words'] ) ? $args['blocked_words'] : false;
			$allowed_words          = isset( $args['allowed_words'] ) && ! empty( $args['allowed_words'] ) ? $args['allowed_words'] : false;
			$powered_by_link        = isset( $args['support_author'] ) ? true : false;
			$masonry_lite_cols      = isset( $args['masonry_lite_cols'] ) ? $args['masonry_lite_cols'] : 4;
			$masonry_lite_gap       = isset( $args['masonry_lite_gap'] ) ? $args['masonry_lite_gap'] : 10;
		} else {
			$template               = isset( $args['m_template'] ) ? $args['m_template'] : 'slider';
			$images_number          = isset( $args['m_images_number'] ) ? absint( $args['m_images_number'] ) : 20;
			$columns                = isset( $args['m_columns'] ) ? absint( $args['m_columns'] ) : 4;
			$controls               = isset( $args['m_controls'] ) ? $args['m_controls'] : 'prev_next';
			$animation              = isset( $args['m_animation'] ) ? $args['m_animation'] : 'slide';
			$slidespeed             = isset( $args['m_slidespeed'] ) ? $args['m_slidespeed'] : 7000;
			$description            = isset( $args['m_description'] ) ? $args['m_description'] : [];
			$shopifeed_phone        = isset( $args['m_shopifeed_phone'] ) ? $args['m_shopifeed_phone'] : null;
			$shopifeed_color        = isset( $args['m_shopifeed_color'] ) ? $args['m_shopifeed_color'] : '#DA004A';
			$shopifeed_columns      = isset( $args['m_shopifeed_columns'] ) ? $args['m_shopifeed_columns'] : 3;
			$caption_words          = isset( $args['m_caption_words'] ) ? $args['m_caption_words'] : 20;
			$enable_control_buttons = isset( $args['m_enable_control_buttons'] ) ? $args['m_enable_control_buttons'] : 0;
			$show_feed_header       = isset( $args['m_show_feed_header'] ) ? $args['m_show_feed_header'] : 0;
			$enable_stories         = isset( $args['m_enable_stories'] ) ? $args['m_enable_stories'] : 0;
			$keep_ratio             = isset( $args['m_keep_ratio'] ) ? $args['m_keep_ratio'] : 0;
			$slick_img_size         = isset( $args['m_slick_img_size'] ) ? absint( $args['m_slick_img_size'] ) : 300;
			$slick_slides_to_show   = isset( $args['m_slick_slides_to_show'] ) ? $args['m_slick_slides_to_show'] : 3;
			$slick_sliding_speed    = isset( $args['m_slick_sliding_speed'] ) ? $args['m_slick_sliding_speed'] : 5000;
			$slick_slides_padding   = isset( $args['m_slick_slides_padding'] ) ? $args['m_slick_slides_padding'] : 0;
			$gutter                 = isset( $args['m_gutter'] ) ? $args['m_gutter'] : 0;
			$masonry_image_width    = isset( $args['m_masonry_image_width'] ) ? $args['m_masonry_image_width'] : 200;
			$highlight_offset       = isset( $args['m_highlight_offset'] ) ? $args['m_highlight_offset'] : 1;
			$highlight_pattern      = isset( $args['m_highlight_pattern'] ) ? $args['m_highlight_pattern'] : 6;
			$enable_ad              = isset( $args['m_enable_ad'] ) ? $args['m_enable_ad'] : 0;
			$enable_icons           = isset( $args['m_enable_icons'] ) ? $args['m_enable_icons'] : 0;
			$orderby                = isset( $args['m_orderby'] ) ? $args['m_orderby'] : 'rand';
			$images_link            = isset( $args['m_images_link'] ) ? $args['m_images_link'] : 'image_url';
			$blocked_words          = isset( $args['m_blocked_words'] ) && ! empty( $args['m_blocked_words'] ) ? $args['m_blocked_words'] : false;
			$allowed_words          = isset( $args['m_allowed_words'] ) && ! empty( $args['m_allowed_words'] ) ? $args['m_allowed_words'] : false;
			$powered_by_link        = isset( $args['m_support_author'] ) ? true : false;
			$masonry_lite_cols      = isset( $args['m_masonry_lite_cols'] ) ? $args['m_masonry_lite_cols'] : 2;
			$masonry_lite_gap       = isset( $args['m_masonry_lite_gap'] ) ? $args['m_masonry_lite_gap'] : 5;
		}

		if ( ! empty( $description ) && ! is_array( $description ) ) {
			$description = explode( ',', $description );
		}

		if ( isset( $args['search_for'] ) && $args['search_for'] == 'hashtag' ) {
			$search                      = 'hashtag';
			$search_for['hashtag']       = $hashtag;
			$search_for['blocked_users'] = $blocked_users;
		} else if ( isset( $args['search_for'] ) && $args['search_for'] == 'account' ) {
			$search                = 'account';
			$show_feed_header      = false;
			$search_for['account'] = $account;
		} else if ( isset ( $args['search_for'] ) && $args['search_for'] == 'account_business' ) {
			$search                         = 'account_business';
			$search_for['account_business'] = $account_business;
			$search_for['blocked_words']    = $blocked_words;
		} else {
			$search                 = 'user';
			$search_for['username'] = $username;
		}

		if ( $refresh_hour == 0 ) {
			$refresh_hour = 5;
		}

		$main_div         = '';
		$keep_ratio_style = "";
		$plugin_version   = WIS_Plugin::app()->getPluginVersion();
		$prefix           = WIS_Plugin::app()->getPrefix();

		if ( $template == 'slick_slider' && $keep_ratio ) {
			$keep_ratio_style = sprintf( "style='height:%spx; width:%spx; margin-left: auto; margin-right: auto; overflow:hidden;'", $slick_img_size + 50, $slick_img_size * $slick_slides_to_show );
		}

		if ( $template == 'slick_slider' ) {
			wp_enqueue_script( $prefix . '_slick-js', WIG_COMPONENT_URL . '/assets/js/slick.js', array( 'jquery' ), $plugin_version, true );
			wp_enqueue_style( $prefix . '_slick-css', WIG_COMPONENT_URL . '/assets/css/slick.css' );
			wp_enqueue_style( $prefix . '_slick-theme-css', WIG_COMPONENT_URL . '/assets/css/slick-theme.css' );
			wp_enqueue_style( $prefix . '_slick-view-css', WIG_COMPONENT_URL . '/assets/css/slick_view.css' );

			wp_enqueue_script( 'wis-slick-view', WIG_COMPONENT_URL . '/assets/js/slick_view.js', array( 'jquery' ), $plugin_version, true );
			$slick_settings = json_encode( [
				'slick_slides_to_show' => $slick_slides_to_show,
				'slick_sliding_speed'  => $slick_sliding_speed,
				'enable_controls'      => $enable_control_buttons,
			] );
			wp_add_inline_script( 'wis-slick-view', "var slick_settings = $slick_settings;" );
			$main_div = "<div class='slick_slider_container'>";
		} else if ( $template == 'masonry' ) {
			wp_enqueue_script( 'masonry' );
			wp_enqueue_script( 'wis-premium-imagesloaded', WIG_COMPONENT_URL . '/assets/js/libs/imagesloaded.pkgd.min.js', [], $plugin_version, true );
			wp_enqueue_script( 'wis-masonry-view', WIG_COMPONENT_URL . '/assets/js/masonry_view.js', array( 'jquery' ), $plugin_version );
			wp_add_inline_script( 'wis-masonry-view', "var gutter = $gutter;" );
			wp_enqueue_style( 'wis-masonry-view-css', WIG_COMPONENT_URL . '/assets/css/masonry-view.css' );
			wp_enqueue_style( 'wis-show-more-button-css', WIG_COMPONENT_URL . '/assets/css/show_more_button.css' );
			wp_enqueue_script( 'wis-show-more-button-js', WIG_COMPONENT_URL . '/assets/js/show_more_button.js', array( 'jquery' ), $plugin_version, true );

			$ajaxurl = json_encode( [
				'nonce' => wp_create_nonce( 'show-more-btn-nonce' ),
				'url'   => admin_url( 'admin-ajax.php' ),
			] );
			wp_add_inline_script( 'wis-show-more-button-js', "var ajaxurl = $ajaxurl;" );

			$main_div = "<div class='masonry_container'>";
		} else if ( $template == 'masonry_lite' ) {
			wp_enqueue_style( 'wis-masonry-view-css', WIG_COMPONENT_URL . '/assets/css/masonry-lite-view.css' );
			$ajaxurl = json_encode( [
				'masonry_lite_cols' => $masonry_lite_cols,
				'masonry_lite_gap'  => $masonry_lite_gap,
			] );

			wp_add_inline_script( 'wis-masonry-lite-cols', "var ajaxurl = $ajaxurl;" );

			$main_div = "<div class='masonry_lite_container'>";

		} else if ( $template == 'highlight' ) {
			wp_enqueue_script( $prefix . 'packery-js', WIG_COMPONENT_URL . '/assets/js/packery.js', array(), $plugin_version, true );

			$container_class = WIS_Plugin::app()->getPluginName() . "-highlight-container";
			$main_div        = "<div class=$container_class id='highlight_container_id'>";
			$script_handle   = WIS_Plugin::app()->getPluginName() . '-packery-view';
			$params          = json_encode( [
				'container' => $container_class,
			] );
			wp_enqueue_script( $script_handle, WIG_COMPONENT_URL . '/assets/js/highlight_view.js', array( 'jquery' ), $plugin_version, true );
			wp_add_inline_script( $script_handle, "var params = $params;" );

			wp_enqueue_style( 'wis-show-more-button-css', WIG_COMPONENT_URL . '/assets/css/show_more_button.css' );
			wp_enqueue_script( 'wis-show-more-button-js', WIG_COMPONENT_URL . '/assets/js/show_more_button.js', array( 'jquery' ), $plugin_version, true );

			$ajaxurl = json_encode( [
				'nonce' => wp_create_nonce( 'show-more-btn-nonce' ),
				'url'   => admin_url( 'admin-ajax.php' ),
			] );
			wp_add_inline_script( 'wis-show-more-button-js', "var ajaxurl = $ajaxurl;" );
		} else if ( $template == 'showcase' ) {
			// не делать ничего, т.к. в методе рендера для showcase подключаются скрипты и стили
		}

		if ( $images_link == 'popup' ) {
			if ( $template != 'slick_slider' && $template != 'showcase' ) {
				wp_enqueue_script( $prefix . '_slick-js', WIG_COMPONENT_URL . '/assets/js/slick.js', array( 'jquery' ), $plugin_version, true );
				wp_enqueue_style( $prefix . '_slick-css', WIG_COMPONENT_URL . '/assets/css/slick.css' );
				wp_enqueue_style( $prefix . '_slick-theme-css', WIG_COMPONENT_URL . '/assets/css/slick-theme.css' );
				wp_enqueue_style( $prefix . '_slick-view-css', WIG_COMPONENT_URL . '/assets/css/slick_view.css' );
			}
			wp_enqueue_script( $prefix . 'remodal-js', WIG_COMPONENT_URL . '/assets/js/remodal.js', array( 'jquery' ), $plugin_version, true );
			wp_enqueue_script( $prefix . 'remodal-view-js', WIG_COMPONENT_URL . '/assets/js/remodal-view.js', array('jquery' ), $plugin_version, true );

			$remodal_data = json_encode( [
				'icons_url' => WIG_COMPONENT_URL . '/assets/icons/',
			] );
			wp_add_inline_script( $prefix . 'remodal-view-js', "var remodal_data = $remodal_data;" );

			wp_enqueue_style( $prefix . 'remodal-css', WIG_COMPONENT_URL . '/assets/css/remodal.css' );
			wp_enqueue_style( $prefix . 'remodal-theme-css', WIG_COMPONENT_URL . '/assets/css/remodal-default-theme.css' );
			wp_enqueue_style( $prefix . 'remodal-view-css', WIG_COMPONENT_URL . '/assets/css/remodal-view.css' );
		}

		$output = __( 'No images found! <br> Try some other hashtag or username', 'instagram-slider-widget' );

		if ( ( $search == 'user' && $attachment ) ) {

			if ( ! wp_next_scheduled( 'jr_insta_cron', [ $search_for['username'], $refresh_hour, $images_number ] ) ) {
				wp_schedule_single_event( time(), 'jr_insta_cron', [
					$search_for['username'],
					$refresh_hour,
					$images_number,
				] );
			}

			$opt_name       = 'jr_insta_' . md5( $search . '_' . $search_for['username'] );
			$attachment_ids = (array) get_option( $opt_name );

			$query_args = [
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => $images_number,
				'no_found_rows'  => true,
			];

			if ( $orderby != 'rand' ) {

				$orderby  = explode( '-', $orderby );
				$meta_key = $orderby[0] == 'date' ? 'jr_insta_timestamp' : 'jr_insta_popularity';

				$query_args['meta_key'] = $meta_key;
				$query_args['orderby']  = 'meta_value_num';
				$query_args['order']    = $orderby[1];
			}

			if ( isset( $attachment_ids['saved_images'] ) && ! empty( $attachment_ids['saved_images'] ) ) {

				$query_args['post__in'] = $attachment_ids['saved_images'];
			} else {

				$query_args['meta_query'] = [
					[
						'key'     => 'jr_insta_username',
						'value'   => $username,
						'compare' => '=',
					],
				];
			}

			$images_data = $wisw->feed_query( $search_for, $refresh_hour, $images_number );

			if ( isset( $images_data['error'] ) ) {
				return $images_data['error'];
			}

			if ( is_array( $images_data ) && ! empty( $images_data ) ) {
				if ( isset( $images_data['error'] ) ) {
					return $images_data['error'];
				}

				$images_data = $this->update_image_data( $orderby, $images_data, true );

				$output = $main_div;
				if ( $template == 'showcase' ) {
					return $this->render_showcase_templates( $images_data, $output, $args );
				}

				if ( $template == 'masonry_lite' ) {
					echo '<style>.masonry_lite_container{column-count:' . $masonry_lite_cols . ';' . 'column-gap:' . $masonry_lite_gap . 'px' . ';}</style>';
				}

				foreach ( $images_data as $image_data ) {
					$template_args = $image_data;

					if ( 'image_link' == $images_link ) {
						$template_args['link_to'] = $image_data['link'];
					} else if ( 'user_url' == $images_link ) {
						$template_args['link_to'] = 'https://www.instagram.com/' . $username . '/';
					} else if ( 'image_url' == $images_link ) {
						$template_args['link_to'] = $image_data['url'];
					} else if ( 'custom_url' == $images_link ) {
						$template_args['link_to'] = $custom_url;
					} else if ( 'none' == $images_link ) {
						$template_args['link_to'] = 'none';
					}

					$template_args['type']         = $image_data['type'];
					$template_args['caption']      = wp_trim_words( $image_data['caption'], $caption_words );
					$template_args['full_caption'] = isset( $image_data['caption'] ) ? $image_data['caption'] : '';
					$template_args['timestamp']    = isset( $image_data['timestamp'] ) ? $image_data['timestamp'] : '';
					$template_args['username']     = isset( $image_data['username'] ) ? $image_data['username'] : '';
					$template_args['attachment']   = false;

					if ( 'GraphVideo' === $image_data['type'] ) {
						$template_args['image'] = isset( $image_data['image'] ) ? $image_data['image'] : '';
					} else {
						$template_args['image'] = isset( $image_data['sizes'][ $image_size ] ) ? $image_data['sizes'][ $image_size ] : '';
					}

					$output .= $this->get_template( $template, $template_args );
				}

				$output .= "</div>";
			}

			wp_reset_postdata();
		} else {
			$is_business = ( $search == 'account_business' );
			if ( $is_business ) {
				$accounts = WIS_Plugin::app()->getOption( 'account_profiles_new' );
			} else {
				$accounts = WIS_Plugin::app()->getOption( 'account_profiles' );
			}

			$images_data = $wisw->feed_query( $search_for, $refresh_hour, $images_number );

			if ( isset( $images_data['error'] ) ) {
				return $images_data['error'];
			}

			$stories = [];
			if ( isset( $images_data['stories'] ) ) {
				$stories = $images_data['stories'];
				unset( $images_data['stories'] );
			}
			$next_max_id = isset( $images_data['next_max_id'] ) ? $images_data['next_max_id'] : null;
			unset($images_data['next_max_id']);

			if ( is_array( $images_data ) && ! empty( $images_data ) ) {
				if ( isset( $images_data['error'] ) ) {
					return $images_data['error'];
				}

				$images_data = $this->update_image_data( $orderby, $images_data, $is_business );

				$output = '';

				if ( $template == 'showcase' ) {
					return $this->render_showcase_templates( $images_data, $output, $args );
				}

				$account = null;
				if ( $accounts ) {
					$account = $accounts[ $images_data[0]['username'] ];
				}

				if ( $account ) {
					$account_data = $account;
				} else if ( $search != 'hashtag' ) {
					$data         = WIS_Plugin::app()->getOption( 'profiles_data_by_username' );
					$data         = $data['entry_data']['ProfilePage']['0']['graphql']['user'];
					$account_data = [
						'username'        => $data['username'],
						'profile_picture' => $data['profile_pic_url'],
						'counts'          => [
							'media'       => $data['edge_owner_to_timeline_media']['count'],
							'followed_by' => $data['edge_followed_by']['count'],
						],
					];
				}
				if ( $show_feed_header && $search == 'account_business' ) {
					$output .= $this->display_header_with_stories( $account, $account_data, $stories, $enable_stories );
				}
				$output .= $main_div;

				if ( $template == 'masonry_lite' ) {
					echo '<style>.masonry_lite_container{column-count:' . $masonry_lite_cols . ';' . 'column-gap:' . $masonry_lite_gap . 'px' . ';}</style>';
				}

				$current_pic_number = 0 - $highlight_offset;

				foreach ( $images_data as $image_data ) {

					if ( 'image_link' == $images_link ) {
						$template_args['link_to'] = $image_data['link'];
					} else if ( 'user_url' == $images_link ) {
						$template_args['link_to'] = 'https://www.instagram.com/' . $username . '/';
					} else if ( 'image_url' == $images_link ) {
						$template_args['link_to'] = $image_data['url'];
					} else if ( 'custom_url' == $images_link ) {
						$template_args['link_to'] = $custom_url;
					} else if ( 'popup' == $images_link ) {
						$template_args['link_to'] = 'popup';
					} else if ( 'none' == $images_link ) {
						$template_args['link_to'] = 'none';
					}


					$template_args['type']                   = $image_data['type'];
					$template_args['id']                     = $image_data['id'];
					$template_args['image']                  = $image_data['image'];
					$template_args['caption']                = wp_trim_words( isset( $image_data['caption'] ) ? $image_data['caption'] : "", $caption_words );
					$template_args['likes']                  = isset( $image_data['likes'] ) ? $image_data['likes'] : '';
					$template_args['comments']               = isset( $image_data['comments'] ) ? $image_data['comments'] : '';
					$template_args['full_caption']           = $image_data['caption'];
					$template_args['timestamp']              = isset( $image_data['timestamp'] ) ? $image_data['timestamp'] : null;
					$template_args['username']               = isset( $image_data['username'] ) ? $image_data['username'] : '';
					$template_args['slick_slides_padding']   = $slick_slides_padding;
					$template_args['enable_control_buttons'] = $enable_control_buttons;
					$template_args['keep_ratio']             = $keep_ratio;
					$template_args['slick_img_size']         = $slick_img_size;
					$template_args['enable_ad']              = $enable_ad;
					$template_args['enable_icons']           = $enable_icons;
					$template_args['masonry_image_width']    = $masonry_image_width;
					$template_args['masonry_lite_cols']      = $masonry_lite_cols;
					$template_args['masonry_lite_gap']       = $masonry_lite_gap;
					$template_args['width']                  = $image_data['width'];
					$template_args['height']                 = $image_data['height'];
					$template_args['sidecar_media']          = isset( $image_data['sidecar_media'] ) ? $image_data['sidecar_media'] : [];
					$template_args['video']                  = isset( $image_data['video'] ) ? $image_data['video'] : '';
					$template_args['post_link']              = $image_data['link'];
					$template_args['profile_img_link']       = $search == 'account_business' ? $account_data['profile_picture_url'] : '';

					if ( $current_pic_number + 1 == 0 ) {
						$template_args['is_big_pic'] = true;
						$current_pic_number          = 0 - $highlight_pattern;
					} else {
						$template_args['is_big_pic'] = false;
					}
					$current_pic_number ++;
					$output .= $this->get_template( $template, $template_args );
				}

				$output .= "</div>";
				if ( $enable_ad ) {
					$output .= '
                <div class="wis-template-ad" style="font-size: 1.3rem !important; margin-top: 2%; text-align: center; color: rgba(22,22,22,0.72) !important;" >
                    <a target="_blank" style="color: rgba(22,22,22,0.72) !important; text-decoration: none" href="https://cm-wp.com/instagram-slider-widget/" ><h3 style="font-size: 1.3rem !important;"> Powered by Social Slider Feed </h3 ></a >
                </div >
                ';
				}

				if(($template == 'masonry' || $template == 'highlight') && $next_max_id != null)
				$output .= "<div class='show-more-button'>
								<a  class='wis-show-more' data-image-size='{$image_size}' data-masonry-width='{$masonry_image_width}' data-caption-words='{$caption_words}' data-linkto='{$template_args['link_to']}' data-img-cnt='{$images_number}' data-template='{$template}' data-username='{$template_args['username']}' data-next-page='{$next_max_id}' data-masonry_cols='{$masonry_lite_cols}' data-masonry_gap='{$masonry_lite_gap}'>" . __( 'Show More', 'instagram-slider-widget' ) . "</a>
							</div>";
			}
		}

		return "<div class='wis-instagram-widget' {$keep_ratio_style}>{$output}</div>";
	}

	public function display_header_with_stories( $account, $account_data, $stories, $enable_stories ) {
		$plugin_version = WIS_Plugin::app()->getPluginVersion();
		$prefix         = WIS_Plugin::app()->getPrefix();

		wp_enqueue_style( $prefix . '_stories-css', WIG_COMPONENT_URL . '/assets/css/socialstory.css' );
		wp_enqueue_script( $prefix . '_stories-js', WIG_COMPONENT_URL . '/assets/js/socialstory.js', array(), $plugin_version, true );
		wp_enqueue_script( $prefix . '_stories-view-js', WIG_COMPONENT_URL . '/assets/js/socialstory-view.js', array( $prefix . '_stories-js', 'jqeury' ), $plugin_version, true );


		$stories_data = json_encode( [
			'account' => $account,
			'stories' => $stories,
		] );
		wp_add_inline_script( WIS_Plugin::app()->getPrefix() . '_stories-view-js', "var stories_data = $stories_data;" );
		$account_data['has_stories']    = ! empty( $stories );
		$account_data['enable_stories'] = $enable_stories;

		return $this->render_template( 'templates/feed_header_template', array(
			'account_data' => $account_data,
		) );
	}

	/**
	 * Function to display Templates styles
	 *
	 * @param string $template
	 * @param array $args
	 *
	 * return mixed
	 *
	 * @return string
	 */
	public function get_template( $template, $args ) {
		$args = [
			'username'               => $args['username'],
			'link_to'                => isset( $args['link_to'] ) ? $args['link_to'] : false,
			'id'                     => isset( $args['id'] ) ? $args['id'] : false,
			'image_url'              => isset( $args['image'] ) ? $args['image'] : false,
			'type'                   => isset( $args['type'] ) ? $args['type'] : '',
			'likes'                  => isset( $args['likes'] ) ? $args['likes'] : '',
			'comments'               => isset( $args['comments'] ) ? $args['comments'] : '',
			'width'                  => isset( $args['width'] ) ? $args['width'] : '',
			'height'                 => isset( $args['height'] ) ? $args['height'] : '',
			'timestamp'              => isset( $args['timestamp'] ) ? $args['timestamp'] : '',
			'caption'                => isset( $args['caption'] ) ? $args['caption'] : '',
			'full_caption'           => isset( $args['full_caption'] ) ? $args['full_caption'] : '',
			'shopifeed_phone'        => isset( $args['m_shopifeed_phone'] ) ? $args['m_shopifeed_phone'] : null,
			'shopifeed_color'        => isset( $args['m_shopifeed_color'] ) ? $args['m_shopifeed_color'] : '#DA004A',
			'shopifeed_columns'      => isset( $args['m_shopifeed_columns'] ) ? $args['m_shopifeed_columns'] : 3,
			'padding'                => isset( $args['slick_slides_padding'] ) ? $args['slick_slides_padding'] : 0,
			'enable_control_buttons' => isset( $args['enable_control_buttons'] ) ? $args['enable_control_buttons'] : 0,
			'keep_ratio'             => isset( $args['keep_ratio'] ) ? $args['keep_ratio'] : 0,
			'slick_img_size'         => isset( $args['slick_img_size'] ) ? $args['slick_img_size'] : 300,
			'enable_ad'              => isset( $args['enable_ad'] ) ? $args['enable_ad'] : 0,
			'enable_icons'           => isset( $args['enable_icons'] ) ? $args['enable_icons'] : 0,
			'masonry_image_width'    => isset( $args['masonry_image_width'] ) ? $args['masonry_image_width'] : 200,
			'masonry_lite_cols'      => isset( $args['masonry_lite_cols'] ) ? $args['masonry_lite_cols'] : 4,
			'masonry_lite_gap'       => isset( $args['masonry_lite_gap'] ) ? $args['masonry_lite_gap'] : 10,
			'is_big_pic'             => isset( $args['is_big_pic'] ) ? $args['is_big_pic'] : false,
			'video'                  => isset( $args['video'] ) ? $args['video'] : '',
			'sidecar_media'          => isset( $args['sidecar_media'] ) ? $args['sidecar_media'] : [],
			'post_link'              => isset( $args['post_link'] ) ? $args['post_link'] : $args['link_to'],
			'profile_img_link'       => isset( $args['profile_img_link'] ) ? $args['profile_img_link'] : '',
		];


		$output = '';
		if ( $template == 'slick_slider' ) {
			$output .= $this->render_template( 'templates/slick', $args );;
		} else if ( $template == 'masonry' ) {
			$output .= $this->render_template( 'templates/masonry', $args );;
		} else if ( $template == 'highlight' ) {
			$output .= $this->render_template( 'templates/highlight', $args );
		} else if ( $template == 'showcase' ) {
			$output .= $this->render_template( 'templates/showcase', $args );
		} else if ( $template == 'masonry_lite' ) {
			$output .= $this->render_template( 'templates/masonry_light', $args );
		} else {
			$output .= __( 'Template not found', 'instagram-slider-widget' );
		}

		return $output;
	}

	/**
	 * Displays the showcase template.
	 *
	 * @param array $images_data Instagram post data
	 *
	 * @param array $args Arguments from display settings
	 *
	 * @return false|string
	 */
	public function render_showcase_template( $images_data, $args ) {
		$plugin_version = WIS_Plugin::app()->getPluginVersion();
		$prefix         = WIS_Plugin::app()->getPrefix();

		wp_enqueue_script( $prefix . '-cookie-js', WIG_COMPONENT_URL . '/assets/js/cookie.min.js', array(), $plugin_version, true );
		wp_enqueue_style( $prefix . '-showcase-css', WIG_COMPONENT_URL . '/assets/css/showcase.css' );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( $prefix . '-showcase-js', WIG_COMPONENT_URL . '/assets/js/showcase.js', array( 'jquery' ), $plugin_version, true );

		return $this->render_template( 'templates/showcase', array(
			'posts' => $images_data,
			'args'  => $args,
		) );
	}

	public function render_showcase_remodal_template( $images_data, $args ) {
		$plugin_version = WIS_Plugin::app()->getPluginVersion();
		$prefix         = WIS_Plugin::app()->getPrefix();

		wp_enqueue_script( $prefix . '_slick-js', WIG_COMPONENT_URL . '/assets/js/slick.js', array( 'jquery' ), $plugin_version, true );
		wp_enqueue_script( $prefix . '-remodal-shopifeed-js', WIG_COMPONENT_URL . '/assets/js/remodal.js', array( 'jquery' ), $plugin_version, true );
		wp_enqueue_script( $prefix . '-remodal-view-shopifeed-js', WIG_COMPONENT_URL . '/assets/js/remodal-view.js', array(), $plugin_version, true );
		$remodal_data = json_encode( [
			'icons_url' => WIG_COMPONENT_URL . '/assets/icons/',
		] );
		wp_add_inline_script( $prefix . '-remodal-view-shopifeed-js', "var remodal_data = $remodal_data;" );

		wp_enqueue_style( $prefix . '-remodal-shopifeed-css', WIG_COMPONENT_URL . '/assets/css/remodal.css' );
		wp_enqueue_style( $prefix . '-remodal-shopifeed-theme-css', WIG_COMPONENT_URL . '/assets/css/remodal-default-theme.css' );

		wp_enqueue_style( $prefix . '_slick-css', WIG_COMPONENT_URL . '/assets/css/slick.css' );
		wp_enqueue_style( $prefix . '_slick-theme-css', WIG_COMPONENT_URL . '/assets/css/slick-theme.css' );
		wp_enqueue_style( $prefix . '_slick-view-css', WIG_COMPONENT_URL . '/assets/css/slick_view.css' );

		wp_enqueue_style( $prefix . '-remodal-shopifeed-template-css', WIG_COMPONENT_URL . '/assets/css/remodal-template1.css' );

		return $this->render_template( 'templates/showcase_remodal1', array(
			'posts' => $images_data,
			'args'  => $args,
		) );
	}

	/**
	 * Method renders layout template
	 *
	 * @param string $template_name Template name.
	 *
	 * @param array $args Template arguments.
	 *
	 * @return false|string
	 */
	private function render_template( $name, $args ) {
		ob_start();
		if ( strpos( $name, DIRECTORY_SEPARATOR ) !== false && ( is_file( $name ) || is_file( $name . '.php' ) ) ) {
			if ( is_file( $name ) ) {
				$path = $name;
			} else {
				$path = $name . '.php';
			}
		} else {
			$path = WIG_COMPONENT_VIEWS_DIR . "/$name.php";
		}
		if ( ! is_file( $path ) ) {
			ob_end_clean();

			return __( 'Template not found', 'instagram-slider-widget' );
		}

		extract( $args );
		include $path;

		return ob_get_clean();
	}

	public function wis_get_more() {
		$result = '';
		$data   = [];

		$post = $_POST;
		// if _nonce doesn't match, kill the script
		if ( isset( $post['_nonce'] ) && ! wp_verify_nonce( $post['_nonce'], 'show-more-btn-nonce' ) ) {
			wp_die();
		} 

		$template                 = $post['template'];
		$next_page                = $post['next_page'];
		$username                 = $post['username'];
		$images_count             = $post['images_count'];
		$template_args['link_to'] = $post['link_to'];
		$caption_words            = $post['caption_words'];
		$masonry_image_width      = $post['masonry_image_width'];
		$image_size               = $post['image_size'];
		$masonry_lite_cols        = $post['masonry_lite_cols'];
		$masonry_lite_gap         = $post['masonry_lite_gap'];

		$url = WIG_USERS_SELF_MEDIA_URL;

		$accounts_data = WIS_Plugin::app()->getOption( 'account_profiles' );
		$token         = $accounts_data[ $username ]['token'];

		$args          = [
			'body' => [
				'access_token' => $token,
				'count'        => $images_count,
				'max_id'       => $next_page,
			],
		];
		$response      = wp_remote_get( $url, $args );
		$response      = json_decode( $response['body'], true );
		$next_max_id   = $response['pagination']['next_max_id'];
		$response_data = $response['data'];

		foreach ( $response_data as $value ) {
			array_push( $data, WIG_Widget::app()->to_media_model_from_account( $value ) );
		}

		$user_data    = WIS_Plugin::app()->getOption( 'profiles_data_by_username' );
		$user_data    = $user_data['entry_data']['ProfilePage']['0']['graphql']['user'];
		$account_data = [
			'username'        => $user_data['username'],
			'profile_picture' => $user_data['profile_pic_url'],
			'counts'          => [
				'media'       => $user_data['edge_owner_to_timeline_media']['count'],
				'followed_by' => $user_data['edge_followed_by']['count'],
			],
		];

		foreach ( $data as $image_data ) {
			$template_args['type']                = $image_data['type'];
			$template_args['id']                  = $image_data['id'];
			$template_args['image']               = $image_data['sizes'][ $image_size ];
			$template_args['likes']               = $image_data['likes_count'];
			$template_args['comments']            = $image_data['comment_count'];
			$template_args['caption']             = wp_trim_words( $image_data['caption'], $caption_words );
			$template_args['timestamp']           = $image_data['timestamp'];
			$template_args['username']            = isset( $image_data['username'] ) ? $image_data['username'] : '';
			$template_args['masonry_image_width'] = $masonry_image_width;
			$template_args['sidecar_media']       = $image_data['sidecar_media'];
			$template_args['video']               = $image_data['video'];
			$template_args['post_link']           = $image_data['link'];
			$template_args['profile_img_link']    = $account_data['profile_picture'];
			$template_args['masonry_lite_cols']   = $masonry_lite_cols;
			$template_args['masonry_lite_gap']    = $masonry_lite_gap;
			$result                               .= $this->get_template( $template, $template_args );
		}

		$result = [
			'next_max_id' => $next_max_id,
			'result'      => $result,
		];

		wp_send_json_success( $result );
	}

	/**
	 * Update image data.
	 *
	 * @param array|string $orderby Order render image data.
	 * @param array        $images_data   Images data.
	 * @param bool         $is_business   Is business account.
	 *
	 * @return array
	 */
	private function update_image_data( $orderby, $images_data, $is_business = false ) {

		if ( $orderby === 'rand' ) {
			shuffle( $images_data );

			return $images_data;
		}

		if ( is_string( $orderby ) ) {
			$orderby = explode( '-', $orderby );
		}

		if ( $orderby[0] == 'date' ) {
			$func = 'sort_timestamp_' . $orderby[1];
		} else {
			$func = $is_business ? 'sort_popularity_' . $orderby[1] : 'sort_timestamp_' . $orderby[1];
		}

		usort( $images_data, [ $this, $func ] );

		return $images_data;
	}

	/**
	 * Render showcase templates.
	 *
	 * @param array  $images_data Images data.
	 * @param string $output Render output.
	 * @param array  $arg Argument.
	 *
	 * @return string
	 */
	private function render_showcase_templates( $images_data, $output, $args  ) {
		$output .= $this->render_showcase_template( $images_data, $args );
		$output .= $this->render_showcase_remodal_template( $images_data, $args );

		return $output;
	}
}
