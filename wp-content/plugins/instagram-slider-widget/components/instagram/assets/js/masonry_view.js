/* int gutter */

jQuery(document).ready(function($) {
	const $container = $('.masonry_container');


	// We hold while all images will be loaded than will initialize masonry plugin.
	// Plugin imagesLoaded detects images when they have been loaded.
	// imagesloaded.pkgd.js

	// todo: I would like to add preloader for images grid,
	// todo: so as grid looks terrible now, while images haven't been loaded.

	$container.imagesLoaded(function() {
		$container.masonry({
			gutter: Number(10),
			columnWidth: 60,
			itemSelector: '.wis-item'
		});
	});
});
