/**
 * @todo
 */

Drupal.omega = Drupal.omega || {};

(function($) {  
  /**
   * @todo
   */
  Drupal.omega.isActiveLayout = function (layout, strict) {
    strict = strict || false;

    if (Drupal.omega.isCrappyBrowser()) {
      return layout == Drupal.settings.omega.primary;
    }
    else if (Drupal.settings.omega.layouts.queries.hasOwnProperty(layout) && Drupal.settings.omega.layouts.queries[layout]) {
      if (Drupal.omega.mediaQueryApplies(Drupal.settings.omega.layouts.queries[layout])) {
        if (!strict) { 
          return true;
        }
        else {
          for (var i = Drupal.settings.omega.layouts.order.length - 1; i >= 0; i--) {
            if (Drupal.settings.omega.layouts.order[i] == layout) {
              return true;
            }
            else if (Drupal.omega.mediaQueryApplies(Drupal.settings.omega.layouts.queries[Drupal.settings.omega.layouts.order[i]])) {
              return false;
            }
          }
        }
      }
    }

    return false;
  };
  
  /**
   * @todo
   */
  Drupal.omega.mediaQueryApplies = function (query) {
    var injection = $('<div id="omega-media-query-dummy"><style media="' + query + '">#omega-media-query-dummy { content: "omega"; }</style></div>').hide().prependTo('body');
    var output = injection.css('content') == 'omega';

    injection.remove();

    return output;
  };

  /**
   * @todo
   */
  Drupal.omega.isCrappyBrowser = function () {
    return $.browser.msie && parseInt($.browser.version, 10) < 9;
  };
})(jQuery);