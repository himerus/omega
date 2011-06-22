/**
 * @todo
 */

Drupal.omega = Drupal.omega || {};

(function($) {
  /**
   * @todo
   */
  var current;
  var last;
  
  /**
   * @todo
   */
  var setCurrentLayout = function (index) {
    index = parseInt(index);
    last = current;
    current = Drupal.settings.omega.layouts.order.hasOwnProperty(index) ? Drupal.settings.omega.layouts.order[index] : 'mobile';

    if (last != current) {      
      $('body').removeClass('responsive-layout-' + last).addClass('responsive-layout-' + current);      
      $.event.trigger('responsivelayout', {from: last, to: current});
    }
  };
  
  /**
   * @todo
   */
  Drupal.omega.isActiveLayout = function (layout) {
    if (Drupal.omega.crappyBrowser()) {
      return layout == Drupal.settings.omega.layouts.primary;
    }
    else if (Drupal.settings.omega.layouts.queries.hasOwnProperty(layout) && Drupal.settings.omega.layouts.queries[layout]) {
      return Drupal.omega.checkQuery(Drupal.settings.omega.layouts.queries[layout]);
    }

    return false;
  };
  
  /**
   * @todo
   */
  Drupal.omega.getCurrentLayout = function () {
    return current;
  };
  
  /**
   * @todo
   */
  Drupal.omega.checkQuery = function (query) {
    var dummy = $('<div id="omega-check-query"><style media="' + query + '">#omega-check-query { position: relative; z-index: 100; }</style></div>').prependTo('body');
    var output = parseInt(dummy.css('z-index')) == 100;

    dummy.remove();

    return output;
  };

  /**
   * @todo
   */
  Drupal.omega.crappyBrowser = function () {
    return $.browser.msie && parseInt($.browser.version, 10) < 9;
  };
  
  /**
   * @todo
   */
  $(function() {    
    var primary = $.inArray(Drupal.settings.omega.layouts.primary, Drupal.settings.omega.layouts.order);
    var dummy = $('<div id="omega-media-query-dummy"></div>').prependTo('body');

    dummy.append('<style media="all">#omega-media-query-dummy { position: relative; z-index: -1; }</style>');
    dummy.append('<!--[if (lt IE 9)&(!IEMobile)]><style media="all">#omega-media-query-dummy { z-index: ' + primary + '; }</style><![endif]-->');

    for (var i in Drupal.settings.omega.layouts.order) {
      dummy.append('<style media="' + Drupal.settings.omega.layouts.queries[Drupal.settings.omega.layouts.order[i]] + '">#omega-media-query-dummy { z-index: ' + i + '; }</style>');
    }
    
    setCurrentLayout(dummy.css('z-index'));
    
    $(window).bind('resize.omega', function() { 
      setCurrentLayout(dummy.css('z-index')); 
    });
  });
})(jQuery);