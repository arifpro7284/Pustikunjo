

jQuery(document).ready(function ($) {
    const container = document.getElementsByClassName(params['container']);
    jQuery(container).packery({
        // options
        itemSelector: ".highlight-item",
        gutter: 1,
    });
});
