import portoNext40 from './next40';
/**
 * Porto Advanced Range Control (Responsive)
 *
 * A Gutenberg control that combines:
 *  - A responsive device selector ( Desktop / Tablet / Mobile )
 *  - A number slider ( RangeControl )
 *  - A unit selectbox ( px, %, em, rem, vw, vh, ... ) – can be disabled with `allowUnit={ false }`
 *    for unit-less CSS properties ( z-index, order, opacity, line-height, ... ).
 *
 * Value structure:
 *  {
 *      desktop: { value: 320, unit: 'px' },
 *      tablet:  { value: '',  unit: 'px' },
 *      mobile:  { value: '',  unit: 'px' }
 *  }
 *
 *  When `allowUnit={ false }`, the per-device `unit` is persisted as '' and the CSS helper
 *  emits the bare value without any unit suffix.
 *
 * Usage:
 *  <PortoAdvancedRangeControl
 *      label={ __( 'Width', 'porto-functionality' ) }
 *      value={ attributes.width }
 *      units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh' ] }
 *      defaultUnit="px"
 *      range={ {
 *          px:   { min: 0, max: 1000, step: 1 },
 *          '%':  { min: 0, max: 100,  step: 1 },
 *          em:   { min: 0, max: 50,   step: 0.1 },
 *          rem:  { min: 0, max: 50,   step: 0.1 },
 *          vw:   { min: 0, max: 100,  step: 1 },
 *          vh:   { min: 0, max: 100,  step: 1 },
 *      } }
 *      onChange={ ( val ) => setAttributes( { width: val } ) }
 *  />
 *
 *  <PortoAdvancedRangeControl
 *      label={ __( 'z-index', 'porto-functionality' ) }
 *      value={ attributes.zIndex }
 *      allowUnit={ false }
 *      min={ -1 }
 *      max={ 9999 }
 *      step={ 1 }
 *      onChange={ ( val ) => setAttributes( { zIndex: val } ) }
 *  />
 */

const DEFAULT_UNITS = [ 'px', '%', 'em', 'rem', 'vw', 'vh' ];

const DEFAULT_RANGE = {
	px:   { min: 0, max: 1000, step: 1 },
	'%':  { min: 0, max: 100,  step: 1 },
	em:   { min: 0, max: 50,   step: 0.1 },
	rem:  { min: 0, max: 50,   step: 0.1 },
	vw:   { min: 0, max: 100,  step: 1 },
	vh:   { min: 0, max: 100,  step: 1 },
};

const DEVICES = [
	{ name: 'desktop', icon: 'desktop',    label: 'Desktop', preview: 'Desktop' },
	{ name: 'tablet',  icon: 'tablet',     label: 'Tablet',  preview: 'Tablet'  },
	{ name: 'mobile',  icon: 'smartphone', label: 'Mobile',  preview: 'Mobile'  },
];

/**
 * Read Gutenberg's current preview device, supporting all known stores
 * ( core/editor [new], core/edit-post, core/edit-site ).
 */
const getGutenbergPreviewDevice = function ( select ) {
	if ( !select ) {
		return 'Desktop';
	}
	const editorStore = select( 'core/editor' );
	if ( editorStore && typeof editorStore.getDeviceType === 'function' ) {
		return editorStore.getDeviceType();
	}
	const editPostStore = select( 'core/edit-post' );
	if ( editPostStore && typeof editPostStore.__experimentalGetPreviewDeviceType === 'function' ) {
		return editPostStore.__experimentalGetPreviewDeviceType();
	}
	const editSiteStore = select( 'core/edit-site' );
	if ( editSiteStore && typeof editSiteStore.__experimentalGetPreviewDeviceType === 'function' ) {
		return editSiteStore.__experimentalGetPreviewDeviceType();
	}
	return 'Desktop';
};

