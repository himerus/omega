(function ($, Modernizr, Drupal, drupalSettings, window) {

    "use strict";

    /**
     * Reusable function to assign the appropriate min-height around various omega-centric page wrappers to ensure
     * the footer can be attached to the bottom of the screen on pages where the total height of content is less than
     * that of the viewport. 100vh++
     *
     * @param offset
     * @returns {boolean}
     */
    var omegaAdjustContainers = function (offset) {
        // The elements that should obtain "100%" height at minimum.
        var elements = [
            '.page--wrapper',
            '.page--wrapper .page',
            '.page--wrapper .page .omega-layout-wrapper',
            '.page--wrapper .page .omega-layout'
        ];
        // Combine the elements into a usable jQuery object.
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
            // Handle adjusting the appropriate padding for the absolutely positioned component footer.
            $(window).on('resize ready load', function () {
                // Find the actual height of the footer and its contents.
                var footerHeight = $footer.outerHeight();
                // Apply the calculated value to minimum height on the parent region group element.
                $wrapper.css('padding-bottom', footerHeight);
            });
        }
    };

    /**
     * Behavior to adjust the height of primary container elements for a footer fixed to the bottom of the screen..
     *
     * This behavior is only needed/required/used for logged in users with the Use the administration toolbar
     * permission assigned. The anonymous user who does not see the toolbar has the component footer positioned
     * entirely by the CSS defined in style/scss/components/component--footer.scss.
     *
     * Essentially, this has a zero performance impact on any standard site users and is implmented to ensure a
     * clean, working version that adapts to the toolbar in its many sizes and positions.
     *
     * @see style/scss/components/component--footer.scss
     */
    Drupal.behaviors.bodyElementAdjust = {
        attach: function (context, settings) {
            // Handle adjustments to toolbar tray visibility.
            $(document).on('drupalViewportOffsetChange.toolbar', function (event, offsets) {
                omegaAdjustContainers(offsets.top);
            });
        }
    };
})(jQuery, Modernizr, Drupal, drupalSettings, window);
