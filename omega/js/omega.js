(function ($, Modernizr, Drupal, drupalSettings, window) {

  "use strict";

  // @todo: Figure out how to assign the 'default' 'ALL' breakpoint.
  drupalSettings.omega.currentBreakpoints = {};

  var breakpoints;
  var breakpointMatch;

  if (drupalSettings.toolbar) {
    // Attempting to make toolbar play nice with Omega breakpoints.
    // @todo: Figure out a way to assign breakpoint media queries via variable.
    drupalSettings.toolbar.breakpoints = {
      'toolbar.narrow': 'all',
      'toolbar.standard': 'none', // HIDE the horrid version.
      'toolbar.wide': 'all and (min-width: 1220px)'
    };
  }

  /**
   * Adjust a few things that make the toolbar better to use.
   *
   * @type {{attach: Drupal.behaviors.mobileTriggers.attach}}
   */
  Drupal.behaviors.toolbarResponsiveEnhance = {
    attach: function (context, settings) {
      $(window).on('breakpointAdded breakpointRemoved', function(e, b){
        // Let's close the open toolbar menu rather than it switching to vertical

      });
    }
  };

  /**
   * Function to handle breakpoint triggers and adjustments.
   *
   * This function allows you to determine both the active breakpoints being used, as well as act upon the
   * breakpointAdded and breakpointRemoved triggers.
   *
   * @type {{attach: attach}}
   */
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
        $.each(breakpoints, function (bp, value) {
          if (window.matchMedia(value.query).matches) {
            breakpointMatch = true;
            drupalSettings.omega.currentBreakpoints[bp] = {
              name: value.name,
              status: true,
              bp: value.system,
            };
            $.event.trigger('breakpointAdded', {
              name: value.name,
              query: value.query,
              bp: value.system,
            });
          }
          else {
            drupalSettings.omega.currentBreakpoints[bp] = {
              name: value.name,
              status: false,
              bp: value.system,
            };
            // don't trigger the event since it is on page load, just rely on setting it to false above.
            //$.event.trigger('breakpointRemoved', {breakpoint: this.name, query: this.query});
          }
        });
      });

      // handle resize events
      $(window).on('resize', function () {
        $.each(breakpoints, function (bp, value) {
          if (window.matchMedia(value.query).matches) {
            breakpointMatch = true;
            // if it wasn't already active
            if (!drupalSettings.omega.currentBreakpoints[bp].status) {
              drupalSettings.omega.currentBreakpoints[bp].status = true;
              $.event.trigger('breakpointAdded', {
                name: value.name,
                query: value.query
              });
            }
          }
          else {
            // if it was already active
            if (drupalSettings.omega.currentBreakpoints[bp].status) {
              drupalSettings.omega.currentBreakpoints[bp].status = false;
              $.event.trigger('breakpointRemoved', {
                name: value.name,
                query: value.query
              });
            }
          }
        });

        // must be mobile or something shitty like IE8
        if (!breakpointMatch) {
          breakpointMatch = false;
          // @todo: Figure out how to assign the 'default' 'ALL' breakpoint.
          drupalSettings.omega.currentBreakpoints['all'] = {
            status: true,
          };
        }
      });
    }
  };

  // @todo: Should use some LocalStorage to keep the indicator open/closed based on last setting.
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
        $.each(breakpoints, function (bp, value) {
          if (drupalSettings.omega.currentBreakpoints[bp] && drupalSettings.omega.currentBreakpoints[bp].status) {
            breakpointText.push(value.name);
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