/**
 * Switch Gutenberg's preview device.
 *
 * Switching the device re-mounts the editor canvas ( esp. Desktop <-> Tablet
 * which toggles the iframe ), which deselects the active block, can flip the
 * inspector tab back to "Settings", and collapses any open PanelBody.
 *
 * Instead of fighting the re-mount, we let it happen then restore:
 *   1. The previously selected block ( via core/block-editor ).
 *   2. The active complementary sidebar ( via core/interface ).
 *   3. The previously active inspector tab ( via DOM, the only public surface ).
 *   4. The PanelBody where the responsive button was clicked ( re-opened via DOM ).
 *
 * @param {string}      deviceType  - "Desktop" / "Tablet" / "Mobile".
 * @param {HTMLElement} sourceEl    - The DOM node that was clicked. Used to locate the
 *                                    surrounding PanelBody so we can re-open it.
 */
const setGutenbergPreviewDevice = function ( deviceType, sourceEl ) {
	if ( !wp.data || !wp.data.dispatch || !wp.data.select ) {
		return;
	}

	let prevBlockId = null;
	try {
		const blockEditor = wp.data.select( 'core/block-editor' );
		if ( blockEditor && typeof blockEditor.getSelectedBlockClientId === 'function' ) {
			prevBlockId = blockEditor.getSelectedBlockClientId();
		}
	} catch ( e ) {}

	let prevSidebar = null;
	let prevSidebarScope = 'core/edit-post';
	try {
		const interfaceStore = wp.data.select( 'core/interface' );
		if ( interfaceStore && typeof interfaceStore.getActiveComplementaryArea === 'function' ) {
			const candidates = [ 'core/edit-post', 'core/edit-site', 'core' ];
			for ( let i = 0; i < candidates.length; i++ ) {
				const area = interfaceStore.getActiveComplementaryArea( candidates[ i ] );
				if ( area ) {
					prevSidebar = area;
					prevSidebarScope = candidates[ i ];
					break;
				}
			}
		}
	} catch ( e ) {}

	let panelTitle = null;
	if ( sourceEl && typeof sourceEl.closest === 'function' ) {
		const panel = sourceEl.closest( '.components-panel__body' );
		if ( panel ) {
			const titleEl = panel.querySelector( '.components-panel__body-title' );
			if ( titleEl ) {
				panelTitle = ( titleEl.textContent || '' ).trim();
			}
		}
	}

	let activeTabId = null;
	let activeTabLabel = null;
	try {
		const activeTab = document.querySelector( '.block-editor-block-inspector [role="tab"][aria-selected="true"]' )
			|| document.querySelector( '.interface-interface-skeleton__sidebar [role="tab"][aria-selected="true"]' )
			|| document.querySelector( '[role="tab"][aria-selected="true"]' );
		if ( activeTab ) {
			activeTabId    = activeTab.id || activeTab.getAttribute( 'data-tab' ) || null;
			activeTabLabel = ( activeTab.textContent || '' ).trim() || activeTab.getAttribute( 'aria-label' ) || null;
		}
	} catch ( e ) {}

	const dispatchDeviceChange = () => {
		const editorActions = wp.data.dispatch( 'core/editor' );
		if ( editorActions && typeof editorActions.setDeviceType === 'function' ) {
			editorActions.setDeviceType( deviceType );
			return;
		}
		const editPostActions = wp.data.dispatch( 'core/edit-post' );
		if ( editPostActions && typeof editPostActions.__experimentalSetPreviewDeviceType === 'function' ) {
			editPostActions.__experimentalSetPreviewDeviceType( deviceType );
			return;
		}
		const editSiteActions = wp.data.dispatch( 'core/edit-site' );
		if ( editSiteActions && typeof editSiteActions.__experimentalSetPreviewDeviceType === 'function' ) {
			editSiteActions.__experimentalSetPreviewDeviceType( deviceType );
		}
	};

	const restoreSidebar = () => {
		try {
			if ( !prevSidebar ) {
				return;
			}
			const dispatchIface = wp.data.dispatch( 'core/interface' );
			if ( dispatchIface && typeof dispatchIface.enableComplementaryArea === 'function' ) {
				const currentSidebar = wp.data.select( 'core/interface' ).getActiveComplementaryArea( prevSidebarScope );
				if ( currentSidebar !== prevSidebar ) {
					dispatchIface.enableComplementaryArea( prevSidebarScope, prevSidebar );
				}
			}
		} catch ( e ) {}
	};

	const restoreBlock = () => {
		try {
			if ( !prevBlockId ) {
				return true;
			}
			const blockEditor = wp.data.select( 'core/block-editor' );
			if ( !blockEditor ) {
				return false;
			}
			if ( blockEditor.getSelectedBlockClientId() === prevBlockId ) {
				return true;
			}
			wp.data.dispatch( 'core/block-editor' ).selectBlock( prevBlockId );
			return blockEditor.getSelectedBlockClientId() === prevBlockId;
		} catch ( e ) {
			return false;
		}
	};

	const restoreInspectorTab = () => {
		if ( !activeTabId && !activeTabLabel ) {
			return true;
		}
		try {
			let target = activeTabId ? document.getElementById( activeTabId ) : null;
			if ( ( !target || target.getAttribute( 'role' ) !== 'tab' ) && activeTabLabel ) {
				const tabs = document.querySelectorAll( '[role="tab"]' );
				for ( let i = 0; i < tabs.length; i++ ) {
					const t = tabs[ i ];
					const label = ( t.textContent || '' ).trim() || t.getAttribute( 'aria-label' );
					if ( label === activeTabLabel ) {
						target = t;
						break;
					}
				}
			}
			if ( target && target.getAttribute( 'aria-selected' ) !== 'true' ) {
				target.click();
				return false;
			}
			return true;
		} catch ( e ) {
			return false;
		}
	};

	const restorePanel = () => {
		if ( !panelTitle ) {
			return true;
		}
		try {
			const panels = document.querySelectorAll( '.components-panel__body' );
			for ( let i = 0; i < panels.length; i++ ) {
				const p = panels[ i ];
				const titleEl = p.querySelector( '.components-panel__body-title' );
				const titleText = titleEl ? ( titleEl.textContent || '' ).trim() : '';
				if ( titleText === panelTitle ) {
					if ( !p.classList.contains( 'is-opened' ) ) {
						const btn = ( titleEl && titleEl.tagName === 'BUTTON' )
							? titleEl
							: ( titleEl && titleEl.querySelector( 'button' ) ) || p.querySelector( '.components-panel__body-toggle' );
						if ( btn ) {
							btn.click();
							return false;
						}
					}
					return true;
				}
			}
			return false;
		} catch ( e ) {
			return false;
		}
	};

	const deadline = Date.now() + 2000;
	const tick = () => {
		restoreSidebar();
		const blockOk = restoreBlock();
		const tabOk   = restoreInspectorTab();
		const panelOk = restorePanel();
		if ( ( blockOk && tabOk && panelOk ) || Date.now() > deadline ) {
			return;
		}
		setTimeout( tick, 80 );
	};

	setTimeout( () => {
		dispatchDeviceChange();
		setTimeout( tick, 60 );
	}, 0 );
};

