jQuery( document ).ready(function($) {

    const prevButton =
        '<div class="fa fa-chevron-left remodal-view-arrow-prev" style="display: none" aria-label="Previous"></div>';
    const nextButton =
        '<div class="fa fa-chevron-right remodal-view-arrow-next" style="display: none" aria-label="Next"></div>';
    jQuery('.wis-remodal-slick-container').slick({
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        prevArrow: prevButton,
        nextArrow: nextButton
    });

    jQuery(document).on('opening', '.remodal', function () {
        console.log('Modal is opened');
        jQuery('.wis-remodal-slick-container').slick('setPosition');
        jQuery('video.remodal-media').removeAttr('style');
    });
    jQuery(document).on('opened', '.remodal', function () {
        jQuery('video.remodal-media').removeAttr('style');
    });

    jQuery(".wis-video").click(function(){
        if(this.paused){
           this.play();
           console.log("fasfa");
            jQuery(".wis-play-icon").fadeOut();
            jQuery(".wis-icon-play").fadeOut();
        } else {
            this.pause();
            console.log("fasfa");
            jQuery(".wis-play-icon").fadeIn();
            jQuery(".wis-icon-play").fadeIn();
        }
    });

    /********************************
    * Остановка воспроизведения при закрытии и переключении слайда
    *********************************/
    function video_pause()
    {
        jQuery(".wis-video").each(function(){
            this.pause();
        });
        jQuery(".wis-video2").each(function(){
            this.pause();
        });
    }

    jQuery(document).on('closing', '.remodal', function (e) {
        video_pause();
    });
    $('.wis-remodal-slick-container').on('afterChange', function(slick, currentSlide){
        video_pause();
    });
    /*********************************/

    jQuery(".wis-remodal-share").click(function () {
        var block_share_buttons = jQuery(".wis-remodal-share-buttons");
        if(block_share_buttons.css('display') === 'block')
            block_share_buttons.css({'display' : 'none'});
        else
            block_share_buttons.css({'display' : 'block'});
    });
});

