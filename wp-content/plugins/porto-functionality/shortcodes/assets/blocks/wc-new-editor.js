(() => {
    "use strict";
    if ( wc && wc.adminLayout && porto_wc_product_editor && porto_wc_product_editor.product_url ) {
        function EditWithPortoButton() {
            var _adminLayout = wc.adminLayout;
            return(
                <_adminLayout.WooHeaderItem name="product">
                    <a href={ porto_wc_product_editor.product_url } target="_blank" className="porto-edit-builder" style={ { marginTop: 0, marginBottom: 0 } }>
                        <i class="porto-icon-studio"></i>
                        { wp.i18n.__( 'Build the layout with Product Builder', 'porto-functionality' ) }
                    </a>
                </_adminLayout.WooHeaderItem>
            );
        }

        if ( wp && wp.plugins && typeof wp.plugins.registerPlugin == 'function' ) {
            wp.plugins.registerPlugin( 'porto-builder-item', {
                render: EditWithPortoButton,
                scope: 'woocommerce-product-block-editor'
            });
        }
    }
})();