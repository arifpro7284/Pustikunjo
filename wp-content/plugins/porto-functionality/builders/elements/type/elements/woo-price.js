/**
 * Post Type Builder - WooCommerce Price
 * 
 * @since 2.3.0
 */
import PortoStyleOptionsControl, {portoGenerateStyleOptionsCSS} from '../../../../shortcodes/assets/blocks/controls/style-options';
import PortoTypographyControl, {portoGenerateTypographyCSS} from '../../../../shortcodes/assets/blocks/controls/typography';
import {portoAddHelperClasses} from '../../../../shortcodes/assets/blocks/controls/editor-extra-classes';

( function ( wpI18n, wpBlocks, wpBlockEditor, wpComponents ) {
    "use strict";

    const __ = wpI18n.__,
        registerBlockType = wpBlocks.registerBlockType,
        InspectorControls = wpBlockEditor.InspectorControls,
        useBlockProps = wpBlockEditor.useBlockProps,
        SelectControl = wpComponents.SelectControl,
        TextControl = wpComponents.TextControl,
        RangeControl = wpComponents.RangeControl,
        ToggleControl = wpComponents.ToggleControl,
        Disabled = wpComponents.Disabled,
        PanelBody = wpComponents.PanelBody,
        ServerSideRender = wp.serverSideRender,
        useEffect = wp.element.useEffect,
        useState = wp.element.useState;

    const PortoTBWooPrice = function ( { attributes, setAttributes, name, clientId } ) {
        const [firstRenderForClone, setRenderClone] = useState(false);
        useEffect(
            () => {
                if ( ! attributes.el_class || -1 !== porto_tb_ids.indexOf( attributes.el_class ) ) { // new or just cloned
                    let new_cls = 'porto-tb-woo-price-' + Math.ceil( Math.random() * 10000 );
                    attributes.el_class = new_cls;
                    setAttributes( { el_class: new_cls } );
                }
                setRenderClone( prev => true );
                porto_tb_ids.push( attributes.el_class );

                return () => {
                    let arr_index = porto_tb_ids.indexOf( attributes.el_class );
                    if ( -1 !== arr_index ) {
                        porto_tb_ids.splice( arr_index, 1 );
                    }
                }
            },
            [],
        );

        let style_options = {};
        if ( typeof attributes.style_options != 'undefined' ) {
            Object.keys( attributes.style_options ).forEach( function( key ) {
                if ( typeof attributes.style_options[key] == 'object' ) {
                    style_options[key] = Object.assign( {}, attributes.style_options[key] );
                } else {
                    style_options[key] = attributes.style_options[key];
                }
            });
        }

        let attrs = Object.assign( {}, { el_class: attributes.el_class, className: attributes.className, font_settings: attributes.font_settings, style_options: style_options, ...style_options } );
        if ( porto_content_type ) {
            attrs.content_type = porto_content_type;
            if ( porto_content_type_value ) {
                attrs.content_type_value = porto_content_type_value;
            }
        }

        let internalStyle = '',
            font_settings = Object.assign( {}, attributes.font_settings );

        let selectorCls = 'tb-woo-price';
        if ( attributes.el_class ) {
            selectorCls = attributes.el_class;
        }

        if ( attributes.alignment || attributes.font_settings ) {
            let fontAtts = attributes.font_settings;
            fontAtts.alignment = attributes.alignment;

            internalStyle += portoGenerateTypographyCSS( fontAtts, selectorCls + ' .price' );
        }

        // add helper classes to parent block element
        if ( attributes.className ) {
            portoAddHelperClasses( attributes.className, clientId );
        }

        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={ __( 'General', 'porto-functionality' ) }>
                        <SelectControl
                            label={ __( 'Alignment', 'porto-functionality' ) }
                            value={ attributes.alignment }
                            options={ [ { 'label': __( 'Inherit', 'porto-functionality' ), 'value': '' }, { 'label': __( 'Left', 'porto-functionality' ), 'value': 'left' }, { 'label': __( 'Center', 'porto-functionality' ), 'value': 'center' }, { 'label': __( 'Right', 'porto-functionality' ), 'value': 'right' }, { 'label': __( 'Justify', 'porto-functionality' ), 'value': 'justify' } ] }
                            onChange={ ( value ) => { setAttributes( { alignment: value } ); } }
                        />
                    </PanelBody>
                </InspectorControls>
                <InspectorControls group="styles">
                    <PanelBody title={ __( 'Font Settings', 'porto-functionality' ) }>
                        <PortoTypographyControl
                            label={ __( 'Typography', 'porto-functionality' ) }
                            value={ font_settings }
                            options={ { textAlign: false } }
                            onChange={ ( value ) => {
                                setAttributes( { font_settings: value } );
                            } }
                            removeHoverLinkClr = { true }
                        />
                    </PanelBody>
                    <PortoStyleOptionsControl
                        label={ __( 'Style Options', 'porto-functionality' ) }
                        value={ style_options }
                        options={ {} }
                        onChange={ ( value ) => { setAttributes( { style_options: value } ); } }
                    />
                </InspectorControls>
                <div { ...blockProps }>
                <Disabled>
                    <style>
                        { internalStyle }
                        { portoGenerateStyleOptionsCSS( style_options, selectorCls ) }
                    </style>
                    { firstRenderForClone && <ServerSideRender
                        block={ name }
                        attributes={ attrs }
                    /> }
                </Disabled>
                </div>
            </>
        )
    }
    registerBlockType( 'porto-tb/porto-woo-price', {
        apiVersion: 3,
        title: __( 'Woo Price', 'porto-functionality' ),
        icon: 'porto',
        category: 'porto-tb',
        keywords: [ 'type builder', 'mini', 'card', 'post', 'woocommerce', 'product', 'currency' ],
        description: __( 'Display the price of a product', 'porto-functionality' ),
        attributes: {
            content_type: {
                type: 'string',
            },
            content_type_value: {
                type: 'string',
            },
            alignment: {
                type: 'string',
            },
            font_settings: {
                type: 'object',
                default: {},
            },
            style_options: {
                type: 'object',
            },
            el_class: {
                type: 'string',
            }
        },
        edit: PortoTBWooPrice,
        save: function () {
            return null;
        }
    } );
} )( wp.i18n, wp.blocks, wp.blockEditor, wp.components );