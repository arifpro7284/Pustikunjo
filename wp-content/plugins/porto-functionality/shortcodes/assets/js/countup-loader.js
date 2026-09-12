
if (typeof portoInitStatCounter == 'undefined') {
    function portoInitStatCounter($elements) {
        'use strict';

        if (typeof $elements == "undefined") {
            $elements = jQuery("body");
        }
        var $stats = $elements.find( '.stats-block' );

        var initCounter = function(obj) {
            if (typeof obj == 'undefined') {
                obj = this;
            }
            var $obj = jQuery(obj),
                $num_obj = $obj.find('.stats-number'),
                endNum = parseFloat($num_obj.attr('data-counter-value'));

            if ( ! $num_obj.length || $num_obj.data('countup-started') ) {
                return;
            }
            $num_obj.data('countup-started', true);

            var Num = ($num_obj.attr('data-counter-value'))+' ';
            var speed = parseInt($num_obj.attr('data-speed'));
            var ID = $num_obj.attr('data-id');
            var sep = $num_obj.attr('data-separator');
            var dec = $num_obj.attr('data-decimal');
            var dec_count = Num.split(".");
            if(dec_count[1]){
                dec_count = dec_count[1].length-1;
            } else {
                dec_count = 0;
            }
            var grouping = true;
            if(dec == "none"){
                dec = "";
            }
            if(sep == "none"){
                grouping = false;
            } else {
                grouping = true;
            }
            var settings = {
                useEasing : false,
                useGrouping : grouping,
                separator : sep,
                decimal : dec
            }
            if ( ! ID ) {
                ID = $num_obj.get(0);
            }
            var counter = new countUp($num_obj.get(0), 0, endNum, dec_count, speed, settings),
                endTrigger = function() {
                    var $suffix_obj = typeof ID == 'string' ? jQuery('#' + ID).next('.counter_suffix') : $num_obj.next('.counter_suffix');
                    if ($suffix_obj.length) {
                        $suffix_obj.css('display', 'inline');
                    }
                };
            setTimeout(function(){
                counter.start(endTrigger);
            },500);
        };

        if (window.theme && theme.intObs) {
            theme.intObs(jQuery.makeArray($stats), initCounter, -50);
        } else {
            $stats.each(function() {
                initCounter(this);
            });
        }
    }
}

jQuery(document).ready(function($) {
    'use strict';

    portoInitStatCounter();
    $(document.body).on('porto_refresh_vc_content', function(event, $elements) {
        portoInitStatCounter($elements);
    });
    // Owl: run counter when slide is active (init / refresh / translate)
    $(document).on('refreshed.owl.carousel translated.owl.carousel', '.owl-carousel', function(e) {
        var $carousel = $(e.currentTarget);
        if ( $carousel.find('.owl-item.active .stats-block').length && typeof portoInitStatCounter === 'function' ) {
            portoInitStatCounter( $carousel.find('.owl-item.active') );
        }
    });

    // Swiper: hook into instance API (Swiper doesn't fire jQuery events on DOM)
    function bindSwiperCounters() {
        $('.swiper, .swiper-container').each(function() {
            var $el = $(this);
            if ( ! $el.find('.stats-block').length || $el.data('porto-counter-swiper-bound') ) { return; }
            var swiper = $el.data('swiper');
            if ( ! swiper ) { return; }
            $el.data('porto-counter-swiper-bound', true);
            var runCounter = function() {
                var $active = $el.find('.swiper-slide-visible');
                if ( $active.length && typeof portoInitStatCounter === 'function' ) {
                    portoInitStatCounter( $active );
                }
            };
            runCounter();
            swiper.on('slideChangeTransitionEnd', runCounter);
        });
    }
    bindSwiperCounters();
    $(document.body).on('porto_after_async_init', function() {
        setTimeout(bindSwiperCounters, 100);
    });

});