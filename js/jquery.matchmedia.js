/**
 * Polyfill the behavior of window.matchMedia.
 *
 * @see http://dev.w3.org/csswg/cssom-view/#widl-Window-matchMedia-MediaQueryList-DOMString-query
 *
 * Test whether a CSS media type or media query applies. Register listeners
 * to MediaQueryList objects.
 *
 * Adapted from https://github.com/paulirish/matchMedia.js with the addition
 * of addListener and removeListener. The polyfill referenced above uses
 * polling to trigger registered listeners on matchMedia tests.
 * This polyfill triggers tests on window resize and orientationchange.
 */

window.matchMedia = window.matchMedia || (function (doc, window, Drupal) {

  "use strict";

  var docElem = doc.documentElement;
  var refNode = docElem.firstElementChild || docElem.firstChild;
  // fakeBody required for <FF4 when executed in <head>.
  var fakeBody = doc.createElement("body");
  var div = doc.createElement("div");

  div.id = "mq-test-1";
  div.style.cssText = "position:absolute;top:-100em";
  fakeBody.style.background = "none";
  fakeBody.appendChild(div);

  /**
   * A replacement for the native MediaQueryList object.
   *
   * @param {String} q
   *   A media query e.g. "screen" or "screen and (min-width: 28em)".
   */
  function MediaQueryList (q) {
    this.media = q;
    this.matches = false;
    // @todo Remove this.applies once BC for $.matchmedia gets removed.
    this.applies = false;
    this.check.call(this);
  }

  /**
   * Polyfill the addListener and removeListener methods.
   */
  MediaQueryList.prototype = {
    listeners: [],

    /**
     * Perform the media query application check.
     */
    check: function () {
      var isApplied;
      div.innerHTML = "&shy;<style media=\"" + this.media + "\"> #mq-test-1 {width: 42px;}</style>";
      docElem.insertBefore(fakeBody, refNode);
      isApplied = div.offsetWidth === 42;
      docElem.removeChild(fakeBody);
      this.matches = isApplied;
      // @todo Remove this.applies once BC for $.matchmedia gets removed.
      this.applies = isApplied;
    },

    /**
     * Polyfill the addListener method of the MediaQueryList object.
     *
     * @param {Function} callback
     *   The callback to be invoked when the media query is applicable.
     *
     * @return {Object MediaQueryList}
     *   A MediaQueryList object that indicates whether the registered media
     *   query applies. The matches property is true when the media query
     *   applies and false when not. The original media query is referenced in
     *   the media property.
     */
    addListener: function (callback) {
      var handler = (function (mql, debounced) {
        return function () {
          // Only execute the callback if the state has changed.
          var oldstate = mql.matches;
          mql.check();
          if (oldstate !== mql.matches) {
            debounced.call(mql, mql);
          }
        };
      }(this, Drupal.debounce(callback, 250)));
      this.listeners.push({
        'callback': callback,
        'handler': handler
      });

      // Associate the handler to the resize and orientationchange events.
      if ('addEventListener' in window) {
        window.addEventListener('resize', handler);
        window.addEventListener('orientationchange', handler);
      }
      else if ('attachEvent' in window) {
        window.attachEvent('onresize', handler);
        window.attachEvent('onorientationchange', handler);
      }
    },

    /**
     * Polyfill the removeListener method of the MediaQueryList object.
     *
     * @param {Function} callback
     *   The callback to be removed from the set of listeners.
     */
    removeListener: function (callback) {
      for (var i = 0, listeners = this.listeners; i < listeners.length; i++) {
        if (listeners[i].callback === callback) {
          // Disassociate the handler to the resize and orientationchange events.
          if ('removeEventListener' in window) {
            window.removeEventListener('resize', listeners[i].handler);
            window.removeEventListener('orientationchange', listeners[i].handler);
          }
          else if ('detachEvent' in window) {
            window.detachEvent('onresize', listeners[i].handler);
            window.detachEvent('onorientationchange', listeners[i].handler);
          }
          listeners.splice(i, 1);
        }
      }
    }
  };

  /**
   * Return a MediaQueryList.
   *
   * @param {String} q
   *   A media query e.g. "screen" or "screen and (min-width: 28em)". The media
   *   query is checked for applicability before the object is returned.
   */
  return function (q) {
    // Build a new MediaQueryList object with the result of the check.
    return new MediaQueryList(q);
  };
}(document, window, Drupal));

/**
 * jQuery wrapper for the matchmedia polyfill including a feature to
 * automatically assign body classes for media queries.
 */
(function ($, window, Drupal) {

"use strict";

/**
 * Toggles media-query specific body classes.
 */
Drupal.behaviors.omegaMediaQueryClasses = {
  handler: function (name, query) {
    if (query.matches) {
      $('body').removeClass(name + '-inactive').addClass(name + '-active');
    }
    else {
      $('body').removeClass(name + '-active').addClass(name + '-inactive');
    }
  },

  attach: function (context, settings) {
    var behavior = this;
    var omegaSettings = settings.omega || {};
    var mediaQueries = omegaSettings.mediaQueries || {};

    $('body', context).once('omega-mediaqueries', function () {
      $.each(mediaQueries, function (index, value) {
        var query = window.matchMedia(value);
        query.name = index;

        // Initially, check if the media query applies or not and add the
        // corresponding class to the body.
        behavior.handler(index, query);

        // React to media query changes and toggle the class names.
        query.addListener(function (query) {
          behavior.handler(index, query);
        });
      });
    });
  }
};

/**
 * Check if the given media query currently applies.
 *
 * @param query
 *   The media query to check for.
 *
 * @deprecated
 *   Use window.matchMedia() instead.
 */
$.matchmedia = function(query) {
  return window.matchMedia(query);
};

/**
 * Special event for listening to media query changes.
 *
 * @deprecated
 *   Use window.matchMedia(query).addListener(callback) instead.
 */
var event = $.event.special.mediaquery = {
  objects: {},

  add: function (handleObj) {
    event.objects[handleObj.guid] = window.matchMedia(handleObj.data);
    event.objects[handleObj.guid].addListener(handleObj.handler);
  },

  remove: function (handleObj) {
    event.objects[handleObj.guid].removeListener(handleObj.handler);
  }
};

/**
 * Event shortcut.
 *
 * @deprecated
 *   Use window.matchMedia(query).addListener(callback) instead.
 */
$.fn.mediaquery = function (query, callback) {
  return $(this).bind('mediaquery', query, callback);
};

})(jQuery, window, Drupal);
