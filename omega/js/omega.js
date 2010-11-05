// $Id$
(function ($) {
  function populateElement(selector, defvalue) {
      if ($.trim($(selector).val()) == "") {
          $(selector).val(defvalue);
      }
      $(selector).focus(function() {
          if($(selector).val() == defvalue) {
              $(selector).val("");
          }
      });
      $(selector).blur(function() {
          if($.trim($(selector).val()) == "") {
              $(selector).val(defvalue);
          }
      });
   }
  
  $(document).ready(function(){
    // give the search box some fancy stuff
    populateElement('#search-box input.form-text, #search-block-form input.form-text', Drupal.t(Drupal.settings.default_search_text));
    populateElement('#search-region input.form-text', Drupal.t(Drupal.settings.default_search_text));
    
    var searchZoneWidth = $('#region-location_search').innerWidth();
    if(searchZoneWidth) {
      //console.log(searchZoneWidth);
    	$('#region-location_search .form-text').width(searchZoneWidth - 10);
    }
  });
})(jQuery);
