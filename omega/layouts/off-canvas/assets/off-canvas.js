(function ($, Drupal) {
  /**
   * Toggle show/hide links for off canvas layout.
   */
  Drupal.behaviors.omegaOffCanvasLayout = {
    attach: function (context) {
      $('.l-off-canvas-show').click(function() {
        $(this).parent().addClass('is-visible');
        return false;
      });
      $('.l-off-canvas-hide').click(function() {
        $(this).parent().removeClass('is-visible');
        return false;
      });
    }
  };

})(jQuery, Drupal);
