(function ($, Modernizr, Drupal, drupalSettings, window) {

    "use strict";

    /**
     * Function to handle adjusting the padding when the footer grows/shrinks.
     */
    Drupal.behaviors.componentFooter = {
        attach: function (context, settings) {

            // The footer region element.
            var $footer = $('.region-group--footer');
            // The layout grouping wrapper around the particular region.
            var $wrapper = $footer.closest('.omega-layout');

            $(window).on('resize ready load', function () {
                // Find the actual height of the footer and its contents.
                var footerHeight = $footer.outerHeight();
                // Apply the calculated value to minimum height on the parent region group element.
                $wrapper.css('padding-bottom', footerHeight);
            });
        }
    };

})(jQuery, Modernizr, Drupal, drupalSettings, window);
