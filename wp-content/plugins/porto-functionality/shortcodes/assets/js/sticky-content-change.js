/**
 * Sticky Content Change for WPBakery Page Builder
 * 
 * @package Porto Functionality
 * @since 3.4.0
 */

'use strict';

window.theme = window.theme || {};
 
 
(function ($, theme) {
    var stickyContentChange = function( $wrap ) {
        if ( $.fn.themeSticky ) {
            let stickyElement = $wrap.find( '.porto-sticky' );
            if ( stickyElement.length ) {
                if ( stickyElement.is( ':visible' ) ) {
                    var pluginOptions = stickyElement.attr( 'data-plugin-options' );
                    if ( typeof pluginOptions == 'string' ) {
                        try {
                            pluginOptions = JSON.parse( pluginOptions.replace( /'/g, '"' ).replace( ';', '' ) );
                        } catch ( e ) { }
                    }

                    if ( pluginOptions.containerSelector && $( ".gsap-content-marker" ).length && typeof gsap != 'undefined' ) {
                        /*
                        * Image Change
                        */
                        gsap.defaults({ overwrite: 'auto'});
                        // Set up our scroll trigger
                        const ST = ScrollTrigger.create({
                            trigger: pluginOptions.containerSelector,
                            start: "top top",
                            end: "bottom center",
                            onUpdate: getCurrentSection
                        });

                        const contentMarkers = gsap.utils.toArray(".gsap-content-marker");
                        $( pluginOptions.containerSelector ).addClass( 'gsap-sticky-initial' );
                        var firstEl;
                        // Set up our content behaviors
                        contentMarkers.forEach((marker, index) => {
                            marker.content = $(pluginOptions.containerSelector).get(0).querySelector(`#${marker.dataset.markerContent}`);
                            if ( marker.content ) {
                                if ( stickyElement.find( '.sticky-active-change-content' ).length == 0 && index == 0 ) {
                                    firstEl = marker.content;
                                } else {
                                    $( marker.content ).css( { 'position': 'absolute', 'top': 0, 'opacity': 0, 'visibility': 'hidden' } ); // Init
                                }
                                marker.content.enter = () => {
                                    gsap.fromTo(marker.content, {
                                        autoAlpha: 0
                                    }, {
                                        duration: 0.3,
                                        autoAlpha: 1
                                    });
                                }
                            
                                marker.content.leave = () => {
                                    gsap.to(marker.content, {
                                        duration: 0.1,
                                        autoAlpha: 0
                                    });
                                }
                            }
                        
                        });

                        // Handle the updated position
                        var lastContent;
                        function getCurrentSection() {
                            let newContent;
                            const currScroll = scrollY;

                            // Find the current section
                            contentMarkers.forEach(marker => {
                                if (currScroll > ($(marker).offset().top - 100)) {
                                    newContent = marker.content;
                                }
                            });
                        
                            // If the current section is different than that last, animate in
                            if (newContent &&
                                (lastContent == null ||
                                    !newContent.isSameNode(lastContent))) {
                                // Fade out last section
                                if (lastContent) {
                                    lastContent.leave();
                                }
                        
                                // Animate in new section
                                newContent.enter();
                                if ( $( pluginOptions.containerSelector ).hasClass( 'gsap-sticky-initial' ) ) {
                                    $( pluginOptions.containerSelector ).removeClass( 'gsap-sticky-initial' );
                                    $( firstEl ).css( { 'position': 'absolute', 'opacity': 0, 'visibility': 'hidden' } );
                                }

                                $( newContent ).siblings().css( 'position', 'absolute' ).removeClass( 'sticky-active-change-content' );
                                $( newContent ).css( 'position', 'relative' ).addClass( 'sticky-active-change-content' );
                                $( newContent ).closest( '.pin-wrapper' ).height( $( newContent ).height() );
                                lastContent = newContent;
                            }
                        
                        }

                    }
                }
            }
        }
    }

    if ( theme.isReady ) { // Finish init
        stickyContentChange( $( document.body ) );
    }
    $( document.body ).on( 'porto_init', function( e, $wrap ) {
        stickyContentChange( $wrap );
    } );
})(jQuery, window.theme);
