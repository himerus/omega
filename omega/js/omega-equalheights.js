/**
 * @todo
 */

(function($) {  
  /**
   * @todo
   */
  var omegaBindHeights = function (elements) {  
    elements.unbind('resize.omegaequalheights');
    
    omegaEqualHeights(elements);
    
    elements.bind('resize.omegaequalheights', function () {
      omegaBindHeights(elements);
    });
  }
  
  /**
   * @todo
   */
  var omegaEqualHeights = function (elements) {
    $(elements).css('min-height', 'inherit');
    $(elements).css('height', 'auto');
    
    if (!Drupal.behaviors.hasOwnProperty('omegaMediaQueries') || Drupal.omega.getCurrentLayout() != 'mobile') {
      var tallest = 0;

      elements.each(function () {    
        if ($(this).height() > tallest) {
          tallest = $(this).height();
        }
      });

      elements.each(function() {
        if ($(this).height() < tallest) {
          $(this).css('min-height', tallest);
          $(this).css('height', tallest);
        }
      });
    }
  }
  
  /**
   * @todo
   */
  Drupal.behaviors.omegaEqualHeights = {
    attach: function (context) {
      $('body', context).once('omega-equalheights', function () {
        $(window).load(function () {
          $($('.equal-height-container').get().reverse()).each(function () {
            omegaBindHeights($(this).children('.equal-height-element'));
          });
        });
      });
    }
  };
})(jQuery);