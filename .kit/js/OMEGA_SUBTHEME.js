
(function ($, Drupal, drupalSettings) {

    "use strict";

    /**
     * Container for easy storage and retrieval of variables in the DOM
     *
     * Configurable Javascript is available with drupalSettings (the successor to Drupal 7's Drupal.settings).
     * However, to make drupalSettings available to our JavaScript file: we have to declare a dependency on it.
     *
     * @requires drupalSettings as dependency OMEGA_SUBTHEME.libraries.yml
     * @see https://www.drupal.org/node/2274843#configurable
     * @see OMEGA_SUBTHEME/js/README.md
     * @type {{object}}
     */
    drupalSettings.OMEGA_SUBTHEME = {
        'config' : {
            'sample_variable': true
        }
    };

    /**
     * Sample of Drupal.behaviors
     *
     * @see https://www.drupal.org/node/2269515
     * @see OMEGA_SUBTHEME/js/README.md
     * @type {{attach: Drupal.behaviors.myCustomSubthemeBehavior.attach}}
     */
    Drupal.behaviors.myCustomSubthemeBehavior = {
        attach: function (context, settings) {
            $(context).find('input.css-class').once('myCustomSubthemeBehavior').each(function () {
                // Apply the myCustomSubthemeBehavior effect to the elements only once.
            });
        }
    };

})(jQuery, Drupal, drupalSettings);
