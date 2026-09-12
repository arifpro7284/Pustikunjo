jQuery( document ).ready(function($) {
    $('.remodal-wyt-share').click(function (){
        var block_share_buttons = jQuery(".remodal-wyt-share-buttons");
        if(block_share_buttons.css('display') === 'block')
            block_share_buttons.css({'display' : 'none'});
        else
            block_share_buttons.css({'display' : 'block'});
    })

    jQuery(document).on('closing', '.remodal', function (e) {
        $('.remodal-wyt-video-iframe').each(function(){
            this.contentWindow.postMessage('{"event":"command","func":"stopVideo","args":""}', '*')
        });
    });
});

