/**
 * Porto – opt control components into the 40px default size.
 *
 * WordPress 6.8 deprecated the 36px default size of SelectControl, TextControl,
 * RangeControl, ToggleGroupControl, … and asks consumers to set
 * `__next40pxDefaultSize` until it becomes the default in 7.1. Rather than add
 * that prop to every one of the hundreds of call sites, wrap the component once
 * so every render inherits the modern default ( callers can still override it ).
 *
 * The wrapper is memoized by the original component so the wrapped component has
 * a STABLE identity even when this helper is called inside a render function –
 * otherwise React would treat it as a new component type on every render and
 * remount the control ( losing input focus ).
 */
const _portoNext40Cache =
	typeof WeakMap !== 'undefined' ? new WeakMap() : null;

const portoNext40 = function ( Control ) {
	if ( ! Control ) {
		return Control;
	}
	if ( _portoNext40Cache && _portoNext40Cache.has( Control ) ) {
		return _portoNext40Cache.get( Control );
	}
	const Wrapped = function ( props ) {
		return wp.element.createElement(
			Control,
			Object.assign( { __next40pxDefaultSize: true }, props )
		);
	};
	if ( _portoNext40Cache ) {
		_portoNext40Cache.set( Control, Wrapped );
	}
	return Wrapped;
};

export default portoNext40;