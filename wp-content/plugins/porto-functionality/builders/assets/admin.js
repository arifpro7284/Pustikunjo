jQuery(document).ready(function($) {
	$('a[href*="post-new.php?post_type=porto_builder"]').on('click', function(e) {
		$.magnificPopup.open({
			items: {
				src: '#porto-builders-input'
			},
			type: 'inline',
			mainClass: 'mfp-with-zoom',
			zoom: {
				enabled: true,
				duration: 300
			},
			callbacks: {
				open: function() {
					setTimeout(function() {
						$('#porto-builders-input input[name="builder_name"]').focus();
					}, 100);
				}
			}
		});
		e.preventDefault();
	});

	if ( ! $('a[href*="post-new.php?post_type=porto_builder"]').length ) {
		return;
	}
	let $builderType = $( '#porto-builders-input select[name=builder_type]' );
	let $headerType = $( '#porto-builders-input select[name=header_type]' );
	let $valueHeader = $( '#porto-builders-input [data-value=header]' );
	let $valueType = $( '#porto-builders-input [data-value=type]' );
	let $valueTerm = $( '#porto-builders-input [data-value=term]' );
	let $typeTerm = $( '#porto-builders-input select[name=content_type]' );
	let $valueSide = $( '#porto-builders-input [data-value=side]' );
	let $previewType = $( '#porto-builders-input [data-value=single_archive]' );

	if ( 'header' == $builderType.val() ) {
		$valueHeader.show();
	} else {
		$valueHeader.hide();
	}

	if ( 'type' == $builderType.val() ) {
		$valueType.show();
	} else {
		$valueType.hide();
	}

	if ( 'single' == $builderType.val() || 'archive' == $builderType.val() ) {
		$previewType.show();
	} else {
		$previewType.hide();
	}	

	if ( 'header' == $builderType.val() && 'side' == $headerType.val() ) {
		$valueSide.show();
	} else {
		$valueSide.hide();
	}
	if ( 'type' == $builderType.val() && 'term' == $typeTerm.val() ) {
		$valueTerm.show();
	} else {
		$valueTerm.hide();
	}	

	$( 'body' ).on( 'change', '#porto-builders-input select[name=builder_type]', function() {
		if ( 'header' == $( this ).val() ) {
			$valueHeader.show();
			if ( 'side' == $headerType.val() ) {
				$valueSide.show();
			} else {
				$valueSide.hide();
			}
		} else {
			$valueHeader.hide();
			$valueSide.hide();
		}
		if ( 'type' == $( this ).val() ) {
			$valueType.show();
			if ( 'term' == $typeTerm.val() ) {
				$valueTerm.show();
			} else {
				$valueTerm.hide();
			}
		} else {
			$valueType.hide();
			$valueTerm.hide();
		}

		if ( 'single' == $( this ).val() || 'archive' == $( this ).val() ) {
			$previewType.show();
		} else {
			$previewType.hide();
		}
	} );
	$( 'body' ).on( 'change', '#porto-builders-input select[name=header_type]', function() {
		if ( 'header' == $builderType.val() && 'side' == $headerType.val() ) {
			$valueSide.show();
		} else {
			$valueSide.hide();
		}
	} );
	$( 'body' ).on( 'change', '#porto-builders-input select[name=content_type]', function() {
		if ( 'type' == $builderType.val() && 'term' == $typeTerm.val() ) {
			$valueTerm.show();
		} else {
			$valueTerm.hide();
		}
	} );
});