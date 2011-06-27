/**
 * @todo
 */

(function($) { 
  /**
   * @todo
   */
  Drupal.behaviors.omegaEqualHeights = {
    attach: function (context) {
      $('body', context).once('omega-equalheights', function () {
        $(window).bind('resize.omegaequalheights', function () {
          $($('.equal-height-container').get().reverse()).each(function () {
            var children = $(this).children('.equal-height-element').height('auto');
            
            if (!Drupal.behaviors.hasOwnProperty('omegaMediaQueries') || Drupal.omega.getCurrentLayout() != 'mobile') {
              children.equalHeights();
            }
          });
        }).load(function () {
          $(this).trigger('resize.omegaequalheights');
        });
      });
    }
  };
})(jQuery);