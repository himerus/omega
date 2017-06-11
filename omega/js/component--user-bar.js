(function ($, Modernizr, Drupal, drupalSettings, window) {

  "use strict";

  /**
   * Function to handle toggling the user login when placed in the user control bar.
   */
  Drupal.behaviors.userBarUserLogin = {
    attach: function (context, settings) {
      $('.region-group--user .login-box--trigger').click(function(){
        var loginBlock = $(this).parent('.block-user-login-block');


        if (loginBlock.hasClass('triggered')) {
          // Already opened, perform close operation(s).
          loginBlock.removeClass('triggered');
          loginBlock.children('.login-box--flyout').slideUp('fast');
        }
        else {
          // Closed, perform open operation(s).
          loginBlock.addClass('triggered');
          loginBlock.children('.login-box--flyout').slideDown('fast');
        }

      });

    }
  };

})(jQuery, Modernizr, Drupal, drupalSettings, window);
