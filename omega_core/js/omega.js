/**
 * @todo
 */

Drupal.omega = Drupal.omega || {};





(function($) {
//(function ($, Modernizr, Drupal, drupalSettings, window) {
  "use strict";
  /*
Drupal.settings.omega.currentBreakpoints = {
     'all' : true
   };
*/
  
  var breakpoints;
  var breakpointMatch;
  var breakpointText;
  var oScreen;
  var screenWidth;
  
  Drupal.omega = {
    currentBreakpoints: {
     'all' : true
    }
  };
  
  Drupal.omega.updateIndicatorBreakpoints = function(breakpoints, activeBreakpoints) {
    breakpointText = [];
    oScreen = $('#omega-screen--indicator');
    
    $.each(breakpoints, function() {    
      if (activeBreakpoints[this.name] == true) {
        breakpointText.push(this.name);
        var text = breakpointText.join(', ');
        oScreen.find('.screen-query .data').html(text);  
      }
    });
  };
  
  //console.log('Hello from Omega.js!!');
  /*
Drupal.behaviors.omegaMediaQueries = {
    attach: function (context) {
      $('body', context).once('omega-mediaqueries', function () {
        var primary = $.inArray(Drupal.settings.omega.layouts.primary, Drupal.settings.omega.layouts.order);
        var dummy = $('<div id="omega-media-query-dummy"></div>').prependTo('body');

        dummy.append('<style media="all">#omega-media-query-dummy { position: relative; z-index: -1; }</style>');
        dummy.append('<!--[if (lt IE 9)&(!IEMobile)]><style media="all">#omega-media-query-dummy { z-index: ' + primary + '; }</style><![endif]-->');

        for (var i in Drupal.settings.omega.layouts.order) {
          dummy.append('<style media="' + Drupal.settings.omega.layouts.queries[Drupal.settings.omega.layouts.order[i]] + '">#omega-media-query-dummy { z-index: ' + i + '; }</style>');
        }

        $(window).bind('resize.omegamediaqueries', function () {
          setCurrentLayout(dummy.css('z-index'));
        }).load(function () {
          $(this).trigger('resize.omegamediaqueries');
        });
      });
    }
  };
*/
  
  
  
  Drupal.behaviors.omegaBreakpoint = {
    attach: function (context) {
      
      $('body', context).once('omega-breakpoint', function () {
        
        // return if not viewing on screen
        if (!window.matchMedia('only screen').matches) {
          //console.log('This appears not to be a screen...');
          return;
        }
        
        breakpoints = Drupal.settings.omega_breakpoints.layouts;
        breakpointMatch = false;
        //console.log(breakpoints);
        
        // Handle the intial load
        $(window).ready( function() {
          $.each(breakpoints, function() {
            //console.log(this.query);
          	if (window.matchMedia(this.query).matches) {
          	  //console.log('matchMedia match found: ' + this.query);
              breakpointMatch = true;
              Drupal.omega.currentBreakpoints[this.name] = true;
              $('body').addClass('omega-breapoint--'+this.name);
              $.event.trigger('breakpointAdded', {name: this.name, query: this.query});
            }
            else {
              Drupal.omega.currentBreakpoints[this.name] = false;
            }
          });
          // run it once on page load
          Drupal.omega.updateIndicatorBreakpoints(breakpoints, Drupal.omega.currentBreakpoints);
          
          $( 'body' ).bind({
            breakpointAdded: function(query) {
              // do something when a breakpoint is added
            },
            breakpointRemoved: function(query) {
              // do something when a breakpoint is removed
            },
            breakpointUpdated: function() {
              // do something when breakpoints are updated
            }
          });
          
          $.event.trigger('breakpointUpdated', {});
        });
        
        // handle resize events
        $(window).resize( function() {
          var breakpointAdjust = false;
          
          $.each(breakpoints, function() {
          	
          	if (window.matchMedia(this.query).matches) {
          	  breakpointMatch = true;
              // if it wasn't already active
              if (Drupal.omega.currentBreakpoints[this.name] != true) {
                breakpointAdjust = true;
                Drupal.omega.currentBreakpoints[this.name] = true;
                $.event.trigger('breakpointAdded', {name: this.name, query: this.query});
                $('body').addClass('omega-breapoint--'+this.name);
              }
            }
            else {
              // if it was already active
              if (Drupal.omega.currentBreakpoints[this.name] == true) {
                breakpointAdjust = true;
                Drupal.omega.currentBreakpoints[this.name] = false;
                $.event.trigger('breakpointRemoved', {name: this.name, query: this.query});
                $('body').removeClass('omega-breapoint--'+this.name);
              }
            }
          });
          
          // if the breakpoints have been updated by adding or removing something, then fire breakpointUpdated
          if (breakpointAdjust) {
            $.event.trigger('breakpointUpdated', {});  
          }
          
          
          // must be mobile or something shitty like IE8
          if (!breakpointMatch) {
            breakpointMatch = false;
            Drupal.omega.currentBreakpoints['all'] = true;
          }
        });
      });
    }
  };
  
  
  Drupal.behaviors.attachIndicatorData = {
    attach: function (context) {
      // grab the wrapper element to manipulate
      oScreen = $('#omega-screen--indicator');
      
      
      $(window).ready(function(){
        screenWidth = $(this).width();
        var layout = Drupal.settings.omega.activeLayout;
        //console.log(screenWidth);
        oScreen.find('.screen-size .data').html(screenWidth + 'px');  
        oScreen.find('.screen-layout .data').html(layout);
        oScreen.find('.theme-name .data').html(Drupal.settings.omega.activeTheme);
        
      });
      
      $(window).resize(function(){
        //console.log(this);
        screenWidth = $(this).width();
        //console.log(screenWidth);
        oScreen.find('.screen-size .data').html(screenWidth + 'px');  
      });
      
      
      
      $('body', context).once('breakpoint', function () {
        
        breakpoints = Drupal.settings.omega_breakpoints.layouts;
        
        
        $( 'body' ).bind({
          breakpointAdded: function(query) {
            // do something when a breakpoint is added
            Drupal.omega.updateIndicatorBreakpoints(breakpoints, Drupal.omega.currentBreakpoints);
          },
          breakpointRemoved: function(query) {
            // do something when a breakpoint is removed
            Drupal.omega.updateIndicatorBreakpoints(breakpoints, Drupal.omega.currentBreakpoints);
          },
          breakpointUpdated: function() {
            // do something when  breakpoints are updated
          }
        });
      });
    }
  };
  
  
  // need to use some LocalStorage to keep the indicator open/closed based on last setting.
  
  Drupal.behaviors.indicatorToggle = {
    attach: function (context) {
      
      $('#indicator-toggle').click( function() {
        if ($(this).hasClass('indicator-open')) {
          $(this).removeClass('indicator-open').addClass('indicator-closed');
          //$('#omega-screen--indicator').css('right', '-280px');
          
          $('#omega-screen--indicator').animate({
            opacity: 0.25,
            right: '-280',
            //height: "toggle"
          }, 500, function() {
            // Animation complete.
          });
          
        }
        else {
          $(this).removeClass('indicator-closed').addClass('indicator-open');
          //$('#omega-screen--indicator').css('right', '0');
          
          $('#omega-screen--indicator').animate({
            opacity: 1,
            right: '0',
            //height: "toggle"
          }, 250, function() {
            // Animation complete.
          });
          
        }
        return false;
      });
    }
  };
  
  
  
  
})(jQuery);
//})(jQuery, Modernizr, Drupal, drupalSettings, window);
