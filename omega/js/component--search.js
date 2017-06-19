(function ($, Modernizr, Drupal, drupalSettings, window) {

  "use strict";

  /**
   * Function to handle submitting the search form via the search icon button rather than the default submit button.
   */
  Drupal.behaviors.searchAdjust = {
    attach: function (context, settings) {
      $('.icon--search').on('click', function() {
        // Transfer the click to the actual submit button.
        $(this).parent('.form-actions').children('.form-submit').click();
      });
    }
  };

})(jQuery, Modernizr, Drupal, drupalSettings, window);
