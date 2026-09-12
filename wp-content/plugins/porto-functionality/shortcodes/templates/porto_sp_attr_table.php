<?php
// Porto Single Product Attributes

if ( ! defined( 'PORTO_VERSION' ) ) {
	return;
}

$default_atts = array(
	'table_title'      => '',
	'title_typography' => '',
	'title_color'      => '',
	'attr_source'      => 'all',
	'attr_include'     => '',
	'attr_exclude'     => '',
);
extract( // @codingStandardsIgnoreLine
	shortcode_atts(
		$default_atts,
		$atts
	)
);

global $product;
$attributes = array(
    'width'  => __( 'Width', 'woocommerce' ),
    'height' => __( 'Height', 'woocommerce' ),
    'length' => __( 'Length', 'woocommerce' ),
    'weight' => __( 'Weight', 'woocommerce' ),
);
$wc_attributes = wc_get_attribute_taxonomy_labels();
$pd_attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );
foreach( $pd_attributes as $attribute_key => $attribute_val ) {
    if ( strpos( $attribute_key, 'pa_' ) !== 0 ) { // product custom attribute
        $wc_attributes[ 'porto_ca_' . $attribute_key ] = $attribute_val['name'];
    }
}
if ( 'include' == $attr_source ) {
    if ( is_string( $attr_include ) ) {
        $attr_include = explode( ',', $attr_include );
    }
    if ( is_array( $attr_include ) ) {
        foreach ( $attributes as $key => $attribute ) {
            if ( ! in_array( $key, $attr_include ) ) {
                unset( $attributes[ $key ] );
            }
        }
        if ( $wc_attributes ) {
            foreach ( $wc_attributes as $key => $attribute ) {
                if ( in_array( 'pa_' . $key, $attr_include ) ) {
                    $attributes[ 'pa_' . $key ] = $attribute;
                }
            }
        }
    }
} else {
    if ( ! empty( $attr_exclude ) ) {
        if ( is_string( $attr_exclude ) ) {
            $attr_exclude = explode( ',', $attr_exclude );
        }
        if ( is_array( $attr_exclude ) ) {
            foreach( $attributes as $key => $value ) {
                if ( in_array( $key, $attr_exclude ) ) {
                    unset( $attributes[$key] );
                }
            }
        }
    }
    if ( $wc_attributes ) {
        if ( is_string( $attr_exclude ) ) {
            $attr_exclude = explode( ',', $attr_exclude );
        }
        foreach ( $wc_attributes as $key => $attribute ) {
            if ( 'all' == $attr_source || '' == $attr_exclude || ( is_array( $attr_exclude ) && ! in_array( 'pa_' . $key, $attr_exclude ) && 'exclude' == $attr_source ) ) {
                if ( strpos( $key, 'porto_ca_' ) === 0 ) {
                    $attributes[ str_replace( 'porto_ca_', '' , $key )  ] = $attribute;
                } else {
                    $attributes[ 'pa_' . $key ] = $attribute;
                }
            }
        }
    }
}
if ( function_exists( 'porto_enqueue_link_style' ) ) {
    porto_enqueue_link_style( 'porto-sp-attr-table', PORTO_SHORTCODES_URL . '/assets/cp-attribute-table/attribute-table.css' );
}
$not_empty = false;
ob_start();
?>
<table class="porto-cp-attr-table wc-attr-table<?php echo ( ! empty( $shortcode_class ) ? esc_attr( $shortcode_class ) : '' ); ?>">
    <?php if ( '' !== $table_title ) { ?>
        <thead>
            <tr>
                <th colspan="2"><span class="porto-attr-title"><?php echo esc_html( $table_title ); ?></span></th>
            </tr>
        </thead>
    <?php } ?>
    <tbody>
        <?php
        if ( ! empty ( $attributes ) ) {
            foreach ( $attributes as $key => $name ) {
                $value = '';
                if ( in_array( $key, array( 'width', 'height', 'weight', 'length' ) )  ) {
                    $value = call_user_func( array( $product, 'get_' . $key ) );
                    if ( '' !== $value ) {
                        if ( 'weight' == $key ) {
                            $value .= get_option( 'woocommerce_weight_unit' );
                        } else {
                            $value .= get_option( 'woocommerce_dimension_unit' );
                        }
                    }
                } else {
                    if ( strpos( $key, 'pa_' ) !== 0 ) { // Custom Attribute on Product Edit Page
                        $value = $pd_attributes[$key]->get_options();
                    } else {
                        $value = wc_get_product_terms( $product->get_id(), $key, array( 'fields' => 'names' ) );
                    }
                }
                if ( ! empty( $value ) ) {
                    $not_empty = true;
                    ?>
                    <tr class="porto-attr-data">
                        <th><span class="porto-attr-name"><?php echo wc_attribute_label( $name ); ?></span></th>
                        <td><span class="porto-attr-term"><?php echo esc_html( is_array( $value ) ?  implode( ', ', $value ) : $value ); ?></span></td>
                    </tr>
                    <?php }
                }
        }?>
	</tbody>
</table>
<?php
$res = ob_get_clean();
if ( $not_empty ) {
    echo porto_filter_output( $res );
}
