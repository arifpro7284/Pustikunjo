jQuery(document).ready(function($) {
    const wis_get_more_button = $('.wis-show-more'); // берем кнопку

    wis_get_more_button.on('click', function (e) { // по клику вызываем аякс
        jQuery.ajax({
            type: "POST",
            url: ajaxurl.url, // url, прокинутый из админки, который ссылается на admin-ajax
            data: {
                'action': 'wis-get-more', // вызываем нужный экшен
                'template': wis_get_more_button.attr('data-template'), // шаблон для рендера
                'next_page': wis_get_more_button.attr('data-next-page'), // свойство для след страницы запроса
                'username': wis_get_more_button.attr('data-username'), // имя аккаунта
                'images_count': wis_get_more_button.attr('data-img-cnt'), // количество изображений, показываемых за 1 раз
                'link_to': wis_get_more_button.attr('data-linkto'), // на что будет вести картика (popup, пост inst, custom url)
                'caption_words': wis_get_more_button.attr('data-caption-words'), // количество слов под картинкой (для masonry)
                'masonry_image_width': wis_get_more_button.attr('data-masonry-width'), // ширина картинок (для masonry)
                'masonry_lite_cols': wis_get_more_button.attr('data-masonry_cols'), // Количество столбцов (для masonry Light)
                'masonry_lite_gap': wis_get_more_button.attr('data-masonry_gap'),
                'image_size': wis_get_more_button.attr('data-image-size'),
                '_nonce': ajaxurl.nonce, // уникальный ключ, по которому будет определяться правильность источника запроса
            },
            success: function(data){
                const items = data;
                if(wis_get_more_button.attr('data-template') === 'masonry'){
                    const container = $('.masonry_container');
                    container
                        .append(items.data.result)
                        .masonry( 'appended', {
                            gutter: Number(10),
                            columnWidth: 60,
                            itemSelector: '.wis-item'
                        });
                } else if(wis_get_more_button.attr('data-template') === 'highlight') {
                    const container = $('.wisw-highlight-container');
                    container
                        .append(items.data.result)
                        .packery('appended', {
                            // options
                            itemSelector: ".highlight-item",
                            gutter: 1,
                        });
                }
            },
        });
    });
});