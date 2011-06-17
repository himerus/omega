/**
 * @todo
 */

(function($) {
  $(function() {
    $('.alpha-grid-toggle').click(function() {
      $('body').toggleClass('alpha-grid-debug');

      return false;
    });
    
    $('.alpha-block-toggle').click(function() {
      $('body').toggleClass('alpha-region-debug').hasClass('alpha-region-debug');      
      $(window).trigger('resize.equalHeights');
      
      return false;
    });
  });
})(jQuery);