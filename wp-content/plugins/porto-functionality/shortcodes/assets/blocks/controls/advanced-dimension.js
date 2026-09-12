/**
 * Porto Advanced Dimension Control (Responsive)
 *
 * A Gutenberg control that combines:
 *  - A responsive device selector ( Desktop / Tablet / Mobile, synced with Gutenberg's preview device )
 *  - A "lock" toggle that mirrors a single value across all four sides
 *  - Four side inputs ( top / right / bottom / left )
 *  - A unit selector ( px, %, em, rem, vw, vh, custom ) – when "custom" is selected each side
 *    accepts a free-form CSS value ( e.g. "10px", "5%", "calc( 100% - 20px )" )
 *
 * Value structure:
 *  {
 *      desktop: { top: '10', right: '20', bottom: '10', left: '20', unit: 'px' },
 *      tablet:  { top: '5',  right: '5',  bottom: '5',  left: '5',  unit: 'px' },
 *      mobile:  { top: 'auto', right: '0', bottom: 'auto', left: '0', unit: 'custom' }
 *  }
 *
 *  - When unit !== 'custom' each side stores a numeric string and the shared `unit` is appended at render time.
 *  - When unit === 'custom' each side stores a complete CSS value ( unit included ) and `unit` is just a marker.
 *
 * Usage:
 *  <PortoAdvancedDimensionControl
 *      label={ __( 'Margin', 'porto-functionality' ) }
 *      value={ attributes.margin }
 *      units={ [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ] }
 *      defaultUnit="px"
 *      onChange={ ( val ) => setAttributes( { margin: val } ) }
 *  />
 */

const DEFAULT_UNITS = [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ];

const DEVICES = [
	{ name: 'desktop', icon: 'desktop',    label: 'Desktop', preview: 'Desktop' },
	{ name: 'tablet',  icon: 'tablet',     label: 'Tablet',  preview: 'Tablet'  },
	{ name: 'mobile',  icon: 'smartphone', label: 'Mobile',  preview: 'Mobile'  },
];

const SIDES = [ 'top', 'right', 'bottom', 'left' ];

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
 * The DOM-based restore is intentionally simple ( query for the panel by its title text )
 * because Gutenberg's PanelBody open state is a local React useState, not exposed to any
 * data store, so DOM manipulation is the only way to reach it.
 *
 * @param {string}      deviceType  - "Desktop" / "Tablet" / "Mobile".
 * @param {HTMLElement} sourceEl    - The DOM node that was clicked. Used to locate the
 *                                    surrounding PanelBody so we can re-open it after
 *                                    the inspector re-mounts.
 */
