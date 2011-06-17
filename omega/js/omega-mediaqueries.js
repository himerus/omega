/**
 * @todo
 */

Drupal.omega = Drupal.omega || {};

(function($) {
  /**
   * @todo
   */
  var current;
  
  /**
   * @todo
   */
  var dummy = $('<div id="omega-media-query-dummy"></div>');
  
  /**
   * @todo
   */
  Drupal.omega.isActiveLayout = function (layout) {
    if (Drupal.omega.crappyBrowser()) {
      return layout == Drupal.settings.omega.primary;
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
    var dummy = $('<div id="omega-check-query"><style media="' + query + '">#omega-check-query { content: "active"; }</style></div>').prependTo('body');
    var output = dummy.css('content') == 'active';

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
    dummy.prependTo('body');    
    dummy.append('<style media="all">#omega-media-query-dummy { content: "mobile"; }</style>');
    dummy.append('<!--[if (lt IE 9)&(!IEMobile)]><style media="all">#omega-media-query-dummy { content: "' + Drupal.settings.omega.layouts.primary + '"; }</style><![endif]-->');
    
    for (var i in Drupal.settings.omega.layouts.queries) {
      dummy.append('<style media="' + Drupal.settings.omega.layouts.queries[i] + '">#omega-media-query-dummy { content: "' + i + '"; }</style>');
    }

    current = dummy.css('content');
  });

  /**
   * @todo
   */
  $(window).resize(function() {
    if (dummy.css('content') != current) {
      $.event.trigger('layoutchanged', current = dummy.css('content'));
    }
  });
})(jQuery);