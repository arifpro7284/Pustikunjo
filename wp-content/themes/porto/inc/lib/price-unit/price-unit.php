<?php
/**
 * Product price unit of measurement (e.g. per kg, / m²).
 *
 * Admin: Products → Edit product → Inventory.
 * Variable products: set on the parent and/or per variation.
 *
 * @package Porto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Porto_Product_Price_Unit' ) ) :

	final class Porto_Product_Price_Unit {

		const META_KEY = '_porto_price_unit';

		public static function init() {
			add_action( 'woocommerce_product_options_inventory_product_data', array( __CLASS__, 'render_simple_field' ), 15 );
			add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_simple' ), 15, 1 );

			add_action( 'woocommerce_variation_options_inventory', array( __CLASS__, 'render_variation_field' ), 15, 3 );
			add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation' ), 15, 2 );

			add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'append_unit_to_price_html' ), 20, 2 );
		}

		public static function render_simple_field() {
			woocommerce_wp_text_input(
				array(
					'id'          => self::META_KEY,
					'label'       => __( 'Unit of measurement', 'porto' ),
					'placeholder' => __( 'e.g. per kg, / m², per unit', 'porto' ),
					'desc_tip'    => true,
					'description' => __( 'Optional text shown after the price. For variable products, this is used when no variation-specific unit is set.', 'porto' ),
				)
			);
		}

		public static function render_variation_field( $loop, $variation_data, $variation ) {
			$value = $variation ? get_post_meta( $variation->ID, self::META_KEY, true ) : '';
			woocommerce_wp_text_input(
				array(
					'id'            => self::META_KEY . '_' . (int) $loop,
					'name'          => self::META_KEY . '[' . (int) $loop . ']',
					'value'         => $value,
					'label'         => __( 'Unit of measurement', 'porto' ),
					'placeholder'   => __( 'e.g. per kg', 'porto' ),
					'desc_tip'      => true,
					'description'   => __( 'Optional. Shown after this variation price. Leave empty to use the variable product unit above.', 'porto' ),
					'wrapper_class' => 'form-row form-row-full',
				)
			);
		}

		public static function save_simple( $post_id ) {
			if ( isset( $_POST[ self::META_KEY ] ) && ! is_array( $_POST[ self::META_KEY ] ) ) {
				update_post_meta( $post_id, self::META_KEY, sanitize_text_field( wp_unslash( $_POST[ self::META_KEY ] ) ) );
			}
		}

		public static function save_variation( $variation_id, $i ) {
			if ( ! isset( $_POST[ self::META_KEY ] ) || ! is_array( $_POST[ self::META_KEY ] ) ) {
				return;
			}
			if ( ! isset( $_POST[ self::META_KEY ][ $i ] ) ) {
				return;
			}
			update_post_meta( $variation_id, self::META_KEY, sanitize_text_field( wp_unslash( $_POST[ self::META_KEY ][ $i ] ) ) );
		}

		public static function get_unit_for_product( $product ) {
			if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
				return '';
			}
			$unit = '';
			if ( $product->is_type( 'variation' ) ) {
				$unit = get_post_meta( $product->get_id(), self::META_KEY, true );
				if ( '' === $unit ) {
					$unit = get_post_meta( $product->get_parent_id(), self::META_KEY, true );
				}
			} else {
				$unit = get_post_meta( $product->get_id(), self::META_KEY, true );
			}

			return apply_filters( 'porto_product_price_unit_text', (string) $unit, $product );
		}

		public static function append_unit_to_price_html( $price_html, $product ) {
			if ( ! $price_html || ! $product ) {
				return $price_html;
			}

			if ( is_admin() && ! wp_doing_ajax() ) {
				return $price_html;
			}

			if ( ! apply_filters( 'porto_product_price_unit_show', true, $product ) ) {
				return $price_html;
			}

			$unit = self::get_unit_for_product( $product );
			if ( '' === $unit ) {
				return $price_html;
			}

			return $price_html . '<span class="porto-pr-unit">' . esc_html( $unit ) . '</span>';
		}
	}

	Porto_Product_Price_Unit::init();

endif;
