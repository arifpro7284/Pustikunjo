<?php
/**
 * Porto Dynamic Tags Content class
 *
 * @author     P-Themes
 * @since      2.3.0
 */

defined( 'ABSPATH' ) || die;

if ( ! class_exists( 'Porto_Func_Dynamic_Tags_Content' ) ) :

	class Porto_Func_Dynamic_Tags_Content {

		/**
		 * Global Instance Objects
		 *
		 * @var array $instances
		 * @since 2.3.0
		 * @access private
		 */
		private static $instance = null;

		private $is_term_archive;

		public $post_id;

		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Current Post for Elementor & Wpb
		 *
		 * @since 2.3.0
		 */
		protected $post;

		/**
		 * Type of Dynamic WPb Tags
		 *
		 * @since 2.3.0
		 */
		public $features = array( 'field', 'link', 'image' );

		/**
		 * Meta Box Types
		 *
		 * @since 2.3.0
		 */
		protected $metabox_types = array(
			'text'           => array( 'field', 'link' ),
			'input'          => array( 'field' ),
			'editor'         => array( 'field' ),
			'textarea'       => array( 'field', 'link' ),
			'number'         => array( 'field' ),
			'range'          => array( 'field' ),
			'date'           => array( 'field' ),
			'email'          => array( 'field' ),
			'url'            => array( 'link' ),
			'image'          => array( 'image' ),
			'image_advanced' => array( 'image' ),
			'video'          => array( 'image' ),
			'file_advanced'  => array( 'link' ),
			'link'           => array( 'link' ),
			'page_link'      => array( 'link' ),
			'post_object'    => array( 'field' ),
			'taxonomy'       => array( 'field' ),
			'attach'         => array( 'image' ),
			'upload'         => array( 'image' ),
		);

		/**
		 * Meta Box Terms
		 *
		 * @since 2.3.0
		 */
		protected $meta_terms = array(
			'product'   => 'product_cat',
			'portfolio' => 'portfolio_cat',
			'member'    => 'member_cat',
			'post'      => 'category',
		);
		/**
		 * Constructor
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'init' ) );

			add_filter( 'porto_dynamic_tags_content', array( $this, 'get_dynamic_content' ), 10, 4 );

			add_action( 'wp_ajax_porto_dynamic_tags_get_value', array( $this, 'get_value' ) );
			add_action( 'wp_ajax_porto_dynamic_tags_acf_fields', array( $this, 'get_acf_fields' ) );
			add_action( 'wp_ajax_porto_dynamic_tags_toolset_fields', array( $this, 'get_toolset_fields' ) );

			add_filter( 'porto_builder_get_current_object', array( $this, 'get_dynamic_content_data' ), 10, 2 );

			// Elementor & Wpb
			$this->metabox_types = apply_filters( 'porto_dynamic_meta_types', $this->metabox_types );
			add_action( 'porto_dynamic_before_render', array( $this, 'before_render' ), 10, 2 );
			add_action( 'porto_dynamic_after_render', array( $this, 'after_render' ), 10, 2 );
		}

		/**
		 * Init functions
		 *
		 * @since 2.3.0
		 */
		public function init() {
			if ( class_exists( 'ACF' ) ) {
				$screen = get_current_screen();
				if ( $screen && 'post' == $screen->base ) {
					// add ACF fields
					include_once PORTO_SHORTCODES_LIB . 'dynamic_tags/class-porto-func-acf.php';
				}
			}
			if ( defined( 'TYPES_VERSION' ) ) { // Toolset Plugin
				require_once PORTO_SHORTCODES_LIB . 'dynamic_tags/class-porto-func-toolset.php';
			}
		}

		/**
		 * Retrieve dynamic tags content according to its type
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_content( $default = false, $object = null, $type = 'post', $field = '' ) {
			if ( ! $object ) {
				if ( 'post' == $type ) {
					global $post;
					$object = $post;
				} else {
					if ( ( $current_object = get_queried_object() ) && isset( $current_object->term_id ) ) {
						$object = $current_object;
					} else {
						global $post;
						$object = $post;
					}
				}
			}
			if ( ! $object ) {
				return $default;
			}
			if ( 'post' == $type ) {
				if ( 'content' == $field ) {
					return do_shortcode( $object->post_content );
				} elseif ( 'like_count' == $field ) {
					$count_res = get_post_meta( $object->ID, 'like_count', true );
					if ( empty( $count_res ) ) {
						$count_res = '0';
					}
					return esc_html( $count_res );
				} elseif ( $field && isset( $object->{ 'post_' . $field } ) ) {
					return $object->{ 'post_' . $field };
				} elseif ( 'thumbnail' == $field ) {
					return esc_url( get_the_post_thumbnail_url( $object, 'full' ) );
				} elseif ( 'author_img' == $field ) {
					return esc_url( get_avatar_url( get_the_author_meta( 'email' ) ) );
				} elseif ( 'permalink' == $field ) {
					return esc_url( get_permalink( $object ) );
				} elseif ( 'author_posts_url' == $field ) {
					global $authordata;
					if ( is_object( $authordata ) ) {
						return esc_url( get_author_posts_url( $authordata->ID, $authordata->user_nicename ) );
					}
				} else {
					return (int) $object->ID;
				}
			} elseif ( 'metabox' == $type ) {
				if ( ! $field ) {
					$field = 'page_sub_title';
				}
				$result = '';
				if ( $object->ID ) {
					$result = get_post_meta( $object->ID, $field, true );
				} else {
					$result = get_term_meta( $object->term_id, $field, true );
					if ( ! $result ) {
						$result = get_metadata( $object->taxonomy, $object->term_id, $field, true );	
					}
				}
				if ( empty( $result ) && 'like_count' == $field ) {
					$result = '0';
				}
				return $result;
			} elseif ( 'acf' == $type && $field ) {
				$field_arr = explode( '-', $field );
				if ( 2 === count( $field_arr ) ) {
					$field_res = $dynamic_field_type = '';
					if ( function_exists( 'acf_get_field' ) ) {
						$type_acf = acf_get_field( $field_arr[1] );
						if ( $type_acf && isset( $type_acf['type'] ) ) {
							$dynamic_field_type = $type_acf['type'];
						}
					}
					if ( isset( $object->term_id ) ) {
						$field_res = get_term_meta( $object->term_id, $field_arr[1], true );
					} else {
						$field_res = get_post_meta( $object->ID, $field_arr[1], true );
					}
					if ( 'image' == $dynamic_field_type ) {
						$field_res = wp_get_attachment_image_src( $field_res, 'full' );
						if ( ! empty( $field_res ) && is_array( $field_res ) ) {
							$field_res = $field_res[0];
						}
					}
					
					return $field_res;
				}
			} elseif ( 'toolset' == $type && $field ) {
				$field_arr = explode( ':', $field );
				if ( 2 === count( $field_arr ) ) {
					if ( isset( $object->term_id ) ) {
						return get_term_meta( $object->term_id, 'wpcf-' . $field_arr[1], true );
					}
					return get_post_meta( $object->ID, 'wpcf-' . $field_arr[1], true );
				}
			} elseif ( 'meta' == $type ) {
				$result = '';
				if ( $object->ID ) {
					$result = get_post_meta( $object->ID, $field, true );
				} else {
					$result = get_term_meta( $object->term_id, $field, true );
					if ( ! $result ) {
						$result = get_metadata( $object->taxonomy, $object->term_id, $field, true );	
					}
				}
				if ( isset( $_POST['is_img'] ) && '1' === $_POST['is_img'] && is_numeric( $result ) ) {
					$result = wp_get_attachment_image_src( $result, 'full' );
					if ( is_array( $result ) ) {
						$result = $result[0];
					}
				}
				return $result;
			} elseif ( 'tax' == $type ) {
				if ( $object->term_id ) {
					if ( 'id' == $field ) {
						return (int) $object->term_id;
					} elseif ( 'title' == $field ) {
						return esc_html( $object->name );
					} elseif ( 'desc' == $field ) {
						return $object->description;
					} elseif ( 'count' == $field ) {
						return (int) $object->count;
					} elseif ( 'term_link' == $field ) {
						return esc_url( get_term_link( $object ) );
					}
				}
			} elseif( 'woo' == $type && class_exists('WooCommerce') ) {
				if ( 'wc_brand_image' == $field && function_exists( 'wc_get_brand_thumbnail_url' ) ) {
					$brands = wp_get_post_terms( $object->ID, 'product_brand', array( 'fields' => 'ids' ) );

					// Bail early if we don't have any brands registered.
					if ( 0 === count( $brands ) ) {
						return $res;
					}
					$brand     = $brands[0];
					$result = wc_get_brand_thumbnail_url( $brand );
					return $result;
				} elseif ( 'wc_brand_url' == $field && function_exists( 'wc_get_brand_thumbnail_url' ) ) {
					$brands = wp_get_post_terms( $object->ID, 'product_brand', array( 'fields' => 'ids' ) );

					// Bail early if we don't have any brands registered.
					if ( 0 === count( $brands ) ) {
						return '';
					}
					$brand  = $brands[0];
					$brand  = get_term_by( 'id', $brand, 'product_brand' );
					$result = '#';
					if ( ! empty( $brand ) ) {
						$result = get_term_link( $brand, 'product_brand' );
					}
					return $result;
				} elseif ( 'sale_date' == $field && function_exists('porto_woocommerce_sale_product_period') ) {
					$dynamic_product = wc_get_product( $object->ID );
					$result          = '';
					if ( $dynamic_product && ! $dynamic_product->is_type( 'variable' ) ) {
						$result = porto_woocommerce_sale_product_period( $dynamic_product );
					}
					return $result;
				}
			}

			return $default;
		}

		/**
		 * Returns the dynamic content data
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_content_data( $builder_id = false, $atts = array() ) {
			$content_type       = false;
			$content_type_value = false;

			if ( isset( $atts['content_type'] ) ) {
				$content_type = $atts['content_type'];
			}
			if ( isset( $atts['content_type_value'] ) ) {
				$content_type_value = $atts['content_type_value'];
			}

			if ( $builder_id ) {
				if ( ! $content_type ) {
					$content_type = get_post_meta( $builder_id, 'content_type', true );
				}
				if ( ! $content_type_value ) {
					if ( $content_type ) {
						$content_type_value = get_post_meta( $builder_id, 'content_type_' . $content_type, true );
					}
				}
			}
			$result = false;

			if ( 'term' == $content_type ) {
				$args = array(
					'hide_empty' => true,
					'number'     => 1,
				);
				if ( $content_type_value ) {
					$args['taxonomy'] = $content_type_value;
				}
				$terms = get_terms( $args );
				if ( is_array( $terms ) && ! empty( $terms ) ) {
					$terms = array_values( $terms );
					return $terms[0];
				}
			} elseif ( $content_type && $content_type_value ) {
				$result = get_post( $content_type_value );
			} else {
				$args = array( 'numberposts' => 1 );
				if ( $content_type ) {
					$args['post_type'] = $content_type;
				}

				$result = get_posts( $args );

				if ( is_array( $result ) && isset( $result[0] ) ) {
					return $result[0];
				}
			}

			return $result;
		}

		/**
		 * Retrieve dynamic tags content from editor
		 *
		 * @since 2.3.0
		 */
		public function get_value() {
			check_ajax_referer( 'porto-nonce', 'nonce' );
			if ( isset( $_POST['content_type'] ) && isset( $_POST['content_type_value'] ) && ! empty( $_POST['source'] ) && ! empty( $_POST['field_name'] ) ) {
				$atts   = array(
					'content_type'       => $_POST['content_type'],
					'content_type_value' => $_POST['content_type_value'],
				);
				$object = $this->get_dynamic_content_data( false, $atts );
				if ( $object ) {
					if ( 'term' == $atts['content_type'] && 'post' == $_POST['source'] ) {
					} elseif ( 'term' != $atts['content_type'] && $atts['content_type'] && 'tax' == $_POST['source'] ) {
					} else {
						$result = $this->get_dynamic_content( false, $object, $_POST['source'], $_POST['field_name'] );
						if ( false === $result ) {
							wp_send_json_error();
						}
						wp_send_json_success( $result );
					}
				}
			}
			wp_send_json_error();
		}

		/**
		 * Retrieve acf fields from selected content type
		 *
		 * @since 2.3.0
		 */
		public function get_acf_fields() {
			check_ajax_referer( 'porto-nonce', 'nonce' );
			if ( class_exists( 'ACF' ) && isset( $_POST['content_type'] ) && isset( $_POST['content_type_value'] ) && 'term' != $_POST['content_type'] ) {
				$atts   = array(
					'content_type'       => $_POST['content_type'],
					'content_type_value' => $_POST['content_type_value'],
				);
				$object = $this->get_dynamic_content_data( false, $atts );
				if ( $object ) {
					include_once PORTO_SHORTCODES_LIB . 'dynamic_tags/class-porto-func-acf.php';
					global $post;
					$post   = $object;
					$fields = apply_filters( 'porto_gutenberg_editor_vars', array() );
					if ( isset( $fields['acf'] ) ) {
						$fields = $fields['acf'];
					}
					wp_send_json_success( $fields );
				}
			}
			wp_send_json_error();
		}


		/**
		 * Retrieve toolset fields from selected content type
		 *
		 * @since 2.9.0
		 */
		public function get_toolset_fields() {
			check_ajax_referer( 'porto-nonce', 'nonce' );
			if ( defined( 'TYPES_VERSION' ) && isset( $_POST['content_type'] ) && isset( $_POST['content_type_value'] ) && 'term' != $_POST['content_type'] ) {
				$atts   = array(
					'content_type'       => $_POST['content_type'],
					'content_type_value' => $_POST['content_type_value'],
				);
				$object = $this->get_dynamic_content_data( false, $atts );
				if ( $object ) {
					include_once PORTO_SHORTCODES_LIB . 'dynamic_tags/class-porto-func-toolset.php';
					global $post;
					$post   = $object;
					$fields = apply_filters( 'porto_gutenberg_editor_vars', array() );
					if ( isset( $fields['toolset'] ) ) {
						$fields = $fields['toolset'];
					}
					wp_send_json_success( $fields );
				}
			}
			wp_send_json_error();
		}


		/**
		 * Retrieve Elementor & WPB dynamic data
		 *
		 * @since 2.3.0
		 */
		public function dynamic_get_data( $dynamic_source, $dynamic_content, $dynamic_field, $index = '' ) {
			if ( empty( $dynamic_source ) || empty( $dynamic_content ) || ! in_array( $dynamic_field, $this->features ) ) {
				return;
			}
			$result = '';
			if ( ! apply_filters( 'porto_already_start_before', false ) ) {
				do_action( 'porto_dynamic_before_render' );
			}
			if ( 'post_info' == $dynamic_source ) {
				if ( 'field' == $dynamic_field ) {
					$date_format = '';
					if ( is_array( $dynamic_content ) ) {
						if ( '' == $index && isset( $dynamic_content['date_format'] ) ) {
							$date_format     = $dynamic_content['date_format'];
							$dynamic_content = $dynamic_content['field_dynamic_content'];
						} elseif ( '' !== $index && isset( $dynamic_content['field_' . $index . '_date_format'] ) ) {
							$date_format     = $dynamic_content['field_' . $index . '_date_format'];
							$dynamic_content = $dynamic_content['field_' . $index . '_dynamic_content'];
						}
					}
					$result = (string) $this->get_dynamic_post_field_prop( $dynamic_content, $date_format );
					$result = $this->get_dynamic_post_field( $result );
				} elseif ( 'image' == $dynamic_field ) { // For Dynamic Tag Image
					$result = $this->get_dynamic_post_image( $dynamic_content );
				}
			} elseif ( 'post_link' == $dynamic_source ) {
				if ( 'link' == $dynamic_field ) {
					$result = $this->get_dynamic_post_link( $dynamic_content );
				}
			} elseif ( 'meta_field' == $dynamic_source ) {
				$result = $this->get_dynamic_content( false, '', 'meta', $dynamic_content );
			} elseif ( 'meta_box' == $dynamic_source ) {
				$result   = array();
				$meta_ids = get_post_meta( get_the_ID(), $dynamic_content );
				$result   = array_merge( $result, $meta_ids ? $meta_ids : array() );
				$result   = $this->get_dynamic_post_field( $result );

				if ( 'like_count' == $dynamic_content && empty( $result ) ) {
					$result = '0';
				}
				if ( 'image' == $dynamic_field ) {
					$result = array( 'id' => $result );
				}
			} elseif ( 'term_meta' == $dynamic_source ) {
				$result     = array();
				$is_preview = porto_is_elementor_preview() || ( function_exists( 'vc_is_inline' ) && vc_is_inline() ) ||
				( isset( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'] && isset( $_REQUEST['post'] ) );
				if ( PortoBuilders::BUILDER_SLUG == get_post_type() ) {
					$porto_builder = get_post_meta( get_the_ID(), PortoBuilders::BUILDER_TAXONOMY_SLUG, true );
					if ( 'archive' == $porto_builder ) {
						PortoBuildersArchive::get_instance()->find_preview();
						$post_term = PortoBuildersArchive::get_instance()->edit_post_type;
					} elseif ( 'shop' == $porto_builder ) {
						$post_term = 'product';
					}
				}
				if ( ! $is_preview ) {
					$post_term = get_post_type();
				}
				if ( ! empty( $post_term ) && ! empty( $this->meta_terms[ $post_term ] ) ) {

					if ( $is_preview ) {
						$atts   = array(
							'content_type'       => 'term',
							'content_type_value' => $this->meta_terms[ $post_term ],
						);
						$object = $this->get_dynamic_content_data( false, $atts );
					} elseif ( is_tax() || is_category() || is_tag() ) {
						$object = get_queried_object();
					}
					if ( ! empty( $object ) ) {
						$result = $this->get_dynamic_content( false, $object, 'metabox', $dynamic_content );
					}
				}
			} elseif ( 'acf' == $dynamic_source && class_exists( 'Porto_Func_ACF' ) ) {
				$result = Porto_Func_ACF::get_instance()->acf_get_meta( $dynamic_content );
				if ( 'image' == $dynamic_field ) {
					$result = array( 'id' => $result );
				}
			} elseif ( 'toolset' == $dynamic_source && class_exists( 'Porto_Func_Toolset' ) ) {
				$result = Porto_Func_Toolset::get_instance()->toolset_get_meta( 'wpcf-' . $dynamic_content );
				if ( 'image' == $dynamic_field ) {
					$result = array( 'id' => $result );
				}
			} elseif ( 'taxonomy' == $dynamic_source ) {
				$result = get_the_term_list( get_the_ID(), $dynamic_content, '', ', ', '' );
				if ( is_wp_error( $result ) ) {
					return '';
				}
				$result = $this->get_dynamic_post_field( $result );
				$result = porto_strip_script_tags( $result );
			} elseif ( 'woocommerce' == $dynamic_source ) {
				$product = wc_get_product();
				if ( ! $product ) {
					return $result;
				}
				if ( 'image' == $dynamic_field ) { // For Dynamic Tag Image
					$result = $this->get_dynamic_woo_image( $dynamic_content );
				} else {
					if ( 'sales' == $dynamic_content ) {
						$result = $product->get_total_sales();
					} elseif ( 'excerpt' == $dynamic_content ) {
						$result = $product->get_short_description();
					} elseif ( 'sku' == $dynamic_content ) {
						$result = esc_html( $product->get_sku() );
					} elseif ( 'stock' == $dynamic_content ) {
						$result = $product->get_stock_quantity();
					} elseif ( 'sale_date' == $dynamic_content && function_exists('porto_woocommerce_sale_product_period') ) {
						$result = porto_woocommerce_sale_product_period( $product );
					}
				}
			} elseif ( 'dynamic_link_popup' == $dynamic_source ) { // wpb popup
				if ( ! empty( $dynamic_content ) ) {
					$result = '#porto-action_popup-id-' . $dynamic_content;
					global $porto_wpb_popup_template_ids;
					if ( ! isset( $porto_wpb_popup_template_ids ) ) {
						$porto_wpb_popup_template_ids = array();
						add_action( 'wp_footer', array( $this, 'print_script_template' ) );
					}
					$porto_wpb_popup_template_ids[] = $dynamic_content;
				}
			}
			if ( ! apply_filters( 'porto_already_start_before', false ) ) {
				do_action( 'porto_dynamic_after_render' );
			}
			return $result;
		}

		/**
		 * Print the script template
		 * 
		 * @since 3.6.0
		 */
		public function print_script_template() {

			global $porto_wpb_popup_template_ids;

			foreach ( $porto_wpb_popup_template_ids as $popup ) {
				echo '<script type="text/template" id="popup-id-'. $popup . '">';

				$popup_options = get_post_meta( $popup, 'popup_options', true );
				$style = '';
				if ( ! ( empty( $popup_options ) && empty( get_post_meta( $popup, 'popup_animation', true ) ) ) ) {
					if ( empty( $popup_options ) ) {
						$popup_options = array(
							'horizontal' => 50,
							'vertical'   => 50,
						);
						if ( ! empty( get_post_meta( $popup, 'popup_animation', true ) ) ) {
							$popup_options['animation'] = get_post_meta( $popup, 'popup_animation', true );
						}
						if ( ! empty( get_post_meta( $popup, 'popup_width', true ) ) ) {
							$popup_options['width'] = (int) get_post_meta( $popup, 'popup_width', true );
						}
						if ( ! empty( get_post_meta( $popup, 'disable_overlay', true ) ) ) {
							$popup_options['disable_overlay'] = get_post_meta( $popup, 'disable_overlay', true );
						}
						if ( ! empty( get_post_meta( $popup, 'popup_type', true ) ) ) {
							$popup_options['popup_type'] = get_post_meta( $popup, 'popup_type', true );
						}
						if ( ! empty( get_post_meta( $popup, 'popup_offcanvas_pos', true ) ) ) {
							$popup_options['popup_offcanvas_pos'] = get_post_meta( $popup, 'popup_offcanvas_pos', true );
						}
						if ( ! empty( get_post_meta( $popup, 'popup_offcanvas_bg', true ) ) ) {
							$popup_options['popup_offcanvas_bg'] = get_post_meta( $popup, 'popup_offcanvas_bg', true );
						}
					}

					if ( empty( $popup_options['builder'] ) ) {

						$style .= 'width: calc(100% - ' . ( empty( $porto_settings['grid-gutter-width'] ) ? '30' : (int) $porto_settings['grid-gutter-width'] ) . 'px); max-width: ' . (int) $popup_options['width'] . 'px; ';

						if ( is_rtl() ) {
							$left  = 'right';
							$right = 'left';
						} else {
							$left  = 'left';
							$right = 'right';
						}

						if ( ! empty( $popup_options['popup_type'] ) ) { // Off-Canvas						
							if ( ! empty( $popup_options['popup_offcanvas_pos'] ) ) { // right
								$style .= $right . ': 0; top: 0; bottom: 0;';
							} else {
								$style .= $left . ': 0; top: 0; bottom: 0;';
							}
						} else {
							if ( 50 === (int) $popup_options['horizontal'] ) {
								if ( 50 === (int) $popup_options['vertical'] ) {
									$style .= 'left: 50%;top: 50%;transform: translate(-50%, -50%);';
								} else {
									$style .= 'left: 50%;transform: translateX(-50%);';
								}
							} elseif ( 50 > (int) $popup_options['horizontal'] ) {
								$style .= $left . ':' . $popup_options['horizontal'] . '%;';
							} else {
								$style .= $right . ':' . ( 100 - $popup_options['horizontal'] ) . '%;';
							}
							if ( 50 === (int) $popup_options['vertical'] ) {
								if ( 50 !== (int) $popup_options['horizontal'] ) {
									$style .= 'top: 50%;transform: translateY(-50%);';
								}
							} elseif ( 50 > (int) $popup_options['vertical'] ) {
								$style .= 'top:' . $popup_options['vertical'] . '%;';
							} else {
								$style .= 'bottom:' . ( 100 - $popup_options['vertical'] ) . '%;';
							}
						}
					}
				}

				$popupClass = ( empty( $popup_options['builder'] ) ? 'position-absolute' : '' );
				if ( ! empty( $popup_options['disable_overlay'] ) )  {
					$popupClass = 'position-fixed';
				}
				$overlayClass = $popup_options['animation'];
				if ( ! empty( $popup_options['popup_type'] ) ) {
					if ( ! empty( $popup_options['popup_offcanvas_pos'] ) ) { // right
						$overlayClass = 'my-mfp-slide-' . $left;
					} else {
						$overlayClass = 'my-mfp-slide-' . $right;
					}
				}

				echo '<div class="' . ( ! empty( $popup_options['popup_type'] ) ? 'zoom-anim-dialog ' : '' ) . $popupClass . '"' . ( ! empty( $popup_options['disable_overlay' ] ) ? ' data-disable-overlay="true"' : '' ) . ' data-popup-id="' . esc_attr( $popup ) . '" data-overlay-class="' . esc_attr( $overlayClass ) . '"' . ' style="' . $style . '" >';
				if ( ! empty( $popup_options['popup_offcanvas_bg'] ) ) {
					echo '<style>.mfp-content [data-popup-id="' . $popup . '"], .mfp-content [data-id="' . $popup . '"]{ background-color:' . $popup_options['popup_offcanvas_bg'] . ' }</style>';
				}
				echo do_shortcode( '[porto_block ' . ( ! empty( $popup_options['popup_type'] ) ? '' : 'el_class="zoom-anim-dialog"' ) . ' id="' . intval( $popup ) . '" tracking="layout-popup-' . intval( $popup ) . '"]' );
				echo '</div>';
			
				echo '</script>';
				
			}
			unset( $GLOBALS['porto_wpb_popup_template_ids'] );
		}

		/**
		 * Set current post type for Elementor & WP Bakery
		 *
		 * @since 2.3.0
		 */
		public function before_render( $post_type = '', $id = '' ) {

			global $post;
			if ( ! $post_type ) {
				$post_type = get_post_type();
			}
			if ( ! $id && $post ) {
				$id = $post->ID;
			};
			$this->post = $post;
			if ( PortoBuilders::BUILDER_SLUG == $post_type && isset( $id ) ) {
				$porto_builder_type = get_post_meta( $id, PortoBuilders::BUILDER_TAXONOMY_SLUG, true );
				if ( 'product' == $porto_builder_type && class_exists( 'PortoCustomProduct' ) ) {
					/**
					 * Set post Product in Single Product builder
					 */
					PortoCustomProduct::get_instance()->restore_global_product_variable();
				} elseif ( 'single' == $porto_builder_type && class_exists( 'PortoBuildersSingle' ) ) {

					/**
					 * Set post in Single builder
					 */
					PortoBuildersSingle::get_instance()->restore_global_single_variable();
				}
			}
		}

		/**
		 * Reset current post type for Elementor & WP Bakery
		 *
		 * @since 2.3.0
		 */
		public function after_render( $post_type = '', $id = '' ) {
			global $post;
			if ( ! $post_type ) {
				$post_type = get_post_type( $this->post );
			}
			if ( ! $id && isset( $this->post ) ) {
				$id = $this->post->ID;
			}
			if ( PortoBuilders::BUILDER_SLUG == $post_type && isset( $id ) ) {
				$porto_builder_type = get_post_meta( $id, PortoBuilders::BUILDER_TAXONOMY_SLUG, true );
				if ( 'product' == $porto_builder_type && class_exists( 'PortoCustomProduct' ) ) {

					/**
					 * Unset post Product in Single Product Builder
					 */
					PortoCustomProduct::get_instance()->reset_global_product_variable();

				} elseif ( 'single' == $porto_builder_type && class_exists( 'PortoBuildersSingle' ) ) {

					/**
					 * Unset post Product in Single Builder
					 */
					PortoBuildersSingle::get_instance()->reset_global_single_variable();
				}
			}
		}

		/**
		 * Get dynamic Post Field
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_post_object_fields() {
			$fields = array(
				array(
					'label'   => esc_html__( 'Post', 'porto-functionality' ),
					'options' => array(
						'post_id'       => esc_html__( 'Post ID', 'porto-functionality' ),
						'post_title'    => esc_html__( 'Title', 'porto-functionality' ),
						'post_date'     => esc_html__( 'Date', 'porto-functionality' ),
						'post_modified' => esc_html__( 'Modified Date', 'porto-functionality' ),
						'post_content'  => esc_html__( 'Content', 'porto-functionality' ),
						'post_excerpt'  => esc_html__( 'Excerpt', 'porto-functionality' ),
						'post_status'   => esc_html__( 'Post Status', 'porto-functionality' ),
						'comment_count' => esc_html__( 'Comments Count', 'porto-functionality' ),
						'like_count'    => esc_html__( 'Like Posts Count', 'porto-functionality' ),
					),
				),
				array(
					'label'   => esc_html__( 'Logged In User', 'porto-functionality' ),
					'options' => array(
						'user_ID'           => esc_html__( 'User ID', 'porto-functionality' ),
						'user_email'        => esc_html__( 'User E-mail', 'porto-functionality' ),
						'user_login'        => esc_html__( 'User Login', 'porto-functionality' ),
						'user_display_name' => esc_html__( 'User Name', 'porto-functionality' ),
						'user_description'  => esc_html__( 'User Description', 'porto-functionality' ),
					),
				),
				array(
					'label'   => esc_html__( 'Author', 'porto-functionality' ),
					'options' => array(
						'ID'          => esc_html__( 'Author ID', 'porto-functionality' ),
						'email'       => esc_html__( 'Author E-mail', 'porto-functionality' ),
						'login'       => esc_html__( 'Author Login', 'porto-functionality' ),
						'name'        => esc_html__( 'Author Name', 'porto-functionality' ),
						'description' => esc_html__( 'Author Description', 'porto-functionality' ),
					),
				),
			);

			return $fields;
		}

		/**
		 * Get dynamic WooCommerce Link
		 *
		 * @since 3.5.0
		 */
		public function get_dynamic_woo_object_links() {
			$fields = array(
				array(
					'label'   => esc_html__( 'Product', 'porto-functionality' ),
					'options' => array(),
				),
			);

			if ( class_exists( 'WC_Brands' ) ) {
				$fields[0]['options'] = array(
					'brand_url' => esc_html__( 'Brand URL (WooCommerce Brands)', 'porto-functionality' ),
				);
			}

			return $fields;
		}

		/**
		 * Get dynamic Post Link
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_post_object_links() {
			$fields = array(
				array(
					'label'   => esc_html__( 'Post', 'porto-functionality' ),
					'options' => array(
						'post_url'           => esc_html__( 'Post Url', 'porto-functionality' ),
						'site_url'           => esc_html__( 'Site Url', 'porto-functionality' ),
						'author_archive_url' => esc_html__( 'Author Archive Url', 'porto-functionality' ),
						'author_website_url' => esc_html__( 'Author Website Url', 'porto-functionality' ),
						'comments_url'       => esc_html__( 'Comments Url', 'porto-functionality' ),
					),
				),
			);

			return $fields;
		}

		/**
		 * Get dynamic Post Image Field
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_taxonomy() {
			$option_fields  = array();
			$taxonomy_array = get_taxonomies();
			if ( $taxonomy_array && is_array( $taxonomy_array ) ) {
				$post_type = get_post_type();
				if ( count( $taxonomy_array ) > 1 ) {
					foreach ( $taxonomy_array as $value ) {
						$taxonomy_object = get_taxonomy( (string) $value );
						$taxonomy_type   = $taxonomy_object->object_type;
						if ( in_array( $post_type, $taxonomy_type ) ) {
							$key                   = $taxonomy_object->name;
							$option_fields[ $key ] = $taxonomy_object->label;
						} else {
							continue;
						}
					}
				} else {
					$taxonomy_object = get_taxonomy( (string) $taxonomy_array[0] );
					$taxonomy_type   = $taxonomy_object->object_type;

					if ( in_array( $post_type, $taxonomy_type ) ) {
						$key                   = $taxonomy_object->name;
						$option_fields[ $key ] = $taxonomy_object->label;
					}
				}
			}
			return $option_fields;
		}

		/**
		 * Get dynamic Post Image Field
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_post_object_image() {
			$objects = array(
				'featured'      => esc_html__( 'Featured Image', 'porto-functionality' ),
				'user_avatar'   => esc_html__( 'Logged-in User Avatar', 'porto-functionality' ),
				'author_avatar' => esc_html__( 'Post Author Avatar', 'porto-functionality' ),
			);

			return $objects;
		}

		/**
		 * Get dynamic Woo Image Field
		 *
		 * @since 3.5.0
		 */
		public function get_dynamic_woo_object_image() {
			$objects = array();
			if ( class_exists( 'WC_Brands' ) ) {
				$objects = array(
					'brand' => esc_html__( 'Brand Image (WooCommerce Brand)', 'porto-functionality' ),
				);
			}

			return $objects;
		}

		/**
		 * Get dynamic Post Field
		 *
		 * @var string $ret Post Field Key
		 * @since 2.3.0
		 */
		public function get_dynamic_post_field( $ret ) {
			if ( is_array( $ret ) ) {
				$temp_content = '';
				if ( count( $ret ) >= 1 ) {
					foreach ( $ret as $value ) {
						$temp_content .= (string) $value . ' ';
					}
				}
				$ret = $temp_content;
			}
			return $ret;
		}

		/**
		 * Get dynamic Post Link
		 *
		 * @var string $ret Post Link Key
		 * @since 2.3.0
		 */
		public function get_dynamic_post_link( $property ) {
			$ret = '';
			switch ( $property ) {
				case 'post_url':
					$ret = get_permalink();
					break;
				case 'site_url':
					$ret = home_url();
					break;
				case 'author_archive_url':
					global $authordata;
					if ( $authordata ) {
						$ret = get_author_posts_url( $authordata->ID, $authordata->user_nicename );
					}
					break;
				case 'author_website_url':
					$ret = get_the_author_meta( 'url' );
					break;
				case 'comments_url':
					$ret = get_comments_link();
					break;
				default:
					$ret = '';
					break;
			}
			return $ret;
		}

		/**
		 * Get dynamic Woo Link
		 *
		 * @var string $ret Post Link Key
		 * @since 3.5.0
		 */
		public function get_dynamic_woo_link( $property ) {
			$ret = '';
			switch ( $property ) {
				case 'brand_url':
					$brands = wp_get_post_terms( get_the_ID(), 'product_brand', array( 'fields' => 'ids' ) );

					// Bail early if we don't have any brands registered.
					if ( 0 === count( $brands ) ) {
						return '';
					}
					$brand = $brands[0];
					$brand = get_term_by( 'id', $brand, 'product_brand' );
					if ( ! empty( $brand ) ) {
						$ret = get_term_link( $brand, 'product_brand' );
					}
					break;
				default:
					$ret = '';
					break;
			}
			return $ret;
		}

		/**
		 * Get dynamic Post Field
		 *
		 * @var string $property post_field_key
		 * @since 2.3.0
		 */
		public function get_dynamic_post_field_prop( $property = null, $date_format = null ) {

			if ( ! $property ) {
				return false;
			}
			$author_properties = array(
				'ID',
				'email',
				'login',
				'name',
				'description',
			);
			$user_properties = array(
				'user_ID',
				'user_email',
				'user_login',
				'user_display_name',
				'user_description',
			);
			if ( $author_properties && in_array( $property, $author_properties ) ) {
				$value = '';
				if ( 'name' == $property ) {
					$value = get_the_author_meta( 'display_name' );
				} else {
					$value = get_the_author_meta( $property );
				}
				return wp_kses_post( $value );

			} else if ( $user_properties && in_array( $property, $user_properties ) ) {
				$user_id = get_current_user_id();
				if ( $user_id ) {
					$value = get_the_author_meta( substr( $property, 5 ), $user_id );
					return wp_kses_post( $value );
				} else {
					return '';
				}
			} else {
				
				if ( ( ! porto_is_elementor_preview() && ! is_preview() && ! porto_is_vc_preview() ) && 'post_title' == $property && defined( 'PORTO_VERSION' ) ) {
					return porto_page_title();
				}

				$this->is_term_archive = false;
				$object                = $this->get_dynamic_post_field_object();
				$vars                  = $object ? get_object_vars( $object ) : array();

				if ( $this->is_term_archive ) {
					if ( 'post_id' == $property ) {
						return isset( $vars['term_id'] ) ? $vars['term_id'] : false;
					}
				}
				if ( 'post_content' == $property && defined( 'WPB_VC_VERSION' ) ) {
					if ( function_exists( 'vc_is_inline' ) && vc_is_inline() ) {
						$vars['post_content'] = isset( $vars['post_content'] ) ? apply_filters( 'the_content', do_shortcode( $vars['post_content'] ) ) : false;
					} else {
						$vars['post_content'] = isset( $vars['post_content'] ) ? do_shortcode( $vars['post_content'] ) : false;
					}
				}
				if ( 'post_id' === $property ) {
					$vars['post_id'] = isset( $vars['ID'] ) ? $vars['ID'] : false;
				}
				if ( 'like_count' === $property ) {
					$vars['like_count'] = get_post_meta( $vars['ID'], 'like_count', true );
					if ( empty( $vars['like_count'] ) ) {
						$vars['like_count'] = '0';
					}
				}
				if ( ! empty( $date_format ) ) {
					$vars['post_date']     = get_the_date( esc_html( $date_format ) );
					$vars['post_modified'] = get_the_modified_date( esc_html( $date_format ) );
				}
			}
			return isset( $vars[ $property ] ) ? $vars[ $property ] : false;
		}

		/**
		 * Get dynamic Post Field Object
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_post_field_object() {
			global $post;
			$post_object = false;
			if ( is_singular() ) {
				$post_object = $post;
			} elseif ( is_tax() || is_category() || is_tag() || is_author() ) {
				$post_object           = get_queried_object();
				$this->is_term_archive = true;
			} elseif ( wp_doing_ajax() ) {
				$post_object = get_post( isset( $this->post_id ) ? $this->post_id : '' );
			} elseif ( class_exists( 'Woocommerce' ) && is_shop() ) {
				$post_object = get_post( (int) get_option( 'woocommerce_shop_page_id' ) );
			} elseif ( is_archive() || is_post_type_archive() || is_home() ) {
				$post_object      = get_queried_object();
			}
			return $post_object;
		}

		/**
		 * Get dynamic Post Field Object
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_post_image( $dynamic_content ) {
			$image_id  = '';
			$image_url = '';
			switch ( $dynamic_content ) {
				case 'featured':
					global $post;
					if ( class_exists( 'woocommerce' ) && is_shop() ) {
						$id = (int) get_option( 'woocommerce_shop_page_id' );
					} elseif ( is_tax() || is_category() || is_tag() || is_author() || is_home() ) {
						$id = get_queried_object_id();
					} else {
						$id = $post->ID;
					}
					$image_id = get_post_thumbnail_id( $id );
					if ( is_tax() ) {
						$image_id = get_term_meta( $id, 'thumbnail_id', true );
						if ( function_exists( 'is_product_category' ) && is_product_category() ) {
							$tmp_img    = esc_url( get_metadata( 'product_cat', $id, 'category_image', true ) );
							if ( $tmp_img ) {
								$tmp_img = esc_url( $tmp_img );
								$upload_dir = wp_upload_dir();
								if ( ! empty( $upload_dir ) && isset( $upload_dir['baseurl'] ) ) {
									$upload_dir = $upload_dir['baseurl'];
									$tmp_img    = str_replace( 'https://sw-themes.com/porto_dummy/wp-content/uploads', $upload_dir, $tmp_img );
									$tmp_img    = str_replace( 'http://sw-themes.com/porto_dummy/wp-content/uploads', $upload_dir, $tmp_img );
								}
								$image_id = porto_get_image_id( $tmp_img );
								if ( ! $image_id ) {
									$image_id = porto_get_image_id( str_replace( 'www.', '', $tmp_img ) );
								}
							}
						}
					}
					if ( ! $image_id ) {
						$gallery = get_post_meta( $id, 'supported_images' );
						if ( is_array( $gallery ) && count( $gallery ) ) {
							$image_id = $gallery[0];
						}
					}
					break;
				case 'user_avatar':
					$current_user = wp_get_current_user();
					if ( $current_user ) {
						$image_url = get_avatar_url( $current_user->ID );
					}
					return array(
						'id'  => 'dynamic_url',
						'url' => $image_url,
					);
				case 'author_avatar':
					$image_url = get_avatar_url( get_the_author_meta( 'email' ) );
					return array(
						'id'  => 'dynamic_url',
						'url' => $image_url,
					);
			}

			if ( $image_id ) {
				$res_img_url = wp_get_attachment_image_src( $image_id, 'full' );
				if ( $res_img_url ) {
					$res_img_url = $res_img_url[0];
				}
			}
			return array(
				'id'  => $image_id,
				'url' => ! empty( $res_img_url ) ? $res_img_url : $image_url,
			);
		}

		/**
		 * Get dynamic WooCommerce Field Object
		 *
		 * @since 3.5.0
		 */
		public function get_dynamic_woo_image( $dynamic_content ) {
			$image_id  = '';
			$image_url = '';
			$res       = array(
				'id'  => '',
				'url' => '',
			);
			switch ( $dynamic_content ) {
				case 'brand':
				case 'wc_brand_image':
					$brands = wp_get_post_terms( get_the_ID(), 'product_brand', array( 'fields' => 'ids' ) );

					// Bail early if we don't have any brands registered.
					if ( 0 === count( $brands ) ) {
						return $res;
					}
					$brand     = $brands[0];
					$image_url = wc_get_brand_thumbnail_url( $brand );
					if ( ! empty( $image_url ) ) {
						$res = array(
							'id'  => get_term_meta( $brand, 'thumbnail_id', true ),
							'url' => $image_url,
						);
					}
					break;
			}

			return $res;
		}

		/**
		 * Add dynamic field vars
		 *
		 * @since 2.3.0
		 */
		public function get_dynamic_metabox_fields( $widget, $type = 'meta' ) {
			$post_type = '';
			$post_term = '';
			$fn_name   = '';
			$backup    = '';
			if ( PortoBuilders::BUILDER_SLUG == get_post_type() ) {
				$porto_builder = get_post_meta( get_the_ID(), PortoBuilders::BUILDER_TAXONOMY_SLUG, true );
				if ( 'product' == $porto_builder ) {
					$post_type = 'product';
				} elseif ( 'single' == $porto_builder ) {
					$post_type = PortoBuildersSingle::get_instance()->edit_post_type;
				} elseif ( 'archive' == $porto_builder ) {
					$post_term = PortoBuildersArchive::get_instance()->edit_post_type;
				} elseif ( 'shop' == $porto_builder ) {
					$post_term = 'product';
				}
			} else {
				$post_type = get_post_type();
			}
			if ( 'meta' == $type && ! empty( $post_type ) ) {
				$fn_name = 'porto_' . $post_type . '_meta_fields';
			}
			if ( 'term' == $type && ! empty( $post_term ) && ! empty( $this->meta_terms[ $post_term ] ) ) {
				global $porto_settings;
				if ( isset( $porto_settings['show-category-skin'] ) ) {
					$backup                               = $porto_settings['show-category-skin'];
					$porto_settings['show-category-skin'] = false;
				}
				$fn_name = 'porto_' . $this->meta_terms[ $post_term ] . '_meta_fields';
			}
			$meta_fields = array();
			if ( ! empty( $fn_name ) && function_exists( $fn_name ) ) {
				$post_fields = $fn_name();
				foreach ( $post_fields as $key => $arr ) {
					if ( array_key_exists( $arr['type'], $this->metabox_types ) && in_array( $widget, $this->metabox_types[ $arr['type'] ] ) ) {
						$meta_fields[ $key ] = array( esc_js( $arr['title'] ) );
					}
				}
			}
			if ( ! empty( $backup ) ) {
				global $porto_settings;
				$porto_settings['show-category-skin'] = $backup;
			}
			return $meta_fields;
		}

		/**
		 * Retrieve Woo fields for each group
		 *
		 * @since 2.3.0
		 */
		public function get_woo_fields() {

			$fields = array(
				'excerpt'   => esc_html__( 'Product Short Description', 'porto-functionality' ),
				'sku'       => esc_html__( 'Product SKU', 'porto-functionality' ),
				'sales'     => esc_html__( 'Product Sales', 'porto-functionality' ),
				'stock'     => esc_html__( 'Product Stock', 'porto-functionality' ),
				'sale_date' => esc_html__( 'Product Sale End Date', 'porto-functionality' ),
			);
			return $fields;
		}
	}
endif;

Porto_Func_Dynamic_Tags_Content::get_instance();
