jQuery(document).ready(function ($) {
    let prevButton; let nextButton

    let enableControls = slick_settings.enable_controls === "1"

    if(enableControls){
        prevButton =
            '<div class="fa fa-chevron-left slick-view-arrow-prev" style="display: none" aria-label="Previous"></div>';
        nextButton =
            '<div class="fa fa-chevron-right slick-view-arrow-next" style="display: none" aria-label="Next"></div>';
    } else {
        prevButton =
            '<div class="" style="width: 0; height: 0; display: none"></div>';
        nextButton = prevButton
    }

    jQuery('.slick_slider_container').slick({
        infinite: true,
        autoplay: !enableControls,
        autoplaySpeed: enableControls ? 0 : Number(slick_settings.slick_sliding_speed),
        slidesToShow: Number(slick_settings.slick_slides_to_show),
        slidesToScroll: Number(slick_settings.slick_slides_to_show),
        prevArrow: prevButton,
        nextArrow: nextButton
    });
});
