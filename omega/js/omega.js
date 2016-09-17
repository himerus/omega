(function ($, Modernizr, Drupal, drupalSettings, window) {

    "use strict";

    drupalSettings.omega.currentBreakpoints = {
        'All': true
    };

    var breakpoints;
    var breakpointMatch;

    Drupal.behaviors.omegaBreakpoint = {
        attach: function (context, settings) {
            // return if not viewing on screen
            if (!window.matchMedia('only screen').matches) {
                return;
            }
            breakpoints = drupalSettings.omega_breakpoints;
            breakpointMatch = false;

            // Handle the intial load
            $(window).on('load', function () {
                $.each(breakpoints, function () {
                    if (window.matchMedia(this.query).matches) {
                        breakpointMatch = true;
                        drupalSettings.omega.currentBreakpoints[this.name] = true;
                        $.event.trigger('breakpointAdded', {
                            name: this.name,
                            query: this.query
                        });
                    }
                    else {
                        drupalSettings.omega.currentBreakpoints[this.name] = false;
                        // don't trigger the event since it is on page load, just rely on setting it to false above.
                        //$.event.trigger('breakpointRemoved', {breakpoint: this.name, query: this.query});
                    }
                });
            });

            // handle resize events
            $(window).on('resize', function () {
                $.each(breakpoints, function () {
                    if (window.matchMedia(this.query).matches) {
                        breakpointMatch = true;
                        // if it wasn't already active
                        if (drupalSettings.omega.currentBreakpoints[this.name] != true) {
                            drupalSettings.omega.currentBreakpoints[this.name] = true;
                            $.event.trigger('breakpointAdded', {
                                name: this.name,
                                query: this.query
                            });
                        }
                    }
                    else {
                        // if it was already active
                        if (drupalSettings.omega.currentBreakpoints[this.name] == true) {
                            drupalSettings.omega.currentBreakpoints[this.name] = false;
                            $.event.trigger('breakpointRemoved', {
                                name: this.name,
                                query: this.query
                            });
                        }
                    }
                });

                // must be mobile or something shitty like IE8
                if (!breakpointMatch) {
                    breakpointMatch = false;
                    drupalSettings.omega.currentBreakpoints['all'] = true;
                }
            });
        }
    };

    // @todo - need to use some LocalStorage to keep the indicator open/closed based on last setting.
    Drupal.behaviors.indicatorToggle = {
        attach: function (context, settings) {

            $('#indicator-toggle').on('click', function () {
                if ($(this).hasClass('indicator-open')) {
                    $(this).removeClass('indicator-open').addClass('indicator-closed');
                    $('#omega-screen--indicator').animate({
                        opacity: 0.25,
                        right: '-280'
                    }, 500, function () {
                        // Animation complete.
                    });
                }
                else {
                    $(this).removeClass('indicator-closed').addClass('indicator-open');
                    $('#omega-screen--indicator').animate({
                        opacity: 1,
                        right: '0',
                        //height: "toggle"
                    }, 250, function () {
                        // Animation complete.
                    });

                }
                return false;
            });
        }
    };

    Drupal.behaviors.attachIndicatorData = {
        attach: function (context, settings) {
            // grab the wrapper element to manipulate
            var oScreen = $('#omega-screen--indicator');
            var screenWidth;
            var breakpointText;

            $(window).on('load resize', function () {
                screenWidth = $(this).width();
                var layout = drupalSettings.omega.activeLayout;
                oScreen.find('.screen-size .data').html(screenWidth + 'px');
                oScreen.find('.screen-layout .data').html(layout);
                oScreen.find('.theme-name .data').html(drupalSettings.omega.activeTheme);
            });

            // if a breakpiont has been added or removed, change the text
            $(window).on('breakpointAdded breakpointRemoved', function (e, b) {
                breakpointText = [];
                $.each(breakpoints, function () {
                    if (drupalSettings.omega.currentBreakpoints[this.name] == true) {
                        breakpointText.push(this.name);
                        var text = breakpointText.join(', ');
                        oScreen.find('.screen-query .data').html(text);
                    }
                });
            });
        }
    };

    /**
     * Toolbar methods of Backbone objects.
     */
    Drupal.omega = {

        // A hash of View instances.
        views: {},

        // A hash of Model instances.
        models: {}

    };

})(jQuery, Modernizr, Drupal, drupalSettings, window);
