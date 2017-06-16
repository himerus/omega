(function ($, Modernizr, Drupal, drupalSettings, window) {

  "use strict";

  // @todo: Figure out how to assign the 'default' 'ALL' breakpoint.
  drupalSettings.omega.currentBreakpoints = {};

  let breakpoints;
  let breakpointMatch;

  /**
   * Function to handle returning the number of breakpoints currently active.
   *
   * @returns {number}
   */
  let omegaActiveBreakpoints = function(){
    let breakpoint = 0;

    $.each(drupalSettings.omega_breakpoints, function(index, object) {
      if (window.matchMedia(object.query).matches) {
        breakpoint++;
      }
    });

    // Return the active number of breakpoints.
    return breakpoint;
  };

  /**
   * Function to handle determining if the breakpoint being viewed is mobile or not.
   *
   * This function requires an additional value in THEME.breakpoints.yml for each assigned breakpoint.
   *
   * @see omega.breakpoints.yml for example:
   * @code
   * isMobile: true
   * isMobile: false
   * @endcode
   *
   * @param bp
   * @returns {boolean} Mobile = true; Not Mobile = false;
   */
  let omegaIsMobileBreakpoint = function(bp = false) {
    let mobile = false;
    if (bp) {
      // Check over the specific breakpoint supplied via argument to see if it is set as isMobile.
      if (drupalSettings.omega_breakpoints[bp].isMobile === true) {
        mobile = true;
      }
    }
    else {
      // Check over ALL the active breakpoints to see if any are set as isMobile.
      $.each(drupalSettings.omega.currentBreakpoints, function(index, object) {
        if (object.isMobile === true && object.status === true) {
          mobile = true;
        }
      });
    }

    return mobile;
  };

  /**
   * Function to test the implementation of the isMobile flag in THEME.breakpoints.yml.
   */
  Drupal.behaviors.testMobile = {
    attach: function(context, settings) {
      $(window).on('breakpointAdded', function(e, b){
        if (omegaIsMobileBreakpoint(b.bp)) {
          // console.log(b.name + ' IS a mobile breakpoint...');
        }
        else {
          // console.log(b.name + ' IS NOT a mobile breakpoint...');
        }
      });
    }
  };

  /**
   * Make toolbar play nice with Omega breakpoints.
   * This seems to need to live outside of a Drupal.behaviors function.
   * @todo: Figure out a way to assign breakpoint media queries via variable.
   * @todo: Figure out how to fire/adjust the breakpoints the 'right' way and via behavior.
   */
  if (drupalSettings.toolbar) {
    drupalSettings.toolbar.breakpoints = {
      'toolbar.narrow': 'all', // This is the 'fixed' version that overlays content.
      'toolbar.standard': 'none', // REMOVE the horrid version that shifts the content.
      'toolbar.wide': 'all and (min-width: 1024px)' // This is the normal version.
    };
  }
  /**
   * Adjust the Drupal toolbar for better responsiveness.
   *
   * This function accomplishes the following enhancements to the default Drupal toolbar's responsiveness:
   * - Line up the drupalSettings.toolbar.breakpoints to match default Omega breakpoints.
   * - Disable the toolbar.standard mode which shifts content when the viewport becomes 'small', and instead opt
   *   for the 'dropdown' (fixed position) on ALL screen sizes below 1024px. (to be made variablized)
   * - Utilize the custom isMobile flag inside THEME.breakpoints.yml to handle CLOSING the toolbar tray when we reach
   *   a breakpoint that is denoted as 'mobile'.
   *   @see omega.breakpoints.yml
   * - Handle various scenarios, storing a new variable drupalSettings.toolbar.lastActive in both active settings and
   *   local storage to ensure proper continuity of intended visible/hidden trays.
   */
  Drupal.behaviors.toolbarResponsiveEnhance = {
    attach: function (context, settings) {

      $(window).on('breakpointAdded', function(e, b){
        if ($(Drupal.toolbar.models.toolbarModel.get('activeTab')).size() > 0) {
          // Let's try to store the last active item for user later.
          let lastTabId = $(Drupal.toolbar.models.toolbarModel.get('activeTab')).attr('id');
          drupalSettings.toolbar.lastActive = lastTabId;

          localStorage.setItem('drupalSettings.toolbar.lastActive', drupalSettings.toolbar.lastActive);
        }
        else {
          // There is NOT an active tab, so we should determine what it should be.
          let lastItem = localStorage.getItem('drupalSettings.toolbar.lastActive');

          if (lastItem) {
            // There isn't a currently set item, but we have one in storage from responsive closing of it.
            let lastLink = $('.toolbar-item[id="' + lastItem + '"]');
            drupalSettings.toolbar.lastActive = lastLink.attr('id');
          }
          else {
            // There is not a currently set active item, and there is not one stored via variable/local storage.
            // Here we will set this to the default 'manage' tab.
            let maninTabId = $('#toolbar-item-administration').attr('id');
            drupalSettings.toolbar.lastActive = maninTabId;
            localStorage.setItem('drupalSettings.toolbar.lastActive', drupalSettings.toolbar.lastActive);
          }
        }

        // Let's close the open toolbar menu rather than it switching to vertical
        if (omegaIsMobileBreakpoint(b.bp)) {
          // Close any open toolbar administration trays.
          Drupal.toolbar.models.toolbarModel.set('activeTab', null);
        }
        else {
          // Reopen any previously closed toolbar administration trays.
          let thisToolbarItem = $('.toolbar-item[id="' + drupalSettings.toolbar.lastActive + '"]');
          Drupal.toolbar.models.toolbarModel.set('activeTab', thisToolbarItem);

        }
      });

      // Handle the event where a tray is open, then a breakpoint is scaled to that hides the tray, then a different
      // item is opened or opened/closed so that the last one clicked/opened/closed is the new lastActive tray.
      $('.toolbar-tab > a.toolbar-item').click(function(){
        let clickedTabId = $(this).attr('id');
        drupalSettings.toolbar.lastActive = clickedTabId;
        localStorage.setItem('drupalSettings.toolbar.lastActive', drupalSettings.toolbar.lastActive);
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
              isMobile: value.isMobile,
            };
            $.event.trigger('breakpointAdded', {
              name: value.name,
              query: value.query,
              bp: value.system,
              isMobile: value.isMobile,
            });
          }
          else {
            drupalSettings.omega.currentBreakpoints[bp] = {
              name: value.name,
              status: false,
              bp: value.system,
              isMobile: value.isMobile,
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
                query: value.query,
                bp: value.system,
                isMobile: value.isMobile,
              });
            }
          }
          else {
            // if it was already active
            if (drupalSettings.omega.currentBreakpoints[bp].status) {
              drupalSettings.omega.currentBreakpoints[bp].status = false;
              $.event.trigger('breakpointRemoved', {
                name: value.name,
                query: value.query,
                bp: value.system,
                isMobile: value.isMobile,
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