/**
 * Map a Gutenberg preview device type ( "Desktop" / "Tablet" / "Mobile" )
 * to one of our internal device keys ( "desktop" / "tablet" / "mobile" ).
 */
const previewToDeviceName = function ( preview ) {
	if ( !preview ) {
		return 'desktop';
	}
	const found = DEVICES.find( ( d ) => d.preview === preview );
	return found ? found.name : 'desktop';
};

/**
 * Whether a value already uses the new responsive shape ( has at least one device bucket ).
 * Anything else ( a bare "2.25rem" / number, or a single { value, unit } object ) is legacy.
 */
const isResponsiveRangeValue = function ( v ) {
	return !!( v && typeof v === 'object' && !Array.isArray( v ) && ( v.desktop || v.tablet || v.mobile ) );
};

/**
 * Split a legacy non-responsive value into { value, unit }.
 *
 *   "2.25rem" → { value: 2.25, unit: 'rem' }
 *   "10"      → { value: 10,   unit: defaultUnit ( or '' when allowUnit === false ) }
 *   0.5       → { value: 0.5,  unit: defaultUnit }
 *   "auto" / "calc( 100% - 1px )" → { value: <raw string>, unit: '' }   ( emitted verbatim )
 *
 * Used by the control so the slider / unit picker reflect an old value, and analogous
 * to migrateLegacyDimension() in advanced-dimension.js.
 */
