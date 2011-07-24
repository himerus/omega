/**
 * @todo
 */

(function($) {
  /**
   * @todo
   */
  var omegaEqualHeights = function (elements) {  
    elements.unbind('resize.omegaequalheights');
    
    $(elements).css('height', '').css('min-height', '');
    
    if (!Drupal.behaviors.hasOwnProperty('omegaMediaQueries') || Drupal.omega.getCurrentLayout() != 'mobile') {
      var tallest = 0;
      
      elements.each(function () {    
        if ($(this).height() > tallest) {
          tallest = $(this).height();
        }
      }).each(function() {
        if ($(this).height() < tallest) {
          $(this).css('height', tallest).css('min-height', tallest);
        }
      });
    }
    
    elements.bind('resize.omegaequalheights', function () {
      omegaEqualHeights(elements);
    });
  }
  
  /**
   * @todo
   */
  Drupal.behaviors.omegaEqualHeights = {
    attach: function (context) {
      $('body', context).once('omega-equalheights', function () {
        $($('.equal-height-container').get().reverse()).each(function () {
          omegaEqualHeights($(this).children('.equal-height-element'));
        });
      });
    }
  };
})(jQuery);