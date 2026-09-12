const PortoAjaxSelect2Control = function( props ) {
	const useEffect = wp.element.useEffect,
		useRef = wp.element.useRef;
	let $select = useRef( null );
	useEffect(
		() => {
			const option = props.option,
				ids = props.value,
				$el = jQuery($select.current),
				is_multiple = (typeof $el.attr('multiple') != 'undefined');

			const path = porto_block_vars.site_url + '/wp-json/ajaxselect2/v1/' + option + '/';
			$el.select2({
				ajax: {
					url: path,
					dataType: 'json',
					data: function (params) {
						var args = {
							s: params.term
						};
						if (!is_multiple) {
							args['add_default'] = '1';
						}
						return args;
					}
				},
				cache: true
			}).on('change', function(e) {
				if ( is_multiple ) {
					var $a = jQuery(e.target);
					props.onChange( $a.val().join(',') );
				} else {
					props.onChange( e.target.value );
				}
			});

			jQuery.ajax({
				url: path,
				dataType: 'json',
				data: {
					ids: ids ? ids : ''
				}
			}).then(function (res) {

				if (null !== res && res.results.length > 0) {
					let _opts = $el.get(0).options;
					let _opt_values = [];
					for ( let i = 0; i < _opts.length; i++ ) {
						_opt_values.push( parseInt( _opts[i].value ) );
					}
					res.results.map((v, i) => {
						if ( _opt_values.findIndex( item => item === v.id ) == -1) {
							$el.append(new Option(v.text, v.id, true, true)).trigger('change');
						}
					});
					$el.trigger({
						type: 'select2:select',
						params: {
							data: res
						}
					});
				}
			});

			return () => {
				if ($el.data('select2')) {
					$el.select2('destroy');
				}
			}
		},
		// Re-initialize when the endpoint ( option ) changes - e.g. the Terms control's option is
		// `{post_tax}_allterm`, so switching Taxonomies must rebuild select2 against the new taxonomy.
		[ props.option ]
	);

	const { label, multiple } = props;
	const attrs = {};
	if ( multiple ) {
		attrs.multiple = 1;
	}
	return (
		<div className="components-base-control porto-ajaxselect2">
			<label className="components-input-control__label css-1wgusda-Text-BaseLabel">
			{ label }
			</label>
			<select ref={ $select } {...attrs}>
			</select>
			{ props.help && (
				<p className="css-1wm1a55-StyledHelp">
					{ props.help }
				</p>
			) }
		</div>
	);
};

export default PortoAjaxSelect2Control;