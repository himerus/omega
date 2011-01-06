// $Id$
(function ($) {
  function populateElement(selector, defvalue) {
      if (omega.trim($(selector).val()) == "") {
          $(selector).val(defvalue);
      }
      $(selector).focus(function() {
          if($(selector).val() == defvalue) {
              $(selector).val("");
          }
      });
      $(selector).blur(function() {
          if(omega.trim($(selector).val()) == "") {
              $(selector).val(defvalue);
          }
      });
   }
  
  omega = jQuery.noConflict();
  $(document).ready(function(){
    // give the search box some fancy stuff
    populateElement('#search-box input.form-text, #search-block-form input.form-text', Drupal.t(Drupal.settings.default_search_text));
    populateElement('#search-region input.form-text', Drupal.t(Drupal.settings.default_search_text));
  });
})(jQuery);
