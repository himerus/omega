/**
 * @todo
 */

(function($) {
  $(window).load(function() {
    $(this).bind('resize.equalHeights', function() {
      $($('.equal-height-container').get().reverse()).each(function() {
        $(this).children('.equal-height-element').height('auto').equalHeights();
      });
    }).trigger('resize.equalHeights');
  });
})(jQuery);