const setGutenbergPreviewDevice = function ( deviceType, sourceEl ) {
	if ( !wp.data || !wp.data.dispatch || !wp.data.select ) {
		return;
	}

	// ----- Capture state from data stores -----
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

	// ----- Capture state from DOM ( panel title + active inspector tab ) -----
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

	// ----- Dispatch the actual device change ( deferred so React finishes click cycle first ) -----
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

	// ----- Restore helpers -----
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
				return false; // give the next tick a chance to verify
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
							return false; // verify next tick
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

	// ----- Retry loop -----
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

const previewToDeviceName = function ( preview ) {
	if ( !preview ) {
		return 'desktop';
	}
	const found = DEVICES.find( ( d ) => d.preview === preview );
	return found ? found.name : 'desktop';
};

/**
 * Detect whether a side value already includes a unit / non-numeric content.
 * Used by the "custom" unit so each side keeps its full CSS expression.
 */
const isCustomLikeValue = function ( v ) {
	if ( typeof v !== 'string' ) {
		return false;
	}
	if ( v === '' ) {
		return false;
	}
	// Anything that isn't a plain integer / float is treated as custom.
	return !/^-?\d*\.?\d+$/.test( v.trim() );
};

/**
 * Detect Porto's old (non-responsive) dimension value format:
 *
 *   { top: '20px', right: '20px', bottom: '20px', left: '20px' }   ( typical )
 *   { 'margin-top': '10px', 'margin-right': '10px', ... }          ( prefixed variant )
 *
 * The new format uses `desktop` / `tablet` / `mobile` top-level keys; if any of
 * those exist we treat the value as already-new.
 */
const isLegacyDimension = function ( val ) {
	if ( !val || typeof val !== 'object' || Array.isArray( val ) ) {
		return false;
	}
	if ( val.desktop || val.tablet || val.mobile ) {
		return false;
	}
	// Direct {top,right,bottom,left} – old non-responsive shape.
	if ( ( 'top' in val ) || ( 'right' in val ) || ( 'bottom' in val ) || ( 'left' in val ) ) {
		return true;
	}
	// Prefixed variant ( e.g. { 'margin-top': '10px', ... } ).
	const keys = Object.keys( val );
	for ( let i = 0; i < keys.length; i++ ) {
		if ( /-(top|right|bottom|left)$/.test( keys[ i ] ) ) {
			return true;
		}
	}
	return false;
};

/**
 * Convert a legacy non-responsive dimension value into the new responsive shape,
 * placing the legacy values on the `desktop` slot.
 *
 *   Input  : { top: '20px', right: '20px', bottom: '20px', left: '20px' }
 *   Output : { desktop: { top: '20', right: '20', bottom: '20', left: '20', unit: 'px' } }
 *
 *   Input  : { top: '10px', right: '5%', bottom: 'auto', left: '10px' }
 *   Output : { desktop: { top: '10px', right: '5%', bottom: 'auto', left: '10px', unit: 'custom' } }
 *
 * If every side shares the same simple numeric unit ( e.g. all "10px" ), the result
 * uses that unit and the side strings are reduced to bare numbers.
 *
 * Both the direct ( {top,right,bottom,left} ) and the prefixed ( {margin-top,...} )
 * shapes are accepted.
 */
const migrateLegacyDimension = function ( val, defaultUnit ) {
	const fallbackUnit = defaultUnit || 'px';
	const desktop = { top: '', right: '', bottom: '', left: '', unit: fallbackUnit };
	if ( !val || typeof val !== 'object' ) {
		return { desktop };
	}

	const sideRaw = {};
	SIDES.forEach( ( side ) => {
		// 1. Direct key match ( old non-responsive shape ).
		let raw = val[ side ];
		// 2. Fallback: any key ending with "-<side>" ( prefixed variant ).
		if ( typeof raw === 'undefined' || raw === null || raw === '' ) {
			const matchingKey = Object.keys( val ).find( ( k ) => k !== side && k.endsWith( '-' + side ) );
			if ( matchingKey ) {
				raw = val[ matchingKey ];
			}
		}
		if ( typeof raw !== 'undefined' && raw !== null && raw !== '' ) {
			sideRaw[ side ] = '' + raw;
		}
	} );

	if ( Object.keys( sideRaw ).length === 0 ) {
		return { desktop };
	}

	// Try to extract a shared simple unit from every side.
	const numericPart = {};
	const units = new Set();
	let allSimple = true;
	Object.keys( sideRaw ).forEach( ( side ) => {
		const v = sideRaw[ side ].trim();
		const m = v.match( /^(-?\d*\.?\d+)([a-zA-Z%]*)$/ );
		if ( m ) {
			numericPart[ side ] = m[ 1 ];
			units.add( m[ 2 ] || '' );
		} else {
			allSimple = false;
		}
	} );

	if ( allSimple && units.size === 1 ) {
		const sharedUnit = Array.from( units )[ 0 ];
		desktop.unit = sharedUnit || fallbackUnit;
		Object.keys( numericPart ).forEach( ( side ) => {
			desktop[ side ] = numericPart[ side ];
		} );
	} else {
		// Mixed units / functions / keywords – preserve as full custom strings.
		desktop.unit = 'custom';
		Object.keys( sideRaw ).forEach( ( side ) => {
			desktop[ side ] = sideRaw[ side ];
		} );
	}

	return { desktop };
};

const PortoAdvancedDimensionControl = function ( {
	label,
	value,
	onChange,
	units = DEFAULT_UNITS,
	defaultUnit = 'px',
	responsive = true,
	linkable = true,
	defaultLinked = true,
	help = '',
} ) {
	const __ = wp.i18n.__,
		Button = wp.components.Button,
		Dashicon = wp.components.Dashicon,
		Dropdown = wp.components.Dropdown,
		MenuGroup = wp.components.MenuGroup,
		MenuItem = wp.components.MenuItem,
		useState = wp.element.useState,
		useEffect = wp.element.useEffect,
		useRef = wp.element.useRef,
		useSelect = wp.data && wp.data.useSelect;

	// We need the toggle button's DOM node so setGutenbergPreviewDevice can locate the
	// surrounding PanelBody for restore. The MenuItem lives in a Popover ( rendered outside
	// the panel ), so we capture the toggle's currentTarget on open and reuse it.
	const deviceToggleSourceRef = useRef ? useRef( null ) : { current: null };

	// Drive the active device from Gutenberg's preview device store.
	const previewDevice = useSelect ? useSelect( ( select ) => getGutenbergPreviewDevice( select ), [] ) : 'Desktop';
	const device = previewToDeviceName( previewDevice );

	// "Linked" is a UI-only toggle ( per device ) – it controls whether typing in one side mirrors to the others.
	// `defaultLinked` may be:
	//   - boolean ( true / false )         → applied to every device.
	//   - object  ( { desktop, tablet, mobile } ) → per-device overrides ( missing keys fall back to true ).
	const resolveInitialLinked = () => {
		if ( defaultLinked && typeof defaultLinked === 'object' ) {
			return {
				desktop: typeof defaultLinked.desktop === 'undefined' ? true : !!defaultLinked.desktop,
				tablet:  typeof defaultLinked.tablet  === 'undefined' ? true : !!defaultLinked.tablet,
				mobile:  typeof defaultLinked.mobile  === 'undefined' ? true : !!defaultLinked.mobile,
			};
		}
		const flag = !!defaultLinked;
		return { desktop: flag, tablet: flag, mobile: flag };
	};
	const [ linkedDevices, setLinkedDevices ] = useState( resolveInitialLinked );

	// "Pending" units per device for when the device has no values yet, so the user can pick a unit
	// before typing any number without dirtying the attribute with an empty entry.
	const [ pendingUnits, setPendingUnits ] = useState( {
		desktop: defaultUnit,
		tablet:  defaultUnit,
		mobile:  defaultUnit,
	} );

	// ----- Normalize incoming value -----
	// Accept legacy non-responsive shapes ( e.g. { 'margin-top': '10px', ... } ) and
	// transparently lift them onto the desktop slot so old saved attributes keep working.
	const normalized = ( () => {
		const source = isLegacyDimension( value ) ? migrateLegacyDimension( value, defaultUnit ) : value;
		const result = {};
		DEVICES.forEach( ( d ) => {
			const v = source && typeof source === 'object' ? source[ d.name ] : null;
			if ( v && typeof v === 'object' ) {
				result[ d.name ] = {
					top:    typeof v.top    !== 'undefined' && v.top    !== null ? '' + v.top    : '',
					right:  typeof v.right  !== 'undefined' && v.right  !== null ? '' + v.right  : '',
					bottom: typeof v.bottom !== 'undefined' && v.bottom !== null ? '' + v.bottom : '',
					left:   typeof v.left   !== 'undefined' && v.left   !== null ? '' + v.left   : '',
					unit:   v.unit || defaultUnit,
				};
			} else {
				result[ d.name ] = { top: '', right: '', bottom: '', left: '', unit: defaultUnit };
			}
		} );
		return result;
	} )();

	const current = normalized[ device ];

	const hasAnySideValue = ( deviceVal ) =>
		!!deviceVal && SIDES.some( ( s ) => deviceVal[ s ] !== '' && typeof deviceVal[ s ] !== 'undefined' && deviceVal[ s ] !== null );

	const hasSide = ( deviceVal, side ) =>
		!!deviceVal && deviceVal[ side ] !== '' && typeof deviceVal[ side ] !== 'undefined' && deviceVal[ side ] !== null;

	// Per-side inherited value ( tablet inherits from desktop, mobile from tablet then desktop ).
	// Returns { value, unit } or null. Reflects how CSS cascading actually works at runtime.
	const getInheritedSide = ( forDevice, side ) => {
		if ( forDevice === 'tablet' ) {
			if ( hasSide( normalized.desktop, side ) ) {
				return { value: normalized.desktop[ side ], unit: normalized.desktop.unit || defaultUnit };
			}
			return null;
		}
		if ( forDevice === 'mobile' ) {
			if ( hasSide( normalized.tablet, side ) ) {
				return { value: normalized.tablet[ side ], unit: normalized.tablet.unit || defaultUnit };
			}
			if ( hasSide( normalized.desktop, side ) ) {
				return { value: normalized.desktop[ side ], unit: normalized.desktop.unit || defaultUnit };
			}
			return null;
		}
		return null;
	};

	// Inherited "device" object used to back-fill the unit selector when the active device has no values.
	const inheritedDeviceUnit = ( () => {
		if ( device === 'tablet' && hasAnySideValue( normalized.desktop ) ) {
			return normalized.desktop.unit || defaultUnit;
		}
		if ( device === 'mobile' ) {
			if ( hasAnySideValue( normalized.tablet ) ) {
				return normalized.tablet.unit || defaultUnit;
			}
			if ( hasAnySideValue( normalized.desktop ) ) {
				return normalized.desktop.unit || defaultUnit;
			}
		}
		return null;
	} )();

	// Keep "pendingUnits" trailing whatever unit the device currently uses, so clearing all
	// side values doesn't drop the user's unit choice ( e.g. picked "custom", typed values,
	// then cleared them – we still want the unit to stay "custom" ).
	useEffect( () => {
		if ( hasAnySideValue( current ) && current.unit && pendingUnits[ device ] !== current.unit ) {
			setPendingUnits( ( prev ) => Object.assign( {}, prev, { [ device ]: current.unit } ) );
		}
	}, [ device, current.unit, current.top, current.right, current.bottom, current.left ] );

	// While the device has no side values yet, show / use the pending unit so the user can pick a unit first.
	// On tablet/mobile fall back to the inherited unit so the picker reflects what is actually applied.
	const currentUnit = hasAnySideValue( current )
		? ( current.unit || defaultUnit )
		: ( pendingUnits[ device ] && pendingUnits[ device ] !== defaultUnit
			? pendingUnits[ device ]
			: ( inheritedDeviceUnit || pendingUnits[ device ] || defaultUnit ) );

	// ----- Output writer -----
	const emit = ( nextDevices ) => {
		const cleaned = {};
		let hasAny = false;
		DEVICES.forEach( ( d ) => {
			const dv = nextDevices[ d.name ];
			if ( hasAnySideValue( dv ) ) {
				cleaned[ d.name ] = {
					top:    dv.top    || '',
					right:  dv.right  || '',
					bottom: dv.bottom || '',
					left:   dv.left   || '',
					unit:   dv.unit   || defaultUnit,
				};
				hasAny = true;
			}
		} );
		onChange( hasAny ? cleaned : undefined );
	};

	const writeDevice = ( patch ) => {
		const next = {
			desktop: Object.assign( {}, normalized.desktop ),
			tablet:  Object.assign( {}, normalized.tablet ),
			mobile:  Object.assign( {}, normalized.mobile ),
		};
		next[ device ] = Object.assign( {}, next[ device ], patch );
		emit( next );
	};

	// ----- Side input change -----
	const onChangeSide = ( side, raw ) => {
		const newVal = typeof raw === 'undefined' || raw === null ? '' : ( '' + raw );

		// First write needs to pick up the active unit ( pending or persisted ).
		const unit = currentUnit;

		const patch = { unit };
		if ( linkedDevices[ device ] ) {
			SIDES.forEach( ( s ) => { patch[ s ] = newVal; } );
		} else {
			patch[ side ] = newVal;
		}

		// If the result clears the device, also remember the unit locally so the picker
		// keeps showing the user's choice on the next render ( the attribute will be undefined ).
		const merged = Object.assign( {}, current, patch );
		if ( !hasAnySideValue( merged ) && pendingUnits[ device ] !== unit ) {
			setPendingUnits( Object.assign( {}, pendingUnits, { [ device ]: unit } ) );
		}

		writeDevice( patch );
	};

	// ----- Unit change -----
	const onChangeUnit = ( newUnit ) => {
		// If no values yet, just remember the chosen unit locally; persist when a value is entered.
		if ( !hasAnySideValue( current ) ) {
			setPendingUnits( Object.assign( {}, pendingUnits, { [ device ]: newUnit } ) );
			return;
		}

		// Switching to / from "custom" is special: the stored side strings need to be converted.
		const next = { unit: newUnit };
		const wasCustom = current.unit === 'custom';
		const isCustom  = newUnit === 'custom';

		if ( wasCustom && !isCustom ) {
			// Strip non-numeric characters when leaving custom mode.
			SIDES.forEach( ( s ) => {
				const v = current[ s ];
				if ( typeof v === 'string' && v !== '' ) {
					const m = v.match( /-?\d*\.?\d+/ );
					next[ s ] = m ? m[ 0 ] : '';
				} else {
					next[ s ] = '';
				}
			} );
		} else if ( !wasCustom && isCustom ) {
			// Append previous unit to each side so values stay valid CSS.
			const prevUnit = current.unit || defaultUnit;
			SIDES.forEach( ( s ) => {
				const v = current[ s ];
				next[ s ] = v !== '' && typeof v !== 'undefined' && v !== null ? ( '' + v + prevUnit ) : '';
			} );
		}

		writeDevice( next );
	};

	// ----- Linked toggle -----
	const toggleLinked = () => {
		const willLink = !linkedDevices[ device ];
		setLinkedDevices( Object.assign( {}, linkedDevices, { [ device ]: willLink } ) );

		// When turning on the link, mirror the top side onto the rest if values diverge.
		if ( willLink && hasAnySideValue( current ) ) {
			const reference = current.top !== '' ? current.top : ( current.right || current.bottom || current.left || '' );
			const patch = {};
			SIDES.forEach( ( s ) => { patch[ s ] = reference; } );
			writeDevice( patch );
		}
	};

	const sideInputType = currentUnit === 'custom' ? 'text' : 'number';

	const unitDropdown = (
		<Dropdown
			className="porto-advanced-dimension-control__unit-dropdown"
			popoverProps={ { placement: 'bottom-end' } }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<span
					className={ 'porto-advanced-dimension-control__unit-toggle' + ( isOpen ? ' is-open' : '' ) + ( currentUnit === 'custom' ? ' is-custom' : '' ) }
					role="button"
					tabIndex={ 0 }
					onClick={ onToggle }
					onKeyDown={ ( e ) => {
						if ( e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown' ) {
							e.preventDefault();
							onToggle();
						}
					} }
					aria-haspopup="true"
					aria-expanded={ isOpen }
					aria-label={ __( 'Choose Unit', 'porto-functionality' ) + ' (' + currentUnit + ')' }
					title={ __( 'Choose Unit', 'porto-functionality' ) }
					style={ {
						display: 'inline-flex',
						alignItems: 'center',
						justifyContent: 'center',
						height: 34,
						padding: '0 3px',
						background: isOpen ? '#f0f0f0' : '#fff',
						color: '#1e1e1e',
						cursor: 'pointer',
						fontSize: 11,
						fontWeight: 500,
						lineHeight: 1,
						boxSizing: 'border-box',
						userSelect: 'none',
					} }
				>
					{ currentUnit === 'custom'
						? <Dashicon icon="edit" style={ { width: 16, height: 16, fontSize: 16 } } />
						: currentUnit
					}
				</span>
			) }
			renderContent={ ( { onClose } ) => (
				<MenuGroup label={ __( 'Unit', 'porto-functionality' ) }>
					{ ( units || DEFAULT_UNITS ).map( ( u ) => (
						<MenuItem
							key={ u }
							isSelected={ currentUnit === u }
							icon={ currentUnit === u ? 'yes' : null }
							onClick={ () => { onChangeUnit( u ); onClose(); } }
						>
							{ u === 'custom' ? __( 'custom', 'porto-functionality' ) : u }
						</MenuItem>
					) ) }
				</MenuGroup>
			) }
		/>
	);

	return (
		<div className="components-base-control porto-advanced-dimension-control porto-custom-control">
			<div className="porto-advanced-dimension-control__header" style={ { display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 } }>
				<label className="components-base-control__label" style={ { marginBottom: 0 } }>
					{ label }
				</label>
				<div className="porto-advanced-dimension-control__actions" style={ { display: 'inline-flex', alignItems: 'center', gap: 2 } }>
					{ responsive && (
						<Dropdown
							className="porto-advanced-dimension-control__device-dropdown"
							contentClassName="porto-advanced-dimension-control__device-popover"
							popoverProps={ { placement: 'bottom-end' } }
							renderToggle={ ( { isOpen, onToggle } ) => {
								const active = DEVICES.find( ( d ) => d.name === device ) || DEVICES[ 0 ];
								return (
									<Button
										className={ 'porto-advanced-dimension-control__device-toggle' + ( isOpen ? ' is-open' : '' ) }
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
											className={ 'porto-advanced-dimension-control__device-option' + ( device === d.name ? ' is-active' : '' ) }
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
					{ linkable && (
						<Button
							className="porto-advanced-dimension-control__link"
							isPressed={ !!linkedDevices[ device ] }
							isSmall={ true }
							onClick={ toggleLinked }
							label={ linkedDevices[ device ] ? __( 'Unlink Sides', 'porto-functionality' ) : __( 'Link Sides', 'porto-functionality' ) }
							showTooltip={ true }
						>
							<Dashicon icon={ linkedDevices[ device ] ? 'admin-links' : 'editor-unlink' } />
						</Button>
					) }
				</div>
			</div>
			<div className="porto-advanced-dimension-control__body" style={ { display: 'flex', alignItems: 'flex-start', gap: 3 } }>
				<div className="porto-advanced-dimension-control__sides" style={ { display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 3, flex: 1, minWidth: 0 } }>
					{ SIDES.map( ( side ) => {
						const ownVal = typeof current[ side ] === 'undefined' || current[ side ] === null ? '' : current[ side ];
						const isOwnSet = ownVal !== '';
						const inherited = !isOwnSet ? getInheritedSide( device, side ) : null;
						let placeholder = '';
						if ( inherited ) {
							if ( currentUnit === 'custom' ) {
								// Custom mode: show full CSS value ( with unit / function / etc. ).
								if ( inherited.unit === 'custom' ) {
									placeholder = '' + inherited.value;
								} else {
									placeholder = '' + inherited.value + ( inherited.unit || '' );
								}
							} else {
								// Numeric mode: show only the number, no unit suffix.
								if ( inherited.unit === 'custom' ) {
									const m = ( '' + inherited.value ).match( /-?\d*\.?\d+/ );
									placeholder = m ? m[ 0 ] : '';
								} else {
									placeholder = '' + inherited.value;
								}
							}
						} else if ( currentUnit === 'custom' ) {
							placeholder = '0px';
						}
						// When showing a non-numeric placeholder ( inherited "auto", "calc(...)", or unit-suffixed
						// like "10px" ) we need the input to be type="text" so the placeholder renders properly.
						const inputType = ( currentUnit === 'custom' || ( placeholder && /[^0-9.\-]/.test( placeholder ) ) ) ? 'text' : 'number';
						return (
							<div className="porto-advanced-dimension-control__side" key={ side } style={ { display: 'flex', flexDirection: 'column', alignItems: 'center', minWidth: 0 } }>
								<input
									type={ inputType }
									className={ 'porto-advanced-dimension-control__input' + ( !isOwnSet && inherited ? ' is-inherited' : '' ) }
									value={ ownVal }
									placeholder={ placeholder }
									onChange={ ( e ) => onChangeSide( side, e.target.value ) }
									style={ {
										width: '100%',
										minWidth: 0,
										textAlign: 'center',
										padding: '2px',
										border: '1px solid #949494',
										borderRadius: 2,
										boxSizing: 'border-box',
										fontSize: 13,
										color: !isOwnSet && inherited ? '#a7aaad' : 'currentColor',
									} }
								/>
								<span className="porto-advanced-dimension-control__side-label" style={ { fontSize: 11, color: '#888', marginTop: 2, textTransform: 'lowercase' } }>
									{ __( side, 'porto-functionality' ) }
								</span>
							</div>
						);
					} ) }
				</div>
				<div className="porto-advanced-dimension-control__unit" style={ { flexShrink: 0 } }>
					{ unitDropdown }
				</div>
			</div>
			{ help && (
				<p className="components-base-control__help" style={ { marginTop: 6 } }>
					{ help }
				</p>
			) }
		</div>
	);
};

export default PortoAdvancedDimensionControl;

/**
 * Map a logical side ( top / right / bottom / left ) to the actual CSS property name to emit,
 * given the requested `property` value.
 *
 * Supported `property` values:
 *  - "margin"        → margin-<side>           ( margin-top, margin-right, ... )
 *  - "padding"       → padding-<side>          ( padding-top, ... )
 *  - "border"        → border-<side>-width     ( border-top-width, ... )
 *  - "border-width"  → border-<side>-width     ( alias of "border", matches the PHP helper )
 *  - "border-radius" → border-<corner>-radius  ( top→top-left, right→top-right, bottom→bottom-right, left→bottom-left )
 *  - anything else   → <property>-<side>       ( generic fallback, preserves the historical behaviour )
 */
const BORDER_RADIUS_CORNER_MAP = {
	top:    'border-top-left-radius',
	right:  'border-top-right-radius',
	bottom: 'border-bottom-right-radius',
	left:   'border-bottom-left-radius',
};
const sidePropertyFor = function ( property, side ) {
	if ( !property ) {
		// Very defensive: emit just the bare side name so callers can wrap it themselves.
		return side;
	}
	if ( 'border' === property || 'border-width' === property ) {
		return 'border-' + side + '-width';
	}
	if ( 'border-radius' === property ) {
		return BORDER_RADIUS_CORNER_MAP[ side ] || ( 'border-' + side + '-radius' );
	}
	return property + '-' + side;
};

/**
 * Build per-side ( or per-corner, for border-radius ) declarations for one device.
 *
 * - When unit === 'custom', side strings are emitted as-is ( assumed to include their own unit ).
 * - Otherwise the shared unit is appended to each non-empty numeric side.
 */
const buildDeclarations = function ( deviceVal, property ) {
	if ( !deviceVal ) {
		return '';
	}
	let out = '';
	const unit = deviceVal.unit || 'px';
	SIDES.forEach( ( s ) => {
		const v = deviceVal[ s ];
		if ( typeof v === 'undefined' || v === null || v === '' ) {
			return;
		}
		let css;
		if ( unit === 'custom' ) {
			css = '' + v;
		} else if ( /^-?\d*\.?\d+$/.test( ( '' + v ).trim() ) ) {
			css = '' + v + unit;
		} else {
			// Defensive: a non-numeric stored value with a non-custom unit – emit as-is.
			css = '' + v;
		}
		out += sidePropertyFor( property, s ) + ':' + css + ';';
	} );
	return out;
};

/**
 * Generate responsive CSS for an advanced dimension value.
 *
 * @param {Object} value     - Value produced by PortoAdvancedDimensionControl.
 * @param {string} selector  - CSS selector ( without leading dot / hash ). Will be prefixed with "html ." like other Porto controls.
 * @param {string} property  - CSS property the dimension drives. Recognised values:
 *                              - "margin"        → margin-top / -right / -bottom / -left
 *                              - "padding"       → padding-top / -right / -bottom / -left
 *                              - "border"        → border-top-width / -right-width / -bottom-width / -left-width
 *                              - "border-width"  → alias of "border"
 *                              - "border-radius" → border-top-left-radius ( top ) / -top-right-radius ( right )
 *                                                  / -bottom-right-radius ( bottom ) / -bottom-left-radius ( left )
 *                              - anything else   → <property>-<side> ( generic fallback )
 * @param {Object} options   - Optional. { tabletBreakpoint: 991, mobileBreakpoint: 767, raw: false }
 *                              When raw=true, "selector" is used verbatim ( useful for "html #block-xxx" ).
 *
 * @return {string} The generated CSS string.
 */
export const portoGenerateAdvancedDimensionCSS = function ( value, selector, property, options = {} ) {
	if ( !value || !selector || ( !property && '' !== property ) ) {
		return '';
	}
	// Transparently migrate legacy non-responsive dimension values ( treat them as desktop ).
	if ( isLegacyDimension( value ) ) {
		value = migrateLegacyDimension( value );
	}
	// `selector` may arrive already-finalized from portoGenerateStyleOptionsCSS
	// ( "#block-xxx", "html .cls", "html #id" ) or as a bare class from a direct
	// caller. Treat anything already carrying an id or an "html " prefix as raw so we
	// never double-prefix. The old code only special-cased "#", so an already-prefixed
	// "html .cls" became "html .html .cls" and matched nothing — that broke margin,
	// padding, border, border-radius and position-offset in the editor for every block
	// that uses a class-based style-options selector.
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

	const desktopDecl = buildDeclarations( value.desktop, property );
	if ( desktopDecl ) {
		css += sel + '{' + desktopDecl + '}';
	}

	const tabletDecl = buildDeclarations( value.tablet, property );
	if ( tabletDecl ) {
		css += '@media (max-width:' + tabletBp + 'px){' + sel + '{' + tabletDecl + '}}';
	}

	const mobileDecl = buildDeclarations( value.mobile, property );
	if ( mobileDecl ) {
		css += '@media (max-width:' + mobileBp + 'px){' + sel + '{' + mobileDecl + '}}';
	}

	return css;
};

// Exposed for callers that want to detect "custom" content on legacy / migrated values.
export const portoIsCustomDimensionValue = isCustomLikeValue;

// Exposed legacy-format helpers so block code can detect / migrate old saved attributes.
export const portoIsLegacyDimension = isLegacyDimension;
export const portoMigrateLegacyDimension = migrateLegacyDimension;
