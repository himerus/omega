(function($) {
  $(function() {
    var button = $('.alpha-grid-toggle');
    
    $(button).click(function() {
      Drupal.settings.alpha.overlayActive = !Drupal.settings.alpha.overlayActive;
      
      if (Drupal.settings.alpha.overlayActive) {
        $(button).removeClass('alpha-grid-toggle-inactive').addClass('alpha-grid-toggle-active');
        $('body').addClass('alpha-grid-debug');
      }
      else {
        $(button).removeClass('alpha-grid-toggle-active').addClass('alpha-grid-toggle-inactive');
        $('body').removeClass('alpha-grid-debug');
      }
      
      return false;
    });
  });
})(jQuery);