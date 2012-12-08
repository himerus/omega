(function ($) {

/**
 * Array for gathering media queries from (sub)themes and modules.
 */
Drupal.mediaQueries = Drupal.mediaQueries | new Array;

/**
 * Toggles media-query specific body classes.
 */
Drupal.behaviors.mediaQueryClasses = {
  attach: function (context, settings) {
    $('body').once('mediaqueries', function () {
      $.each(Drupal.mediaQueries, function (index, value) {
        // Initially, check if the media query applies or not and add the
        // corresponding class to the body.
        if ($.matchmedia(value)) {
          $('body').addClass(index + '-active');
        }
        else {
          $('body').addClass(index + '-inactive');
        }

        // React to media query changes and toggle the class names.
        $(window).mediaquery(value, function (e) {
          if (e.applies) {
            $('body').removeClass(index + '-inactive').addClass(index + '-active');
          }
          else {
            $('body').removeClass(index + '-active').addClass(index + '-inactive');
          }
        });
      });
    });
  }
}

/**
 * Array for caching the media queries.
 */
var mediaQueryCache = new Array();

/**
 * Check if a media query currently applies.
 *
 * @param query
 *   The media query to check for.
 */
$.matchmedia = function(query) {
  // Check if the media query is already in the list.
  var index = $.inArray(query, mediaQueryCache);
  var $dummy;

  if (index == -1) {
    // The media query is not yet in the list.
    index = mediaQueryCache.length;

    // Add the media query to the list. Its index is going to be the previous
    // length of the array.
    mediaQueryCache.push(query);

    // Create the dummy for checking for the media query.
    $dummy = $('<div id="matchmedia-' + index + '" />').css({position: 'absolute', top: '-999em'}).prependTo('body');
    $dummy.html('<style media="' + query + '"> #matchmedia-' + index + ' { width: 42px; } </style>');
  }
  else {
    // The media query is already in the list. We just have to find it.
    $dummy = $('#matchmedia-' + index);
  }

  // If the media query applies the width of the dummy is 42.
  return $dummy.outerWidth() == 42;
};

/**
 * Throttled resize event. Fires only once after the resize ended.
 */
var $event = $.event.special.mediaquery = {
  add: function (handleObj) {
    $(this).bind('resize.matchmedia.' + handleObj.guid, {query: handleObj.data, applies: $.matchmedia(handleObj.data)}, $event.handler);
  },

  remove: function (handleObj) {
    $(this).unbind('resize.matchmedia.' + handleObj.guid, $event.handler);
  },

  handler: function (e) {
    var context = this;
    var args = arguments;

    if (e.data.applies != $.matchmedia(e.data.query)) {
      e.type = 'mediaquery';
      e.applies = e.data.applies = !e.data.applies;
      $.event.handle.apply(context, args);
    }
  }
};

/**
 * Event shortcut.
 */
$.fn.mediaquery = function (query, callback) {
  return $(this).bind('mediaquery', query, callback);
}

})(jQuery);
