(function ($, Modernizr, Drupal, drupalSettings, window) {

    "use strict";

    var omegaAdjustContainers = function (offset) {
        var toolbarExists = $('#toolbar-administration').length;

        if (!toolbarExists) {
            return false;
        }

        // The elements that should obtain "100%" height at minimum.
        var elements = [
            '.page--wrapper',
            '.page--wrapper .page',
            '.page--wrapper .page .omega-layout-wrapper',
            '.page--wrapper .page .omega-layout'
        ];

        var $heightElements = $(elements.join(', '));

        // Apply a calculated value the height of the elements.
        $heightElements.css('min-height', 'calc(100vh - ' + offset + 'px)');
    }

    /**
     * Function to handle adjusting the padding when the footer grows/shrinks.
     */
    Drupal.behaviors.componentFooter = {
        attach: function (context, settings) {

            // The footer region element.
            var $footer = $('.region-group--footer');
            // The layout grouping wrapper around the particular region.
            var $wrapper = $footer.closest('.omega-layout');
            //var $wrapper = $('body');

            $(window).on('resize ready load', function () {
                // Find the actual height of the footer and its contents.
                var footerHeight = $footer.outerHeight();
                // Apply the calculated value to minimum height on the parent region group element.
                $wrapper.css('padding-bottom', footerHeight);
            });
        }
    };

    /**
     * Function to handle adjusting the height of primary container elements for a footer fixed to the bottom of the screen..
     */
    Drupal.behaviors.bodyElementAdjust = {
        attach: function (context, settings) {

            // Handle adjustments to toolbar tray visibility.
            $(document).on('drupalViewportOffsetChange.toolbar', function(event, offsets){
                omegaAdjustContainers(offsets.top);
            });
        }
    };
})(jQuery, Modernizr, Drupal, drupalSettings, window);
