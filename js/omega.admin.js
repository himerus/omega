(function ($) {

/**
 * Allows administrators to click on the icon of a layout instead of having to
 * target the radio button in order to select it.
 */
Drupal.behaviors.omegaThemeSettingsLayouts = {
  attach: function (context, settings) {
    $('.form-item-omega-layout .omega-layout-icon').click(function () {
      $(this).siblings('.form-item').find('input').click().change();
    });
  }
}

})(jQuery);