const parseLegacyRangeValue = function ( v, defaultUnit, allowUnit ) {
	const fallbackUnit = allowUnit === false ? '' : ( defaultUnit || '' );
	if ( typeof v === 'undefined' || v === null || v === '' ) {
		return { value: '', unit: fallbackUnit };
	}
	const m = ( '' + v ).trim().match( /^(-?\d*\.?\d+)\s*([a-zA-Z%]*)$/ );
	if ( m ) {
		return { value: Number( m[ 1 ] ), unit: allowUnit === false ? '' : ( m[ 2 ] || fallbackUnit ) };
	}
	// Non-numeric ( keyword / function ) – keep raw, with an empty unit so it is emitted as-is.
	return { value: ( '' + v ).trim(), unit: '' };
};

const PortoAdvancedRangeControl = function ( {
	label,
	value,
	onChange,
	units = DEFAULT_UNITS,
	defaultUnit = 'px',
	min,
	max,
	step,
	range,
	responsive = true,
	allowReset = true,
	allowUnit = true,
	help = '',
} ) {
	// When the consumer disables units ( e.g. for z-index, opacity, order ), force the
	// fallback unit to '' so every persisted value comes out unit-less and the CSS
	// helper emits the bare number. The unit picker is also skipped in render.
	if ( !allowUnit ) {
		defaultUnit = '';
	}
	const __ = wp.i18n.__,
		Button = wp.components.Button,
		Dashicon = wp.components.Dashicon,
		Dropdown = wp.components.Dropdown,
		MenuGroup = wp.components.MenuGroup,
		MenuItem = wp.components.MenuItem,
		RangeControl = portoNext40( wp.components.RangeControl ),
		SelectControl = portoNext40( wp.components.SelectControl ),
		useState = wp.element.useState,
		useRef = wp.element.useRef,
		useSelect = wp.data && wp.data.useSelect;

	// We need the toggle button's DOM node so setGutenbergPreviewDevice can locate the
	// surrounding PanelBody for restore. The MenuItem lives in a Popover ( rendered outside
	// the panel ), so we capture the toggle's currentTarget on open and reuse it.
	const deviceToggleSourceRef = useRef ? useRef( null ) : { current: null };

	// Drive the active device from Gutenberg's preview device store so the control
	// stays in sync with the editor toolbar ( and vice versa ).
	const previewDevice = useSelect ? useSelect( ( select ) => getGutenbergPreviewDevice( select ), [] ) : 'Desktop';
	const device = previewToDeviceName( previewDevice );

	// Per-device "pending" unit for when the value is empty. The unit only gets persisted onto
	// the attribute once the user actually enters a value, so we don't dirty the attribute with
	// a stand-alone unit change.
	const [ pendingUnits, setPendingUnits ] = useState( {
		desktop: defaultUnit,
		tablet:  defaultUnit,
		mobile:  defaultUnit,
	} );

	// Normalize incoming value so consumers can pass a primitive ( legacy ) or an object.
	const normalized = ( () => {
		const result = { desktop: {}, tablet: {}, mobile: {} };
		if ( value && typeof value === 'object' ) {
			DEVICES.forEach( ( d ) => {
				const v = value[ d.name ];
				if ( v && typeof v === 'object' ) {
					result[ d.name ] = {
						value: typeof v.value !== 'undefined' ? v.value : '',
						unit:  allowUnit ? ( v.unit || defaultUnit ) : '',
					};
				} else if ( typeof v === 'number' || typeof v === 'string' ) {
					// Allow per-device primitive shorthand.
					result[ d.name ] = { value: v, unit: defaultUnit };
				} else {
					result[ d.name ] = { value: '', unit: defaultUnit };
				}
			} );
		} else if ( typeof value === 'number' || typeof value === 'string' ) {
			// Legacy non-responsive primitive ( e.g. "2.25rem" ) – lift onto desktop,
			// splitting any trailing unit so the slider / unit picker reflect it.
			const legacy   = parseLegacyRangeValue( value, defaultUnit, allowUnit );
			result.desktop = legacy;
			result.tablet  = { value: '', unit: legacy.unit };
			result.mobile  = { value: '', unit: legacy.unit };
		}
		return result;
	} )();

	const current     = normalized[ device ] || { value: '', unit: defaultUnit };
	const currentVal  = ( current.value === '' || typeof current.value === 'undefined' ) ? undefined : Number( current.value );

	// Per-device inheritance ( tablet inherits from desktop, mobile from tablet then desktop ).
	const hasDeviceVal = ( dv ) =>
		dv && typeof dv.value !== 'undefined' && dv.value !== '' && dv.value !== null;
	const inheritedFor = ( forDevice ) => {
		if ( forDevice === 'tablet' ) {
			return hasDeviceVal( normalized.desktop ) ? normalized.desktop : null;
		}
		if ( forDevice === 'mobile' ) {
			if ( hasDeviceVal( normalized.tablet ) )  return normalized.tablet;
			if ( hasDeviceVal( normalized.desktop ) ) return normalized.desktop;
		}
		return null;
	};
	const inherited = inheritedFor( device );

	// While the device has no value, show / use the pending unit so the user can pick a unit first.
	// On tablet/mobile fall back to the inherited unit so the picker reflects what is actually applied.
	const currentUnit = ( current.value === '' || typeof current.value === 'undefined' )
		? ( pendingUnits[ device ] && pendingUnits[ device ] !== defaultUnit
			? pendingUnits[ device ]
			: ( ( inherited && inherited.unit ) || pendingUnits[ device ] || defaultUnit ) )
		: ( current.unit || defaultUnit );

	// Resolve min / max / step from per-unit range, falling back to props or defaults.
	const resolvedRange = ( () => {
		const merged = Object.assign( {}, DEFAULT_RANGE, range || {} );
		const per = merged[ currentUnit ] || {};
		return {
			min:  typeof min  !== 'undefined' ? min  : ( typeof per.min  !== 'undefined' ? per.min  : 0 ),
			max:  typeof max  !== 'undefined' ? max  : ( typeof per.max  !== 'undefined' ? per.max  : 100 ),
			step: typeof step !== 'undefined' ? step : ( typeof per.step !== 'undefined' ? per.step : 1 ),
		};
	} )();

	const hasDeviceValue = hasDeviceVal;

	const updateValue = ( newValue ) => {
		const next = {
			desktop: Object.assign( {}, normalized.desktop ),
			tablet:  Object.assign( {}, normalized.tablet ),
			mobile:  Object.assign( {}, normalized.mobile ),
		};
		next[ device ] = Object.assign( {}, next[ device ], newValue );

		// Only keep devices that have an actual value – discard entries that hold just an empty value + unit.
		const cleaned = {};
		let hasAny = false;
		DEVICES.forEach( ( d ) => {
			if ( hasDeviceValue( next[ d.name ] ) ) {
				cleaned[ d.name ] = {
					value: next[ d.name ].value,
					unit:  next[ d.name ].unit || defaultUnit,
				};
				hasAny = true;
			}
		} );

		onChange( hasAny ? cleaned : undefined );
	};

	const onChangeNumber = ( val ) => {
		if ( val === '' || val === null || typeof val === 'undefined' ) {
			updateValue( { value: '' } );
		} else {
			// Persist the pending unit alongside the value when the user starts entering one.
			updateValue( {
				value: Number( val ),
				unit:  current.unit || pendingUnits[ device ] || defaultUnit,
			} );
		}
	};

	const onChangeUnit = ( newUnit ) => {
		if ( hasDeviceValue( normalized[ device ] ) ) {
			updateValue( { unit: newUnit } );
		} else {
			// No value yet – just remember the chosen unit locally; it will be persisted as soon as a value is entered.
			setPendingUnits( Object.assign( {}, pendingUnits, { [ device ]: newUnit } ) );
		}
	};

	const unitOptions = ( units || DEFAULT_UNITS ).map( ( u ) => ( { label: u, value: u } ) );

	return (
		<div className="components-base-control porto-advanced-range-control porto-custom-control">
			<div className="porto-advanced-range-control__header" style={ { display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 } }>
				<label className="components-base-control__label" style={ { marginBottom: 0 } }>
					{ label }
				</label>
				{ responsive && (
					<Dropdown
						className="porto-advanced-range-control__device-dropdown"
						contentClassName="porto-advanced-range-control__device-popover"
						popoverProps={ { placement: 'bottom-end' } }
						renderToggle={ ( { isOpen, onToggle } ) => {
							const active = DEVICES.find( ( d ) => d.name === device ) || DEVICES[ 0 ];
							return (
								<Button
									className={ 'porto-advanced-range-control__device-toggle' + ( isOpen ? ' is-open' : '' ) }
									isSmall={ true }
									onClick={ ( e ) => {
										if ( e && e.currentTarget ) {
											deviceToggleSourceRef.current = e.currentTarget;
										}
										onToggle();
									} }
									label={ __( active.label, 'porto-functionality' ) }
									showTooltip={ true }
									aria-haspopup="true"
									aria-expanded={ isOpen }
								>
									<Dashicon icon={ active.icon } />
								</Button>
							);
						} }
						renderContent={ ( { onClose } ) => (
							<MenuGroup>
								{ DEVICES.map( ( d ) => (
									<MenuItem
										key={ d.name }
										className={ 'porto-advanced-range-control__device-option' + ( device === d.name ? ' is-active' : '' ) }
										isSelected={ device === d.name }
										icon={ d.icon }
										onClick={ () => {
											setGutenbergPreviewDevice( d.preview, deviceToggleSourceRef.current );
											onClose();
										} }
									>
										{ __( d.label, 'porto-functionality' ) }
									</MenuItem>
								) ) }
							</MenuGroup>
						) }
					/>
				) }
			</div>
			<div className="porto-advanced-range-control__body" style={ { display: 'flex', alignItems: 'center', gap: 8 } }>
				<div style={ { flex: 1 } }>
					<RangeControl
						value={ typeof currentVal === 'number' && !isNaN( currentVal ) ? currentVal : ( inherited ? Number( inherited.value ) : '' ) }
						min={ resolvedRange.min }
						max={ resolvedRange.max }
						step={ resolvedRange.step }
						withInputField={ false }
						allowReset={ allowReset }
						onChange={ onChangeNumber }
						hideLabelFromVision={ true }
					/>
				</div>
				<div className={ 'porto-advanced-range-control__input' + ( allowUnit ? '' : ' is-unitless' ) } style={ { display: 'inline-flex', alignItems: 'center', border: '1px solid #949494', borderRadius: 2, overflow: 'hidden', background: '#fff' } }>
					<input
						type="number"
						className={ 'porto-advanced-range-control__number' + ( typeof currentVal !== 'number' && inherited ? ' is-inherited' : '' ) }
						value={ typeof currentVal === 'number' && !isNaN( currentVal ) ? currentVal : '' }
						placeholder={ typeof currentVal !== 'number' && inherited ? ( '' + inherited.value ) : '' }
						min={ resolvedRange.min }
						max={ resolvedRange.max }
						step={ resolvedRange.step }
						onChange={ ( e ) => onChangeNumber( e.target.value ) }
						style={ { width: allowUnit ? 50 : 64, border: 'none', boxShadow: 'none', padding: allowUnit ? '0 0 0 4px' : '0 6px', fontSize: 13, textAlign: 'center', background: 'transparent' } }
					/>
					{ allowUnit && (
						<SelectControl
							className="porto-advanced-range-control__unit"
							value={ currentUnit }
							options={ unitOptions }
							onChange={ onChangeUnit }
							hideLabelFromVision={ true }
							__nextHasNoMarginBottom={ true }
							style={ { border: 'none', boxShadow: 'none', background: 'transparent', minHeight: 'auto', paddingLeft: 2, paddingRight: 14, fontSize: '12px' } }
						/>
					) }
				</div>
			</div>
			{ help && (
				<p className="components-base-control__help" style={ { marginTop: 4 } }>
					{ help }
				</p>
			) }
		</div>
	);
};

