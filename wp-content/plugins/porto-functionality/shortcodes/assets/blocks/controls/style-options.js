import portoNext40 from './next40';
import PortoAdvancedDimensionControl, { portoGenerateAdvancedDimensionCSS } from './advanced-dimension';
import PortoAdvancedRangeControl, { portoGenerateAdvancedRangeCSS } from './advanced-range';

const PortoStyleOptionsControl = function( {
	label,
	value,
	options,
	onChange
} ) {
	const __ = wp.i18n.__,
		TextControl = portoNext40( wp.components.TextControl ),
		SelectControl = portoNext40( wp.components.SelectControl ),
		UnitControl = wp.components.__experimentalUnitControl,
		RangeControl = portoNext40( wp.components.RangeControl ),
		PanelBody = wp.components.PanelBody,
		IconButton = wp.components.Button,
		ToggleControl = wp.components.ToggleControl,
		TabPanel = wp.components.TabPanel,
		ToggleGroupControl = portoNext40( wp.components.__experimentalToggleGroupControl ),
		ToggleGroupControlOption = wp.components.__experimentalToggleGroupControlOption,
		PanelColorSettings = wp.blockEditor.PanelColorSettings,
		MediaUpload = wp.blockEditor.MediaUpload;

	if ( !value ) {
		value = {};
	}

	const marginEnabled = !options || false !== options.margin,
		paddingEnabled = !options || false !== options.padding,
		positionEnabled = !options || false !== options.position,
		borderEnabled = !options || false !== options.border,
		bgEnabled = !options || false !== options.bg,
		visibilityEnabled = !options || false !== options.visibility,
		boxShadowEnabled = !options || false !== options.boxShadow,
		transformEnabled = !options || false !== options.transform;

	let positionDefaultVal = {};
	if ( value.position && value.position.offset ) {
		positionDefaultVal = value.position.offset;
	} else if ( value.position ) {
		if ( value.position.top ) {
			positionDefaultVal.unit = value.position.top.trim().replace( /[0-9.]/g, '' );
			positionDefaultVal.top = value.position.top.replace( positionDefaultVal.unit, '' );
		}
		if ( value.position.right ) {
			positionDefaultVal.unit = value.position.right.trim().replace( /[0-9.]/g, '' );
			positionDefaultVal.right = value.position.right.replace( positionDefaultVal.unit, '' );
		}
		if ( value.position.bottom ) {
			positionDefaultVal.unit = value.position.bottom.trim().replace( /[0-9.]/g, '' );
			positionDefaultVal.bottom = value.position.bottom.replace( positionDefaultVal.unit, '' );
		}
		if ( value.position.left ) {
			positionDefaultVal.unit = value.position.left.trim().replace( /[0-9.]/g, '' );
			positionDefaultVal.left = value.position.left.replace( positionDefaultVal.unit, '' );
		}
		if ( JSON.stringify(positionDefaultVal) !== '{}' ) {
			positionDefaultVal = { desktop: positionDefaultVal };
		}
	}

	const styleTabChildren = {
		normal: (
			<>
				{ bgEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Background', 'porto-functionality' ) }>
						<ToggleGroupControl
								value="normal"
								isBlock
								className="porto-tabs-control"
								onChange={ ( val ) => {
									
								} }
							>
							<ToggleGroupControlOption value="normal" label={ __( 'Normal', 'porto-functionality' ) } />
							<ToggleGroupControlOption value="hover" label={ __( 'Hover', 'porto-functionality' ) } />
						</ToggleGroupControl>
						<div className="porto-typography-control porto-dimension-control">
							<PanelColorSettings
								title={ __( 'Background', 'porto-functionality' ) }
								enableAlpha = { true }
								colorSettings={[
									{
										label: __( 'Color', 'porto-functionality' ),
										value: value.bg && value.bg.color,
										onChange: ( val ) => { 
											if ( !value.bg ) {
												value.bg = {};
											}
											value.bg.color = val;
											onChange( value );
										}
									},
								]}
							/>
							<MediaUpload
								allowedTypes={ ['image'] }
								value={ value.bg && value.bg.img_id }
								onSelect={ ( image ) => {
									if ( !value.bg ) {
										value.bg = {};
									}
									value.bg.img_url = image.url;
									value.bg.img_id = image.id;
									onChange( value );
								} }
								render={ ( _ref ) => {
									var open = _ref.open;
									return (
										<div>
											<IconButton
												className="components-toolbar__control"
												label={ __( 'Change image', 'porto-functionality' ) }
												icon="edit"
												onClick={ open }
											/>
											{ value.bg && value.bg.img_id && ( <IconButton
												className="components-toolbar__control"
												label={ __( 'Remove image', 'porto-functionality' ) }
												icon="no"
												onClick={ () => {
													if ( !value.bg ) {
														value.bg = {};
													}
													value.bg.img_url = undefined;
													value.bg.img_id = undefined;
													onChange( value );
												} }
											/>
											) }
											{ value.bg && value.bg.img_id && (
												<img src={ value.bg.img_url } className="components-upload-show-img"/>
											) }
										</div>
									);
								} }
							/>
							{ value.bg && value.bg.img_id && (
								<SelectControl
									label={ __( 'Position', 'porto-functionality' ) }
									value={ value.bg && value.bg.position }
									options={ [{ label: __( 'Default', 'porto-functionality' ), value: '' }, { label: __( 'Center Center', 'porto-functionality' ), value: 'center center' }, { label: __( 'Center Left', 'porto-functionality' ), value: 'center left' }, { label: __( 'Center Right', 'porto-functionality' ), value: 'center right' }, { label: __( 'Top Center', 'porto-functionality' ), value: 'top center' }, { label: __( 'Top Left', 'porto-functionality' ), value: 'top left' }, { label: __( 'Top Right', 'porto-functionality' ), value: 'top right' }, { label: __( 'Bottom Center', 'porto-functionality' ), value: 'bottom center' }, { label: __( 'Bottom Left', 'porto-functionality' ), value: 'bottom left' }, { label: __( 'Bottom Right', 'porto-functionality' ), value: 'bottom right' }] }
									onChange={ ( val ) => {
										if ( !value.bg ) {
											value.bg = {};
										}
										value.bg.position = val;
										onChange( value );
									} }
								/>
							) }
							{ value.bg && value.bg.img_id && (
								<SelectControl
									label={ __( 'Attachment', 'porto-functionality' ) }
									value={ value.bg && value.bg.attachment }
									options={ [{ label: __( 'Default', 'porto-functionality' ), value: '' }, { label: __( 'Scroll', 'porto-functionality' ), value: 'scroll' }, { label: __( 'Fixed' ), value: 'fixed' }] }
									onChange={ ( val ) => {
										if ( !value.bg ) {
											value.bg = {};
										}
										value.bg.attachment = val;
										onChange( value );
									} }
								/>
							) }
							{ value.bg && value.bg.img_id && (
								<SelectControl
									label={ __( 'Repeat', 'porto-functionality' ) }
									value={ value.bg && value.bg.repeat }
									options={ [{ label: __( 'Default', 'porto-functionality' ), value: '' }, { label: __( 'No-repeat', 'porto-functionality' ), value: 'no-repeat' }, { label: __( 'Repeat', 'porto-functionality' ), value: 'repeat' }, { label: __( 'Repeat-x', 'porto-functionality' ), value: 'repeat-x' }, { label: __( 'Repeat-y', 'porto-functionality' ), value: 'repeat-y' }] }
									onChange={ ( val ) => {
										if ( !value.bg ) {
											value.bg = {};
										}
										value.bg.repeat = val;
										onChange( value );
									} }
								/>
							) }
							{ value.bg && value.bg.img_id && (
								<SelectControl
									label={ __( 'Size', 'porto-functionality' ) }
									value={ value.bg && value.bg.size }
									options={ [{ label: __( 'Default', 'porto-functionality' ), value: '' }, { label: __( 'Auto', 'porto-functionality' ), value: 'auto' }, { label: __( 'Cover', 'porto-functionality' ), value: 'cover' }, { label: __( 'Contain', 'porto-functionality' ), value: 'contain' }] }
									onChange={ ( val ) => {
										if ( !value.bg ) {
											value.bg = {};
										}
										value.bg.size = val;
										onChange( value );
									} }
								/>
							) }
						</div>
					</PanelBody>
				) }
				{ borderEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Border', 'porto-functionality' ) }>
						<div className="porto-typography-control porto-dimension-control">
							<h3 className="components-base-control" style={ { marginBottom: 15 } }>
								{ __( 'Border', 'porto-functionality' ) }
							</h3>
							<SelectControl
								label={ __( 'Style', 'porto-functionality' ) }
								value={ value.border && value.border.style }
								options={ [{ label: __( 'None', 'porto-functionality' ), value: '' }, { label: __( 'Solid', 'porto-functionality' ), value: 'solid' }, { label: __( 'Double', 'porto-functionality' ), value: 'double' }, { label: __( 'Dotted', 'porto-functionality' ), value: 'dotted' }, { label: __( 'Dashed', 'porto-functionality' ), value: 'dashed' }, { label: __( 'Groove', 'porto-functionality' ), value: 'groove' }] }
								onChange={ ( val ) => {
									if ( !value.border ) {
										value.border = {};
									}
									value.border.style = val;
									onChange( value );
								} }
							/>
							<PortoAdvancedDimensionControl
								label={ __( 'Width', 'porto-functionality' ) }
								value={ value.border && value.border.width }
								units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ] }
								defaultUnit="px"
								onChange={ ( val ) => {
									if ( !value.border ) {
										value.border = {};
									}
									value.border.width = val;
									onChange( value );
								} }
							/>
							<PanelColorSettings
								title={ __( 'Border', 'porto-functionality' ) }
								enableAlpha = { true }
								colorSettings={[
									{
										label: __( 'Color', 'porto-functionality' ),
										value: value.border && value.border.color,
										onChange: ( val ) => { 
											if ( !value.border ) {
												value.border = {};
											}
											value.border.color = val;
											onChange( value );
										}
									},
								]}
							/>
							<PortoAdvancedDimensionControl
								label={ __( 'Radius', 'porto-functionality' ) }
								value={ value.borderRadius }
								units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ] }
								defaultUnit="px"
								onChange={ ( val ) => {
									value.borderRadius = val;
									onChange( value );
								} }
							/>
						</div>
					</PanelBody>
				) }
				{ marginEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Margin', 'porto-functionality' ) }>
						<PortoAdvancedDimensionControl
							label={ __( 'Margin', 'porto-functionality' ) }
							value={ value.margin }
							units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ] }
							defaultUnit="px"
							onChange={ ( val ) => {
								value.margin = val;
								onChange( value );
							} }
						/>
					</PanelBody>
				) }
				{ paddingEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Padding', 'porto-functionality' ) }>
						<PortoAdvancedDimensionControl
							label={ __( 'Padding', 'porto-functionality' ) }
							value={ value.padding }
							units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ] }
							defaultUnit="px"
							onChange={ ( val ) => {
								value.padding = val;
								onChange( value );
							} }
						/>
					</PanelBody>
				) }
				{ positionEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Position', 'porto-functionality' ) }>
						<div className="porto-typography-control porto-dimension-control">
							<h3 className="components-base-control" style={ { marginBottom: 15 } }>
								{ __( 'Position', 'porto-functionality' ) }
							</h3>
							<SelectControl
								label={ __( 'Style', 'porto-functionality' ) }
								value={ value.position && value.position.style }
								options={ [{ label: __( 'Default', 'porto-functionality' ), value: '' }, { label: __( 'Static', 'porto-functionality' ), value: 'static' }, { label: __( 'Relative', 'porto-functionality' ), value: 'relative' }, { label: __( 'Absolute', 'porto-functionality' ), value: 'absolute' }, { label: __( 'Fixed', 'porto-functionality' ), value: 'fixed' }, { label: __( 'Sticky', 'porto-functionality' ), value: 'sticky' }] }
								onChange={ ( val ) => {
									if ( !value.position ) {
										value.position = {};
									}
									value.position.style = val;
									onChange( value );
								} }
							/>
							<PortoAdvancedRangeControl
								label={ __( 'Z-index', 'porto-functionality' ) }
								value={ value.position && typeof value.position.zindex != 'undefined' && value.position.zindex }
								allowUnit={ false }
								min={ -1 }
								max={ 9999 }
								step={ 1 }
								onChange={ ( val ) => {
									if ( !value.position ) {
										value.position = {};
									}
									value.position.zindex = val;
									onChange( value );
								} }
							/>
							<PortoAdvancedDimensionControl
								label={ __( 'Offset', 'porto-functionality' ) }
								value={ positionDefaultVal }
								units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ] }
								defaultUnit="px"
								defaultLinked={ false }
								onChange={ ( val ) => {
									if ( !value.position ) {
										value.position = {};
									}
									delete value.position.top;
									delete value.position.right;
									delete value.position.bottom;
									delete value.position.left;
									value.position.offset = val;
									onChange( value );
								} }
							/>
							<div className="mb-3" />
							<SelectControl
								label={ __( 'Width', 'porto-functionality' ) }
								value={ value.position && value.position.width }
								options={ [{ label: __( 'Default', 'porto-functionality' ), value: '' }, { label: __( 'Full Width (100%)', 'porto-functionality' ), value: '100%' }, { label: __( 'Inline (auto)', 'porto-functionality' ), value: 'auto' }, { label: __( 'Fit Content', 'porto-functionality' ), value: 'fit-content' }, { label: __( 'Custom', 'porto-functionality' ), value: 'custom' }] }
								onChange={ ( val ) => {
									if ( !value.position ) {
										value.position = {};
									}
									value.position.width = val;
									onChange( value );
								} }
							/>
							{ value.position && 'custom' === value.position.width && ( <PortoAdvancedRangeControl
								label={ __( 'Width', 'porto-functionality' ) }
								value={ value.position && value.position.width_val }
								units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh' ] }
								defaultUnit="px"
								range={ {
									px:   { min: 0, max: 1000, step: 1 },
									'%':  { min: 0, max: 100,  step: 1 },
									em:   { min: 0, max: 100,   step: 0.5 },
									rem:  { min: 0, max: 100,   step: 0.5 },
									vw:   { min: 0, max: 100,  step: 1 },
									vh:   { min: 0, max: 100,  step: 1 },
								} }
								onChange={ ( val ) => {
									if ( !value.position ) {
										value.position = {};
									}
									value.position.width_val = val;
									onChange( value );
								} }
							/> ) }
							<PortoAdvancedRangeControl
								label={ __( 'Height', 'porto-functionality' ) }
								value={ value.position && value.position.height_val }
								units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh' ] }
								defaultUnit="px"
								range={ {
									px:   { min: 0, max: 1000, step: 1 },
									'%':  { min: 0, max: 100,  step: 1 },
									em:   { min: 0, max: 100,   step: 0.5 },
									rem:  { min: 0, max: 100,   step: 0.5 },
									vw:   { min: 0, max: 100,  step: 1 },
									vh:   { min: 0, max: 100,  step: 1 },
								} }
								onChange={ ( val ) => {
									if ( !value.position ) {
										value.position = {};
									}
									value.position.height_val = val;
									onChange( value );
								} }
							/>
							{ value.position && 'custom' === value.position.width && (
								<div className="mb-3" />
							) }
							<SelectControl
								label={ __( 'Horizontal Align', 'porto-functionality' ) }
								help={ __( 'This only works in flex container.', 'porto-functionality' ) }
								value={ value.position && value.position.halign }
								options={ [{ label: __( 'Default', 'porto-functionality' ), value: '' }, { label: __( 'Center', 'porto-functionality' ), value: 'x' }, { label: __( 'Left', 'porto-functionality' ), value: 'r' }, { label: __( 'Right', 'porto-functionality' ), value: 'l' }] }
								onChange={ ( val ) => {
									if ( !value.position ) {
										value.position = {};
									}
									value.position.halign = val;
									onChange( value );
								} }
							/>
							<PortoAdvancedRangeControl
								label={ __( 'Opacity', 'porto-functionality' ) }
								value={ value.position && typeof value.position.opacity != 'undefined' && value.position.opacity }
								allowUnit={ false }
								min={ 0 }
								max={ 1 }
								step={ 0.01 }
								onChange={ ( val ) => {
									if ( !value.position ) {
										value.position = {};
									}
									value.position.opacity = val;
									onChange( value );
								} }
							/>
						</div>
					</PanelBody>
				) }
				{ transformEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Transform', 'porto-functionality' ) }>
						<div className="porto-typography-control porto-dimension-control">
							<h3 className="components-base-control" style={ { marginBottom: 15 } }>
								{ __( 'Transform', 'porto-functionality' ) }
							</h3>

							<ToggleControl
								label={ __( 'Translate', 'porto-functionality' ) }
								checked={ ( value.transform && value.transform.translate ) ? value.transform && value.transform.translate : false }
								onChange={ ( val ) => {
									if ( !value.transform ) {
										value.transform = {};
									}
									value.transform.translate = val;
									onChange( value );
								} }
							/>
							{ value.transform && value.transform.translate && (
								<div className="mb-3" style={ { display: 'flex', flexWrap: 'wrap', marginTop: -10 } }>
									<UnitControl
										label={ __( 'X', 'porto-functionality' ) }
										value={ value.transform && value.transform.translatex }
										onChange={ ( val ) => {
											if ( !value.transform ) {
												value.transform = {};
											}
											value.transform.translatex = val;
											onChange( value );
										} }
									/>
									<UnitControl
										label={ __( 'Y', 'porto-functionality' ) }
										value={ value.transform && value.transform.translatey }
										onChange={ ( val ) => {
											if ( !value.transform ) {
												value.transform = {};
											}
											value.transform.translatey = val;
											onChange( value );
										} }
									/>
								</div>
							) }

							<ToggleControl
								label={ __( 'Rotate', 'porto-functionality' ) }
								checked={ ( value.transform && value.transform.rotate ) ? value.transform && value.transform.rotate : false }
								onChange={ ( val ) => {
									if ( !value.transform ) {
										value.transform = {};
									}
									value.transform.rotate = val;
									onChange( value );
								} }
							/>
							{ value.transform && value.transform.rotate && (
								<div className="mb-3" style={ { marginTop: -10 } }>
									<RangeControl
										label={ __( 'Degree', 'porto-functionality' ) }
										value={ value.transform && value.transform.rotatedeg }
										min="-360"
										max="360"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.transform ) {
												value.transform = {};
											}
											value.transform.rotatedeg = val;
											onChange( value );
										} }
									/>
								</div>
							) }

							<ToggleControl
								label={ __( 'Scale', 'porto-functionality' ) }
								checked={ ( value.transform && value.transform.scale ) ? value.transform && value.transform.scale : false }
								onChange={ ( val ) => {
									if ( !value.transform ) {
										value.transform = {};
									}
									value.transform.scale = val;
									onChange( value );
								} }
							/>
							{ value.transform && value.transform.scale && (
								<div className="mb-3" style={ { marginTop: -10 } }>
									<RangeControl
										label={ __( 'X', 'porto-functionality' ) }
										value={ value.transform && value.transform.scalex }
										min="0"
										max="2"
										step="0.1"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.transform ) {
												value.transform = {};
											}
											value.transform.scalex = val;
											onChange( value );
										} }
									/>
									<RangeControl
										label={ __( 'Y', 'porto-functionality' ) }
										value={ value.transform && value.transform.scaley }
										min="0"
										max="2"
										step="0.1"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.transform ) {
												value.transform = {};
											}
											value.transform.scaley = val;
											onChange( value );
										} }
									/>
								</div>
							) }

							<ToggleControl
								label={ __( 'Skew', 'porto-functionality' ) }
								checked={ ( value.transform && value.transform.skew ) ? value.transform && value.transform.skew : false }
								onChange={ ( val ) => {
									if ( !value.transform ) {
										value.transform = {};
									}
									value.transform.skew = val;
									onChange( value );
								} }
							/>
							{ value.transform && value.transform.skew && (
								<div className="mb-3" style={ { marginTop: -10 } }>
									<RangeControl
										label={ __( 'X', 'porto-functionality' ) }
										value={ value.transform && value.transform.skewx }
										min="-360"
										max="360"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.transform ) {
												value.transform = {};
											}
											value.transform.skewx = val;
											onChange( value );
										} }
									/>
									<RangeControl
										label={ __( 'Y', 'porto-functionality' ) }
										value={ value.transform && value.transform.skewy }
										min="-360"
										max="360"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.transform ) {
												value.transform = {};
											}
											value.transform.skewy = val;
											onChange( value );
										} }
									/>
								</div>
							) }

							<ToggleControl
								label={ __( 'Flip Horizontal', 'porto-functionality' ) }
								checked={ ( value.transform && value.transform.flipx ) ? value.transform && value.transform.flipx : false }
								onChange={ ( val ) => {
									if ( !value.transform ) {
										value.transform = {};
									}
									value.transform.flipx = val;
									onChange( value );
								} }
							/>
							<ToggleControl
								label={ __( 'Flip Vertical', 'porto-functionality' ) }
								checked={ ( value.transform && value.transform.flipy ) ? value.transform && value.transform.flipy : false }
								onChange={ ( val ) => {
									if ( !value.transform ) {
										value.transform = {};
									}
									value.transform.flipy = val;
									onChange( value );
								} }
							/>
						</div>
					</PanelBody>
				) }
				{ boxShadowEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Box Shadow', 'porto-functionality' ) }>
						<div className="porto-typography-control porto-dimension-control">
							<h3 className="components-base-control" style={ { marginBottom: 15 } }>
								{ __( 'Box Shadow', 'porto-functionality' ) }
							</h3>
							<SelectControl
								label={ __( 'Type', 'porto-functionality' ) }
								value={ value.boxshadow && value.boxshadow.type }
								options={ [{ label: __( 'Outset', 'porto-functionality' ), value: '' }, { label: __( 'Inset', 'porto-functionality' ), value: 'inset' }, { label: __( 'None', 'porto-functionality' ), value: 'none' }, { label: __( 'Inherit', 'porto-functionality' ), value: 'inherit' }] }
								onChange={ ( val ) => {
									if ( !value.boxshadow ) {
										value.boxshadow = {};
									}
									value.boxshadow.type = val;
									onChange( value );
								} }
							/>
							<div className="mb-3" style={ { display: 'flex', flexWrap: 'wrap' } }>
								<UnitControl
									label={ __( 'X', 'porto-functionality' ) }
									value={ value.boxshadow && value.boxshadow.x }
									onChange={ ( val ) => {
										if ( !value.boxshadow ) {
											value.boxshadow = {};
										}
										value.boxshadow.x = val;
										onChange( value );
									} }
								/>
								<UnitControl
									label={ __( 'Y', 'porto-functionality' ) }
									value={ value.boxshadow && value.boxshadow.y }
									onChange={ ( val ) => {
										if ( !value.boxshadow ) {
											value.boxshadow = {};
										}
										value.boxshadow.y = val;
										onChange( value );
									} }
								/>
								<UnitControl
									label={ __( 'Blur', 'porto-functionality' ) }
									value={ value.boxshadow && value.boxshadow.blur }
									onChange={ ( val ) => {
										if ( !value.boxshadow ) {
											value.boxshadow = {};
										}
										value.boxshadow.blur = val;
										onChange( value );
									} }
								/>
								<UnitControl
									label={ __( 'Spread', 'porto-functionality' ) }
									value={ value.boxshadow && value.boxshadow.spread }
									onChange={ ( val ) => {
										if ( !value.boxshadow ) {
											value.boxshadow = {};
										}
										value.boxshadow.spread = val;
										onChange( value );
									} }
								/>
							</div>
							<PanelColorSettings
								title={ __( 'Box Shadow', 'porto-functionality' ) }
								enableAlpha = { true }
								colorSettings={[
									{
										label: __( 'Color', 'porto-functionality' ),
										value: value.boxshadow && value.boxshadow.color,
										onChange: ( val ) => { 
											if ( !value.boxshadow ) {
												value.boxshadow = {};
											}
											value.boxshadow.color = val;
											onChange( value );
										}
									},                                  
								]}
							/>
						</div>
					</PanelBody>
				) }
				{ visibilityEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Visibility', 'porto-functionality' ) }>
						<div className="porto-typography-control porto-dimension-control">
							<h3 className="components-base-control" style={ { marginBottom: 15 } }>
								{ __( 'Visibility', 'porto-functionality' ) }
							</h3>
							<p className="help">{ __( 'Visibility will take effect only on live page.', 'porto-functionality' ) }</p>
							<ToggleControl
								label={ __( 'Hide On Large Desktop', 'porto-functionality' ) }
								checked={ value.hideXl ? value.hideXl : false }
								onChange={ ( val ) => {
									value.hideXl = val;
									onChange( value );
								} }
							/>
							<ToggleControl
								label={ __( 'Hide On Desktop', 'porto-functionality' ) }
								checked={ value.hideLg ? value.hideLg : false }
								onChange={ ( val ) => {
									value.hideLg = val;
									onChange( value );
								} }
							/>
							<ToggleControl
								label={ __( 'Hide On Tablet', 'porto-functionality' ) }
								checked={ value.hideMd ? value.hideMd : false }
								onChange={ ( val ) => {
									value.hideMd = val;
									onChange( value );
								} }
							/>
							<ToggleControl
								label={ __( 'Hide On Mobile', 'porto-functionality' ) }
								checked={ value.hideSm ? value.hideSm : false }
								onChange={ ( val ) => {
									value.hideSm = val;
									onChange( value );
								} }
							/>
						</div>
					</PanelBody>
				) }
			</>
		)
	}
	if ( options && options.hoverOptions ) {
		styleTabChildren.hover = (
			<>
				<PanelBody initialOpen={ false } title={ __( 'Text, Background & Border', 'porto-functionality' ) }>
					<div className="porto-typography-control porto-dimension-control">
						<PanelColorSettings
							title={ __( 'Color Settings', 'porto-functionality' ) }
							enableAlpha = { true }
							colorSettings={[
								{
									label: __( 'Background Color', 'porto-functionality' ),
									value: value.hover && value.hover.bg,
									onChange: ( val ) => { 
										if ( !value.hover ) {
											value.hover = {};
										}
										value.hover.bg = val;
										onChange( value );
									}
								}, 
								{
									label: __( 'Text Color', 'porto-functionality' ),
									value: value.hover && value.hover.color,
									onChange: ( val ) => { 
										if ( !value.hover ) {
											value.hover = {};
										}
										value.hover.color = val;
										onChange( value );
									}
								}, 
								{
									label: __( 'Border Color', 'porto-functionality' ),
									value: value.hover && value.hover.border_color,
									onChange: ( val ) => { 
										if ( !value.hover ) {
											value.hover = {};
										}
										value.hover.border_color = val;
										onChange( value );
									}
								},
							]}
						/>
						<SelectControl
							label={ __( 'Border Style', 'porto-functionality' ) }
							value={ value.hover && value.hover.border_style }
							options={ [{ label: __( 'None', 'porto-functionality' ), value: '' }, { label: __( 'Solid', 'porto-functionality' ), value: 'solid' }, { label: __( 'Double', 'porto-functionality' ), value: 'double' }, { label: __( 'Dotted', 'porto-functionality' ), value: 'dotted' }, { label: __( 'Dashed', 'porto-functionality' ), value: 'dashed' }, { label: __( 'Groove', 'porto-functionality' ), value: 'groove' }] }
							onChange={ ( val ) => {
								if ( !value.hover ) {
									value.hover = {};
								}
								value.hover.border_style = val;
								onChange( value );
							} }
						/>
						<div style={ { display: 'flex', flexWrap: 'wrap' } }>
							<label style={ { width: '100%', marginBottom: 5 } }>
								{ __( 'Border Width', 'porto-functionality' ) }
							</label>
							<UnitControl
								label={ __( 'Top', 'porto-functionality' ) }
								value={ value.hover && value.hover.border_top }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									value.hover.border_top = val;
									onChange( value );
								} }
							/>
							<UnitControl
								label={ __( 'Right', 'porto-functionality' ) }
								value={ value.hover && value.hover.border_right }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									value.hover.border_right = val;
									onChange( value );
								} }
							/>
							<UnitControl
								label={ __( 'Bottom', 'porto-functionality' ) }
								value={ value.hover && value.hover.border_bottom }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									value.hover.border_bottom = val;
									onChange( value );
								} }
							/>
							<UnitControl
								label={ __( 'Left', 'porto-functionality' ) }
								value={ value.hover && value.hover.border_left }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									value.hover.border_left = val;
									onChange( value );
								} }
							/>
						</div>

						<div className="mb-3" style={ { display: 'flex', flexWrap: 'wrap' } }>
							<label style={ { width: '100%', marginBottom: 5 } }>
								{ __( 'Position', 'porto-functionality' ) }
							</label>
							<UnitControl
								label={ __( 'Top', 'porto-functionality' ) }
								value={ value.hover && value.hover.top }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									value.hover.top = val;
									onChange( value );
								} }
							/>
							<UnitControl
								label={ __( 'Right', 'porto-functionality' ) }
								value={ value.hover && value.hover.right }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									value.hover.right = val;
									onChange( value );
								} }
							/>
							<UnitControl
								label={ __( 'Bottom', 'porto-functionality' ) }
								value={ value.hover && value.hover.bottom }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									value.hover.bottom = val;
									onChange( value );
								} }
							/>
							<UnitControl
								label={ __( 'Left', 'porto-functionality' ) }
								value={ value.hover && value.hover.left }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									value.hover.left = val;
									onChange( value );
								} }
							/>
						</div>

						<RangeControl
							label={ __( 'Opacity', 'porto-functionality' ) }
							value={ value.hover && typeof value.hover.opacity != 'undefined' && value.hover.opacity }
							min="0"
							max="1"
							step="0.01"
							allowReset={ true }
							onChange={ ( val ) => {
								if ( !value.hover ) {
									value.hover = {};
								}
								value.hover.opacity = val;
								onChange( value );
							} }
						/>
					</div>
				</PanelBody>
				{ transformEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Transform', 'porto-functionality' ) }>
						<div className="porto-typography-control porto-dimension-control">
							<h3 className="components-base-control" style={ { marginBottom: 15 } }>
								{ __( 'Transform', 'porto-functionality' ) }
							</h3>

							<ToggleControl
								label={ __( 'Translate', 'porto-functionality' ) }
								checked={ ( value.hover && value.hover.transform && value.hover.transform.translate ) ? value.hover && value.hover.transform && value.hover.transform.translate : false }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									if ( !value.hover.transform ) {
										value.hover.transform = {};
									}
									value.hover.transform.translate = val;
									onChange( value );
								} }
							/>
							{ value.hover && value.hover.transform && value.hover.transform.translate && (
								<div className="mb-3" style={ { display: 'flex', flexWrap: 'wrap', marginTop: -10 } }>
									<UnitControl
										label={ __( 'X', 'porto-functionality' ) }
										value={ value.hover && value.hover.transform && value.hover.transform.translatex }
										onChange={ ( val ) => {
											if ( !value.hover ) {
												value.hover = {};
											}
											if ( !value.hover.transform ) {
												value.hover.transform = {};
											}
											value.hover.transform.translatex = val;
											onChange( value );
										} }
									/>
									<UnitControl
										label={ __( 'Y', 'porto-functionality' ) }
										value={ value.hover && value.hover.transform && value.hover.transform.translatey }
										onChange={ ( val ) => {
											if ( !value.hover ) {
												value.hover = {};
											}
											if ( !value.hover.transform ) {
												value.hover.transform = {};
											}
											value.hover.transform.translatey = val;
											onChange( value );
										} }
									/>
								</div>
							) }

							<ToggleControl
								label={ __( 'Rotate', 'porto-functionality' ) }
								checked={ ( value.hover && value.hover.transform && value.hover.transform.rotate ) ? value.hover && value.hover.transform && value.hover.transform.rotate : false }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									if ( !value.hover.transform ) {
										value.hover.transform = {};
									}
									value.hover.transform.rotate = val;
									onChange( value );
								} }
							/>
							{ value.hover && value.hover.transform && value.hover.transform.rotate && (
								<div className="mb-3" style={ { marginTop: -10 } }>
									<RangeControl
										label={ __( 'Degree', 'porto-functionality' ) }
										value={ value.hover && value.hover.transform && value.hover.transform.rotatedeg }
										min="-360"
										max="360"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.hover ) {
												value.hover = {};
											}
											if ( !value.hover.transform ) {
												value.hover.transform = {};
											}
											value.hover.transform.rotatedeg = val;
											onChange( value );
										} }
									/>
								</div>
							) }

							<ToggleControl
								label={ __( 'Scale', 'porto-functionality' ) }
								checked={ ( value.hover && value.hover.transform && value.hover.transform.scale ) ? value.hover && value.hover.transform && value.hover.transform.scale : false }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									if ( !value.hover.transform ) {
										value.hover.transform = {};
									}
									value.hover.transform.scale = val;
									onChange( value );
								} }
							/>
							{ value.hover && value.hover.transform && value.hover.transform.scale && (
								<div className="mb-3" style={ { marginTop: -10 } }>
									<RangeControl
										label={ __( 'X', 'porto-functionality' ) }
										value={ value.hover && value.hover.transform && value.hover.transform.scalex }
										min="0"
										max="2"
										step="0.1"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.hover ) {
												value.hover = {};
											}
											if ( !value.hover.transform ) {
												value.hover.transform = {};
											}
											value.hover.transform.scalex = val;
											onChange( value );
										} }
									/>
									<RangeControl
										label={ __( 'Y', 'porto-functionality' ) }
										value={ value.hover && value.hover.transform && value.hover.transform.scaley }
										min="0"
										max="2"
										step="0.1"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.hover ) {
												value.hover = {};
											}
											if ( !value.hover.transform ) {
												value.hover.transform = {};
											}
											value.hover.transform.scaley = val;
											onChange( value );
										} }
									/>
								</div>
							) }

							<ToggleControl
								label={ __( 'Skew', 'porto-functionality' ) }
								checked={ ( value.hover && value.hover.transform && value.hover.transform.skew ) ? value.hover && value.hover.transform && value.hover.transform.skew : false }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									if ( !value.hover.transform ) {
										value.hover.transform = {};
									}
									value.hover.transform.skew = val;
									onChange( value );
								} }
							/>
							{ value.hover && value.hover.transform && value.hover.transform.skew && (
								<div className="mb-3" style={ { marginTop: -10 } }>
									<RangeControl
										label={ __( 'X', 'porto-functionality' ) }
										value={ value.hover && value.hover.transform && value.hover.transform.skewx }
										min="-360"
										max="360"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.hover ) {
												value.hover = {};
											}
											if ( !value.hover.transform ) {
												value.hover.transform = {};
											}
											value.hover.transform.skewx = val;
											onChange( value );
										} }
									/>
									<RangeControl
										label={ __( 'Y', 'porto-functionality' ) }
										value={ value.hover && value.hover.transform && value.hover.transform.skewy }
										min="-360"
										max="360"
										allowReset="true"
										onChange={ ( val ) => {
											if ( !value.hover ) {
												value.hover = {};
											}
											if ( !value.hover.transform ) {
												value.hover.transform = {};
											}
											value.hover.transform.skewy = val;
											onChange( value );
										} }
									/>
								</div>
							) }

							<ToggleControl
								label={ __( 'Flip Horizontal', 'porto-functionality' ) }
								checked={ ( value.hover && value.hover.transform && value.hover.transform.flipx ) ? value.hover && value.hover.transform && value.hover.transform.flipx : false }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									if ( !value.hover.transform ) {
										value.hover.transform = {};
									}
									value.hover.transform.flipx = val;
									onChange( value );
								} }
							/>
							<ToggleControl
								label={ __( 'Flip Vertical', 'porto-functionality' ) }
								checked={ ( value.hover && value.hover.transform && value.hover.transform.flipy ) ? value.hover && value.hover.transform && value.hover.transform.flipy : false }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									if ( !value.hover.transform ) {
										value.hover.transform = {};
									}
									value.hover.transform.flipy = val;
									onChange( value );
								} }
							/>
							<RangeControl
								label={ __( 'Transition Duration (ms)', 'porto-functionality' ) }
								value={ value.transform && value.transform.duration }
								min="0"
								max="2000"
								step="10"
								allowReset="true"
								onChange={ ( val ) => {
									if ( !value.transform ) {
										value.transform = {};
									}
									value.transform.duration = val;
									onChange( value );
								} }
							/>
						</div>
					</PanelBody>
				) }
				{ boxShadowEnabled && (
					<PanelBody initialOpen={ false } title={ __( 'Box Shadow', 'porto-functionality' ) }>
						<div className="porto-typography-control porto-dimension-control">
							<h3 className="components-base-control" style={ { marginBottom: 15 } }>
								{ __( 'Box Shadow', 'porto-functionality' ) }
							</h3>
							<SelectControl
								label={ __( 'Type', 'porto-functionality' ) }
								value={ value.hover && value.hover.boxshadow && value.hover.boxshadow.type }
								options={ [{ label: __( 'Outset', 'porto-functionality' ), value: '' }, { label: __( 'Inset', 'porto-functionality' ), value: 'inset' }, { label: __( 'None', 'porto-functionality' ), value: 'none' }, { label: __( 'Inherit', 'porto-functionality' ), value: 'inherit' }] }
								onChange={ ( val ) => {
									if ( !value.hover ) {
										value.hover = {};
									}
									if ( !value.hover.boxshadow ) {
										value.hover.boxshadow = {};
									}
									value.hover.boxshadow.type = val;
									onChange( value );
								} }
							/>
							<div className="mb-3" style={ { display: 'flex', flexWrap: 'wrap' } }>
								<UnitControl
									label={ __( 'X', 'porto-functionality' ) }
									value={ value.hover && value.hover.boxshadow && value.hover.boxshadow.x }
									onChange={ ( val ) => {
										if ( !value.hover ) {
											value.hover = {};
										}
										if ( !value.hover.boxshadow ) {
											value.hover.boxshadow = {};
										}
										value.hover.boxshadow.x = val;
										onChange( value );
									} }
								/>
								<UnitControl
									label={ __( 'Y', 'porto-functionality' ) }
									value={ value.hover && value.hover.boxshadow && value.hover.boxshadow.y }
									onChange={ ( val ) => {
										if ( !value.hover ) {
											value.hover = {};
										}
										if ( !value.hover.boxshadow ) {
											value.hover.boxshadow = {};
										}
										value.hover.boxshadow.y = val;
										onChange( value );
									} }
								/>
								<UnitControl
									label={ __( 'Blur', 'porto-functionality' ) }
									value={ value.hover && value.hover.boxshadow && value.hover.boxshadow.blur }
									onChange={ ( val ) => {
										if ( !value.hover ) {
											value.hover = {};
										}
										if ( !value.hover.boxshadow ) {
											value.hover.boxshadow = {};
										}
										value.hover.boxshadow.blur = val;
										onChange( value );
									} }
								/>
								<UnitControl
									label={ __( 'Spread', 'porto-functionality' ) }
									value={ value.hover && value.hover.boxshadow && value.hover.boxshadow.spread }
									onChange={ ( val ) => {
										if ( !value.hover ) {
											value.hover = {};
										}
										if ( !value.hover.boxshadow ) {
											value.hover.boxshadow = {};
										}
										value.hover.boxshadow.spread = val;
										onChange( value );
									} }
								/>
							</div>
							<PanelColorSettings
								title={ __( 'Box Shadow', 'porto-functionality' ) }
								enableAlpha = { true }
								colorSettings={[
									{
										label: __( 'Color', 'porto-functionality' ),
										value: value.hover && value.hover.boxshadow && value.hover.boxshadow.color,
										onChange: ( val ) => { 
											if ( !value.hover ) {
												value.hover = {};
											}
											if ( !value.hover.boxshadow ) {
												value.hover.boxshadow = {};
											}
											value.hover.boxshadow.color = val;
											onChange( value );
										}
									},                                  
								]}
							/>
						</div>
					</PanelBody>
				) }
			</>
		)

	}
	return (
		options && options.hoverOptions ? ( <TabPanel
			className="tab-panel-full-width"
			tabs={ [
				{
					name: 'normal',
					title: __( 'Normal', 'porto-functionality' ),
				},
				{
					name: 'hover',
					title: __( 'Hover', 'porto-functionality' ),
				},
			] }
		>
        	{ ( tab ) => ( styleTabChildren[tab.name] ) }
    	</TabPanel> )
		: styleTabChildren.normal
	);
};

