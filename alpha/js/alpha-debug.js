(function($) {
  $(function() {
    $('.alpha-grid-toggle').click(function() {
      if (Drupal.settings.alpha.overlayActive = !Drupal.settings.alpha.overlayActive) {
        $('.alpha-grid-toggle').removeClass('alpha-grid-toggle-inactive').addClass('alpha-grid-toggle-active');
        $('body').addClass('alpha-grid-debug');
      }
      else {
        $('.alpha-grid-toggle').removeClass('alpha-grid-toggle-active').addClass('alpha-grid-toggle-inactive');
        $('body').removeClass('alpha-grid-debug');
      }
      
      return false;
    });
    
    $('.alpha-block-toggle').click(function() {
      if (Drupal.settings.alpha.blockActive = !Drupal.settings.alpha.blockActive) {
        $('.alpha-block-toggle').removeClass('alpha-block-toggle-inactive').addClass('alpha-block-toggle-active');
        $('body').addClass('alpha-region-debug');        
        $(window).trigger('resize.equalHeights');
      }
      else {
        $('.alpha-block-toggle').removeClass('alpha-block-toggle-active').addClass('alpha-block-toggle-inactive');
        $('body').removeClass('alpha-region-debug');
        $(window).trigger('resize.equalHeights');
      }
      
      return false;
    });
  });
})(jQuery);