export default PortoAdvancedRangeControl;

/**
 * Helper – build a single CSS declaration ( "<property>:<value><unit>;" ) for the given device value.
 *
 * If `deviceVal.unit` is explicitly stored as an empty string ( the shape produced by the
 * control when `allowUnit={ false }` ), the value is emitted bare – useful for unit-less
 * CSS properties like z-index, order, opacity, line-height.
 */
const buildDeclaration = function ( deviceVal, property ) {
	if ( !deviceVal || typeof deviceVal.value === 'undefined' || deviceVal.value === '' || deviceVal.value === null ) {
		return '';
	}
	const unit = ( typeof deviceVal.unit === 'string' && deviceVal.unit === '' ) ? '' : ( deviceVal.unit || 'px' );
	return property + ':' + deviceVal.value + unit + ';';
};

/**
 * Generate responsive CSS for an advanced range value.
 *
 * @param {Object} value     - Value produced by PortoAdvancedRangeControl.
 * @param {string} selector  - CSS selector ( without leading dot / hash ). Will be prefixed with "html ." like other Porto controls.
 * @param {string} property  - CSS property name ( e.g. "width", "max-width", "font-size" ).
 * @param {Object} options   - Optional. { tabletBreakpoint: 991, mobileBreakpoint: 575, raw: false }
 *                              When raw=true, "selector" is used verbatim ( useful for "html #block-xxx" ).
 *
 * @return {string} The generated CSS string.
 */
