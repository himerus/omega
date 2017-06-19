(function ($, Modernizr, Drupal, drupalSettings, window) {

  "use strict";

  /**
   * Function to handle toggling the user login when placed in the user control bar.
   */
  Drupal.behaviors.userBarUserLogin = {
    attach: function (context, settings) {
      $('.region-group--user .login-box--trigger').click(function(){
        let loginBlock = $(this).parent('.block-user-login-block');

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

      // Handle closing the login block if it's open and we click outside the flyout.
      $(document).mouseup(function(e) {
        let container = $('.region-group--user .block-user-login-block');
        if (!container.is(e.target) && container.has(e.target).length === 0 && container.hasClass('triggered')) {
          container.removeClass('triggered');
          container.children('.login-box--flyout').slideUp('fast');
        }
      });

      // Handle closing the login block if it's open, and someone hits the ESC key.
      $(document).keyup(function(e) {
        if (e.keyCode === 27) { // escape key maps to keycode `27`
          $('.region-group--user .block-user-login-block').removeClass('triggered');
          $('.region-group--user .login-box--flyout').slideUp('fast');
        }
      });
    }
  };

})(jQuery, Modernizr, Drupal, drupalSettings, window);