export default PortoStyleOptionsControl;

export const portoGenerateStyleOptionsCSS = function( style_options, selector, clientId = -1 ) {
	var css = '';
	if ( !style_options || !selector ) {
		return '';
	}

	if ( 0 !== selector.indexOf( '#' ) && 0 !== selector.indexOf( '.' ) ) {
		if ( -1 === selector.indexOf( ' ' ) ) {
			selector = '.editor-styles-wrapper .' + selector;
		} else {
			selector = 'html .' + selector;
		}
	} else if ( 0 === selector.indexOf( '.' ) ) {
		if ( -1 === selector.indexOf( ' ' ) ) {
			selector = '.editor-styles-wrapper ' + selector;
		} else {
			selector = 'html ' + selector;
		}
	}

	const options = {
		bg: {
			color: 'background-color',
			img_url: 'background-image',
			position: 'background-position',
			attachment: 'background-attachment',
			repeat: 'background-repeat',
			size: 'background-size',
		},
		border: {
			color: 'border-color',
			style: 'border-style',
		},
	},
		hover_options = {
			bg: 'background-color',
			color: 'color',
			border_style: 'border-style',
			border_top: 'border-top-width',
			border_right: 'border-right-width',
			border_bottom: 'border-bottom-width',
			border_left: 'border-left-width',
			border_color: 'border-color',
			opacity: 'opacity'
		};

	css += selector + '{';
	_.each( options, function( item, property ) {
		if ( typeof style_options[property] != 'undefined' && style_options[property] ) {
			_.each( item, function( css_property, attr_name ) {
				if ( typeof style_options[property][attr_name] != 'undefined' && ( '' + style_options[property][attr_name] ).length ) {
					var val = style_options[property][attr_name];
					if ( 'background-image' == css_property ) {
						val = 'url(' + val + ')';
					}
					css += css_property + ':' + val + ';';
				}
			} );
		}
	} );
	if ( style_options.position ) {
		if ( style_options.position.translatex || style_options.position.translatey ) {
			css += 'transform:';
			if ( style_options.position.translatex ) {
				css += ' translateX(' + style_options.position.translatex + ')';
			}
			if ( style_options.position.translatey ) {
				css += ' translateY(' + style_options.position.translatey + ')';
			}
			css += ';';
		}
	}
	if ( style_options.transform ) {
		let transform_css = '';
		if ( style_options.transform.translate ) {
			if ( style_options.transform.translatex && style_options.transform.translatey ) {
				transform_css += ' translate(' + style_options.transform.translatex + ', ' + style_options.transform.translatey + ')';
			} else if ( style_options.transform.translatex ) {
				transform_css += ' translateX(' + style_options.transform.translatex + ')';
			} else if ( style_options.transform.translatey ) {
				transform_css += ' translateY(' + style_options.transform.translatey + ')';
			}
		}
		if ( style_options.transform.rotate && style_options.transform.rotatedeg ) {
			transform_css += ' rotate(' + style_options.transform.rotatedeg + 'deg)';
		}
		if ( style_options.transform.scale || style_options.transform.flipx || style_options.transform.flipy ) {
			let scaleX = style_options.transform.scalex,
				scaleY = style_options.transform.scaley;
			if ( style_options.transform.flipx ) {
				if ( scaleX ) {
					scaleX *= -1;
				} else {
					scaleX = -1;
				}
			}
			if ( style_options.transform.flipy ) {
				if ( scaleY ) {
					scaleY *= -1;
				} else {
					scaleY = -1;
				}
			}
			if ( scaleX && scaleY ) {
				transform_css += ' scale(' + scaleX + ', ' + scaleY + ')';
			} else if ( scaleX ) {
				transform_css += ' scaleX(' + scaleX + ')';
			} else if ( scaleY ) {
				transform_css += ' scaleY(' + scaleY + ')';
			}
		}
		if ( style_options.transform.skew ) {
			if ( style_options.transform.skewx && style_options.transform.skewy ) {
				transform_css += ' skew(' + style_options.transform.skewx + 'deg, ' + style_options.transform.skewy + 'deg)';
			} else if ( style_options.transform.skewx ) {
				transform_css += ' skewX(' + style_options.transform.skewx + 'deg)';
			} else if ( style_options.transform.skewy ) {
				transform_css += ' skewY(' + style_options.transform.skewy + 'deg)';
			}
		}
		if ( transform_css ) {
			css += 'transform:' + transform_css + ';';
		}
		if ( style_options && style_options.transform && style_options.transform.duration ) {
			css += 'transition:' + style_options.transform.duration + 'ms;';
		}
	}
	if ( style_options.boxshadow && ( style_options.boxshadow.type || ( style_options.boxshadow.color ) ) ) {
		css += 'box-shadow:';
		if ( style_options.boxshadow.type && 'inset' != style_options.boxshadow.type ) {
			css += style_options.boxshadow.type;
		} else {
			if ( style_options.boxshadow.type ) {
				css += style_options.boxshadow.type;
			}
			if ( style_options.boxshadow.x ) {
				css += ' ' + style_options.boxshadow.x;
			} else {
				css += ' 0';
			}
			if ( style_options.boxshadow.y ) {
				css += ' ' + style_options.boxshadow.y;
			} else {
				css += ' 0';
			}
			if ( style_options.boxshadow.blur ) {
				css += ' ' + style_options.boxshadow.blur;
			}
			if ( style_options.boxshadow.spread ) {
				css += ' ' + style_options.boxshadow.spread;
			}
			if ( style_options.boxshadow.color ) {
				css += ' ' + style_options.boxshadow.color;
			}
		}
		css += ';';
	}

	if ( style_options.border && ! style_options.border.width ) { // legacy border width
		const borderOptions = {
			top: 'border-top-width',
			right: 'border-right-width',
			bottom: 'border-bottom-width',
			left: 'border-left-width',
		};
		_.each( borderOptions, function( css_property, attr_name ) {
			if ( typeof style_options['border'][attr_name] != 'undefined' && ( '' + style_options['border'][attr_name] ).length ) {
				var val = style_options['border'][attr_name];
				css += css_property + ':' + val + ';';
			}
		} );
	}

	if ( style_options.borderRadius ) { // legacy border radius
		const borderRadiusOptions = {
			top: 'border-top-left-radius',
			right: 'border-top-right-radius',
			bottom: 'border-bottom-right-radius',
			left: 'border-bottom-left-radius',
		};
		_.each( borderRadiusOptions, function( css_property, attr_name ) {
			if ( typeof style_options['borderRadius'][attr_name] != 'undefined' && ( '' + style_options['borderRadius'][attr_name] ).length ) {
				var val = style_options['borderRadius'][attr_name];
				css += css_property + ':' + val + ';';
			}
		} );
	}

	css += '}';

	if ( style_options.border && style_options.border.width ) {
		css += portoGenerateAdvancedDimensionCSS( style_options.border.width, selector, 'border-width' );
	}

	if ( style_options.borderRadius ) {
		css += portoGenerateAdvancedDimensionCSS( style_options.borderRadius, selector, 'border-radius' );
	}

	if ( style_options.margin ) {
		css += portoGenerateAdvancedDimensionCSS( style_options.margin, clientId == -1 ? selector : '#block-' + clientId, 'margin' );
	}
	if ( style_options.padding ) {
		css += portoGenerateAdvancedDimensionCSS( style_options.padding, selector, 'padding' );
	}
	if ( style_options.position ) {
		let positionSelector;
		if ( clientId == -1 ) {
			positionSelector = selector;
		} else {
			positionSelector = '#block-' + clientId;
		}

		if ( 'custom' == style_options['position']['width'] ) {
			css += portoGenerateAdvancedRangeCSS( style_options.position.width_val, positionSelector, 'width' );
		}
		css += portoGenerateAdvancedRangeCSS( style_options.position.height_val, positionSelector, 'height' );
		css += portoGenerateAdvancedRangeCSS( style_options.position.height_val, positionSelector, '--porto-height' );


		var positionCss = '';
		const positionOptions = {
			style: 'position',
			width: 'width',
		};
		if ( '' == style_options['position']['width'] || 'custom' == style_options['position']['width'] ) {
			delete positionOptions['width'];
		}
		_.each( positionOptions, function( css_property, attr_name ) {
			if ( typeof style_options['position'][attr_name] != 'undefined' && ( '' + style_options['position'][attr_name] ).length ) {
				var val = style_options['position'][attr_name];
				positionCss += css_property + ':' + val + ';';
			}
		} );

		if ( ! style_options.position.offset ) {
			const posOptions = {
				top: 'top',
				right: 'right',
				bottom: 'bottom',
				left: 'left',
			}
			_.each( posOptions, function( css_property, attr_name ) {
				if ( typeof style_options['position'][attr_name] != 'undefined' && ( '' + style_options['position'][attr_name] ).length ) {
					var val = style_options['position'][attr_name];
					positionCss += css_property + ':' + val + ';';
				}
			} );
		}

		if ( style_options.position.halign ) {
			if ( 'x' === style_options.position.halign ) {
				positionCss += 'margin-left:auto;margin-right:auto;';
			} else if ( 'l' === style_options.position.halign ) {
				positionCss += 'margin-left:auto;';
			} else if ( 'r' === style_options.position.halign ) {
				positionCss += 'margin-right:auto;';
			}
		}

		if ( positionCss ) {
			css += positionSelector + '{';
			css += positionCss;
			css += '}';
		}

		css += portoGenerateAdvancedRangeCSS( style_options.position.zindex, positionSelector, 'z-index' );
		css += portoGenerateAdvancedDimensionCSS( style_options.position.offset, positionSelector, '' );
		css += portoGenerateAdvancedRangeCSS( style_options.position.opacity, positionSelector, 'opacity' );

		if ( clientId != -1 ) {
			css += selector + '{display: block' + '}';
		}
	}

	if ( style_options.hover ) {
		css += selector + ':hover{';
		_.each( hover_options, function( css_property, attr_name ) {
			if ( typeof style_options.hover[attr_name] != 'undefined' && ( '' + style_options.hover[attr_name] ).length ) {
				css += css_property + ':' + style_options.hover[attr_name] + ';';
			}
		} );
		if ( style_options.hover.translatex || style_options.hover.translatey ) {
			css += 'transform:';
			if ( style_options.hover.translatex ) {
				css += ' translateX(' + style_options.hover.translatex + ')';
			}
			if ( style_options.hover.translatey ) {
				css += ' translateY(' + style_options.hover.translatey + ')';
			}
			css += ';';
		}

		if ( style_options.hover.transform ) {
			let transform_css = '';
			if ( style_options.hover.transform.translate ) {
				if ( style_options.hover.transform.translatex && style_options.hover.transform.translatey ) {
					transform_css += ' translate(' + style_options.hover.transform.translatex + ', ' + style_options.hover.transform.translatey + ')';
				} else if ( style_options.hover.transform.translatex ) {
					transform_css += ' translateX(' + style_options.hover.transform.translatex + ')';
				} else if ( style_options.hover.transform.translatey ) {
					transform_css += ' translateY(' + style_options.hover.transform.translatey + ')';
				}
			}
			if ( style_options.hover.transform.rotate && style_options.hover.transform.rotatedeg ) {
				transform_css += ' rotate(' + style_options.hover.transform.rotatedeg + 'deg)';
			}
			if ( style_options.hover.transform.scale || style_options.hover.transform.flipx || style_options.hover.transform.flipy ) {
				let scaleX = style_options.hover.transform.scalex,
					scaleY = style_options.hover.transform.scaley;
				if ( style_options.hover.transform.flipx ) {
					if ( scaleX ) {
						scaleX *= -1;
					} else {
						scaleX = -1;
					}
				}
				if ( style_options.hover.transform.flipy ) {
					if ( scaleY ) {
						scaleY *= -1;
					} else {
						scaleY = -1;
					}
				}
				if ( scaleX && scaleY ) {
					transform_css += ' scale(' + scaleX + ', ' + scaleY + ')';
				} else if ( scaleX ) {
					transform_css += ' scaleX(' + scaleX + ')';
				} else if ( scaleY ) {
					transform_css += ' scaleY(' + scaleY + ')';
				}
			}
			if ( style_options.hover.transform.skew ) {
				if ( style_options.hover.transform.skewx && style_options.hover.transform.skewy ) {
					transform_css += ' skew(' + style_options.hover.transform.skewx + 'deg, ' + style_options.hover.transform.skewy + 'deg)';
				} else if ( style_options.hover.transform.skewx ) {
					transform_css += ' skewX(' + style_options.hover.transform.skewx + 'deg)';
				} else if ( style_options.hover.transform.skewy ) {
					transform_css += ' skewY(' + style_options.hover.transform.skewy + 'deg)';
				}
			}
			if ( transform_css ) {
				css += 'transform:' + transform_css + ';';
			}
		}

		if ( style_options.hover.boxshadow && ( style_options.hover.boxshadow.type || ( style_options.hover.boxshadow.color ) ) ) {
			css += 'box-shadow:';
			if ( style_options.hover.boxshadow.type && 'inset' != style_options.hover.boxshadow.type ) {
				css += style_options.hover.boxshadow.type;
			} else {
				if ( style_options.hover.boxshadow.type ) {
					css += style_options.hover.boxshadow.type;
				}
				if ( style_options.hover.boxshadow.x ) {
					css += ' ' + style_options.hover.boxshadow.x;
				} else {
					css += ' 0';
				}
				if ( style_options.hover.boxshadow.y ) {
					css += ' ' + style_options.hover.boxshadow.y;
				} else {
					css += ' 0';
				}
				if ( style_options.hover.boxshadow.blur ) {
					css += ' ' + style_options.hover.boxshadow.blur;
				}
				if ( style_options.hover.boxshadow.spread ) {
					css += ' ' + style_options.hover.boxshadow.spread;
				}
				if ( style_options.hover.boxshadow.color ) {
					css += ' ' + style_options.hover.boxshadow.color;
				}
			}
			css += ';';
		}
		css += '}';
	}

	if ( style_options.hover ) {
		if ( clientId == -1 ) {
			css += selector + ':hover{';
		} else {
			css += '#block-' + clientId + ':hover{';
		}

		const hoverPositionOptions = {
			top: 'top',
			right: 'right',
			bottom: 'bottom',
			left: 'left',
		}
		_.each( hoverPositionOptions, function( css_property, attr_name ) {
			if ( typeof style_options.hover[attr_name] != 'undefined' && ( '' + style_options.hover[attr_name] ).length ) {
				css += css_property + ':' + style_options.hover[attr_name] + ';';
			}
		} );
		css += '}';
	}
	return css;
}