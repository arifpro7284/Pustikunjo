(function ($) {
    $(document).ready(function ($) {
        $('.wis-shortcode-input').on('click', function () {
		const range = document.createRange();
		range.selectNodeContents(this);

		const selection = window.getSelection();
		selection.removeAllRanges();
		selection.addRange(range);
	});
    });
})(jQuery);