export const portoGenerateAdvancedRangeCSS = function ( value, selector, property, options = {} ) {
	if ( !value || !selector || !property ) {
		return '';
	}
	// Accept legacy non-responsive values ( e.g. width_val: "2.25rem", a bare number, or
	// "auto" ) saved by older versions with no device buckets. Lift them onto the desktop
	// slot, emitted verbatim ( unit '' ), so they still render. New responsive values
	// ( { desktop / tablet / mobile } ) are left untouched.
	if ( ! isResponsiveRangeValue( value ) ) {
		if ( typeof value === 'number' || typeof value === 'string' ) {
			value = { desktop: { value: value, unit: '' } };
		} else if ( typeof value.value !== 'undefined' ) {
			// Single { value, unit } object without device buckets.
			value = { desktop: { value: value.value, unit: typeof value.unit === 'string' ? value.unit : '' } };
		} else {
			return '';
		}
	}
	// Treat any selector already carrying an id or an "html " prefix as raw so an
	// already-finalized selector from portoGenerateStyleOptionsCSS ( e.g. "html .cls" )
	// is not prefixed a second time into "html .html .cls" ( which matches nothing ).
	if (
		0 === selector.indexOf( '#' ) ||
		0 === selector.indexOf( 'html ' ) ||
		0 === selector.indexOf( 'html.' )
	) {
		options.raw = true;
	}
	const tabletBp = typeof options.tabletBreakpoint !== 'undefined' ? options.tabletBreakpoint : 991;
	const mobileBp = typeof options.mobileBreakpoint !== 'undefined' ? options.mobileBreakpoint : 767;
	const sel      = options.raw
		? selector
		: ( 0 === selector.indexOf( '.' ) ? 'html ' + selector : 'html .' + selector );

	let css = '';

	const desktopDecl = buildDeclaration( value.desktop, property );
	if ( desktopDecl ) {
		css += sel + '{' + desktopDecl + '}';
	}

	const tabletDecl = buildDeclaration( value.tablet, property );
	if ( tabletDecl ) {
		css += '@media (max-width:' + tabletBp + 'px){' + sel + '{' + tabletDecl + '}}';
	}

	const mobileDecl = buildDeclaration( value.mobile, property );
	if ( mobileDecl ) {
		css += '@media (max-width:' + mobileBp + 'px){' + sel + '{' + mobileDecl + '}}';
	}

	return css;
};
