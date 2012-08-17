(function($) {

/**
 * Container for the resi
 */
var resizeTimeout;

/**
 * Throttled resize event. Fires only once after the resize ended.
 */
var $event = $.event.special.resizeend = {
  setup: function () {
    $(this).bind('resize', $event.handler);
  },

  teardown: function () {
    $(this).unbind('resize', $event.handler);
  },

  handler: function (e) {
    var context = this;
    var args = arguments;

    if (resizeTimeout) {
      clearTimeout(resizeTimeout);
    }

    resizeTimeout = setTimeout(function () {
      // Set correct event type
      e.type = 'resizeend';
      $.event.handle.apply(context, args);
    }, 150);
  }
};

/**
 * Wrapper for the resizeend event.
 */
$.fn.resizeend = function (handler){
  return this.bind('resizeend', handler);
};

})(jQuery);
