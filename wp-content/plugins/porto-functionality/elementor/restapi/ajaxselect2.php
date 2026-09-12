<?php

/**
 * Get posts and terms in editor
 *
 * @since 2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Porto_Elementor_Ajax_Select2_Api {

	public function action( $request ) {
		if ( isset( $request['method'] ) && in_array( $request['method'], array( 'product_cat', 'category', 'portfolio_cat', 'member_cat', 'faq_cat', 'nav_menu' ) ) ) {
			return $this->get_terms( $request );
		} elseif ( isset( $request['method'] ) && 'orderby' == $request['method'] ) {
			return $this->get_orderby( $request );
		} elseif ( false !== strpos( $request['method'], '_alltax' ) ) {
			$options = array(
				array(
					'id'   => '',
					'text' => esc_html__( 'All', 'porto-functionality' ),
				),
			);
			// The post type comes from $request['condition'] ( Elementor ) or, when absent ( the Gutenberg
			// block bakes it into the method, e.g. "product_alltax" ), from the method prefix on dropdown
			// requests ( search / add_default ) - not on the ids-only initial value load.
			$tax_condition = '';
			if ( ! empty( $request['condition'] ) ) {
				$tax_condition = $request['condition'];
			} elseif ( isset( $request['s'] ) || isset( $request['add_default'] ) ) {
				$tax_condition = str_replace( '_alltax', '', $request['method'] );
			}
			if ( $tax_condition ) {
				$new_taxonomies = get_object_taxonomies( sanitize_key( $tax_condition ), 'objects' );
				foreach ( $new_taxonomies as $new_taxonomy ) {
					if ( in_array( $new_taxonomy->name, array( 'post_format', 'product_visibility' ) ) ) {
						continue;
					}
					$options[] = array(
						'id'   => esc_html( $new_taxonomy->name ),
						'text' => esc_html( $new_taxonomy->label ),
					);
				}
			} elseif ( ! empty( $request['ids'] ) ) {
				$tax = get_taxonomy( $request['ids'] );
				if ( $tax && ! is_wp_error( $tax ) ) {
					$options[] = array(
						'id'   => esc_html( $tax->name ),
						'text' => esc_html( $tax->label ),
					);
				}
			}
			return array( 'results' => $options );
		} elseif ( false !== strpos( $request['method'], '_allterm' ) ) {
			$options = array();

			// The taxonomy comes from $request['condition'] ( Elementor ) or, when absent ( the Gutenberg
			// block bakes it into the method, e.g. "product_cat_allterm" ), from the method prefix on
			// dropdown requests - not on the ids-only initial value load.
			$term_taxonomy = '';
			if ( ! empty( $request['condition'] ) ) {
				$term_taxonomy = $request['condition'];
			} elseif ( isset( $request['s'] ) || isset( $request['add_default'] ) ) {
				$term_taxonomy = str_replace( '_allterm', '', $request['method'] );
			}
			if ( $term_taxonomy ) {
				$args = array(
					'taxonomy'   => sanitize_text_field( $term_taxonomy ), // taxonomy name
					'hide_empty' => false,
					'fields'     => 'id=>name',
				);
				if ( isset( $request['s'] ) ) {
					$args['name__like'] = sanitize_text_field( $request['s'] );
				}
				$terms = get_terms( $args );

				if ( isset( $request['add_default'] ) ) {
					$options[] = array(
						'id'   => '',
						'text' => esc_html__( 'Default', 'porto-functionality' ),
					);
				}
				foreach ( $terms as $term_id => $term_name ) {
					$options[] = array(
						'id'   => esc_html( $term_id ),
						'text' => esc_html( $term_name ),
					);
				}
			} elseif ( ! empty( trim( $request['ids'] ) ) ) {
				$ids = explode( ',', sanitize_text_field( trim( $request['ids'] ) ) );
				foreach ( $ids as $term_id ) {
					$term = get_term( $term_id );
					if ( $term && ! is_wp_error( $term ) ) {
						$options[] = array(
							'id'   => esc_html( $term_id ),
							'text' => esc_html( $term->name ),
						);
					}
				}
			}
			return array( 'results' => $options );
		} elseif ( false !== strpos( $request['method'], '_particularpage' ) ) { // conditional rendering
			if ( ! empty( $request['condition'] ) ) {
				return $this->get_posts( array( 'method' => $request['condition'], 'count' => 'all' ) );
			} elseif ( ! empty( $request['ids'] ) ) {
				return $this->get_posts( array( 'method' => 'any', 'ids' => $request['ids'] ) );
			} else {
				return array( 'results' => array() );
			}
		} elseif ( false !== strpos( $request['method'], '_allid' ) ) {
			$options = array();
			if ( isset( $request['s'] ) ) {
				// Post type from $request['condition'] ( Elementor ) or the method prefix ( Gutenberg
				// bakes it in, e.g. "product_allid" ).
				$post_type = ! empty( $request['condition'] ) ? $request['condition'] : str_replace( '_allid', '', $request['method'] );
				if ( empty( $post_type ) ) {
					$post_type = 'post';
				}
				$args = array(
					'post_type'           => $post_type,
					'post_status'         => 'publish',
					'numberposts'         => -1,
					's'                   => sanitize_text_field( $request['s'] ),
					'search_columns'      => 'post_title'
				);
				$posts = get_posts( $args );
				if ( ! empty( $posts ) ) {
					foreach ( $posts as $post ) {
						$options[] = array(
							'id'   => (int) $post->ID,
							'text' => $post->post_title . sprintf( esc_html__( ' : ID(%s)', 'porto-functionality' ), $post->ID ),
						);
					}
				}
			} elseif ( ! empty( $request['ids'] ) ) {
				$ids = explode( ',', $request['ids'] );
				if ( ! empty( $ids ) ) {
					foreach ( $ids as $post_id ) {
						$options[] = array(
							'id'   => (int) $post_id,
							'text' => get_the_title( $post_id ) . sprintf( esc_html__( ' : ID(%s)', 'porto-functionality' ), $post_id ),
						);
					}
				}
			}
			return array( 'results' => $options );
		} elseif ( false !== strpos( $request['method'], 'product_ids' ) ) {
			$options = array();
			$ids     = array();
			if ( ! empty( ( $request['ids'] ) ) ) {
				$ids = explode( ',', $request['ids'] );
			} else {
				if ( isset( $request['s'] ) ) {
					$data_store = WC_Data_Store::load( 'product' );
					$ids = $data_store->search_products( $request['s'], '', true, false, 10, array(), array() );
				}
			}
			foreach ( $ids as $id ) {
				$product_object = wc_get_product( $id );
				if ( ! $product_object ) {
					continue;
				}
				$formatted_name = $product_object->get_formatted_name();

				$options[] = array(
					'id' => $id,
					'text' => rawurldecode( wp_strip_all_tags( $formatted_name ) ),
				);
			}
			return array( 'results' => $options );
		} elseif ( isset( $request['method'] ) ) { 
			return $this->get_posts( $request );
		}
	}

	public function get_posts( $request ) {
		$post_type = $request['method'];
		if ( 'porto_builder_type' == $post_type || 'porto_builder_popup' == $post_type ) {
			$post_type = 'porto_builder';
		}
		$query_args = array(
			'post_type'      => sanitize_text_field( $post_type ),
			'post_status'    => 'publish',
			'posts_per_page' => 35,
		);
		if ( ! empty( $request['count'] ) && 'all' == $request['count'] ) {
			$query_args['posts_per_page'] = 300;//-1;
			$query_args['fields'] = 'ids';
		}

		if ( isset( $request['ids'] ) ) {
			if ( empty( $request['ids'] ) ) {
				return array( 'results' => array() );
			}
			$query_args['post__in'] = explode( ',', sanitize_text_field( $request['ids'] ) );
			$query_args['orderby']  = 'post__in';
			$query_args['order']    = 'ASC';
		}
		if ( isset( $request['s'] ) ) {
			$query_args['s'] = sanitize_text_field( $request['s'] );
		}
		if ( 'porto_builder' == $request['method'] ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => PortoBuilders::BUILDER_TAXONOMY_SLUG,
					'field'    => 'name',
					'terms'    => array( 'block' ),
				),
			);
		}
		if ( 'porto_builder_type' == $request['method'] ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => PortoBuilders::BUILDER_TAXONOMY_SLUG,
					'field'    => 'name',
					'terms'    => array( 'type' ),
				),
			);
		}
		if ( 'porto_builder_popup' == $request['method'] ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => PortoBuilders::BUILDER_TAXONOMY_SLUG,
					'field'    => 'name',
					'terms'    => array( 'popup' ),
				),
			);
		}

		$query   = new WP_Query( $query_args );
		$options = array();
		if ( $query->have_posts() ) {
			if ( isset( $request['add_default'] ) ) {
				$options[] = array(
					'id'   => '',
					'text' => __( 'None', 'porto-functionality' ),
				);
			}
			$posts = $query->get_posts();
			foreach ( $posts as $p ) {
				if ( empty( $query_args['fields'] ) ) {
					$options[] = array(
						'id'   => (int) $p->ID,
						'text' => str_replace( array( '&amp;', '&#039;' ), array( '&', '\'' ), esc_html( $p->post_title ) ),
					);
				} else {
					$options[] = array(
						'id'   => (int) $p,
						'text' => str_replace( array( '&amp;', '&#039;' ), array( '&', '\'' ), esc_html( get_the_title( $p ) ) ),
					);
				}
			}
		}
		return array( 'results' => $options );
	}

	public function get_terms( $request ) {
		if ( ! taxonomy_exists( sanitize_text_field( $request['method'] ) ) ) {
			return array( 'results' => array() );
		}
		$query_args = array(
			'taxonomy'   => sanitize_text_field( $request['method'] ), // taxonomy name
			'hide_empty' => false,
		);

		if ( isset( $request['ids'] ) ) {
			if ( empty( $request['ids'] ) ) {
				return array( 'results' => array() );
			}
			$query_args['include'] = explode( ',', sanitize_text_field( $request['ids'] ) );
			$query_args['orderby'] = 'include';
			$query_args['order']   = 'ASC';
		}
		if ( isset( $request['s'] ) ) {
			$query_args['name__like'] = sanitize_text_field( $request['s'] );
		}

		$terms   = get_terms( $query_args );
		$options = array();
		if ( count( $terms ) ) :
			if ( isset( $request['add_default'] ) ) {
				$options[] = array(
					'id'   => '',
					'text' => esc_html__( 'Default', 'porto-functionality' ),
				);
			}
			foreach ( $terms as $term ) {
				$options[] = array(
					'id'   => (int) $term->term_id,
					'text' => str_replace( array( '&amp;', '&#039;' ), array( '&', '\'' ), esc_html( $term->name ) ),
				);
			}
		endif;
		return array( 'results' => $options );
	}

	public function get_orderby( $request ) {
		$ids = array();

		if ( isset( $request['ids'] ) ) {
			if ( empty( $request['ids'] ) ) {
				return array( 'results' => array() );
			} else {
				$ids = explode( ',', $request['ids'] );
			}
		} else {
			$ids = array_values( porto_vc_woo_order_by() );
		}

		$arr = array_flip( porto_vc_woo_order_by() );

		foreach ( $ids as $id ) {
			if ( ! empty( $id ) ) {
				$id = trim( $id );
				$options[] = array(
					'id'   => $id,
					'text' => $arr[ $id ],
				);
			}
		}
		return array( 'results' => $options );
	}
}

function porto_elementor_ajax_select2_api( WP_REST_Request $request ) {
	$class = new Porto_Elementor_Ajax_Select2_Api();
	return $class->action( $request );
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'ajaxselect2/v1',
			'/(?P<method>[\w-]+)/',
			array(
				'methods'             => 'GET',
				'callback'            => 'porto_elementor_ajax_select2_api',
				'permission_callback' => '__return_true',
			)
		);
	}
);
