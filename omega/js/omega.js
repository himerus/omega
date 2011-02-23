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
  Drupal.behaviors.manipulateFormElements = {
    attach: function(context, settings) {
      // give the search box some fancy stuff
    populateElement('#search-box input.form-text, #search-block-form input.form-text', Drupal.t(Drupal.settings.default_search_text));
    populateElement('#search-region input.form-text', Drupal.t(Drupal.settings.default_search_text));
    // give the login form some love
    $('#user-login-form .login-submit-link').click(function(){
    	$('#user-login-form').submit();
    	return false;
    });
    }
  };
  Drupal.behaviors.correctActiveTrails = {
    attach: function(context, settings) {
      // fix menus that don't respect active trail because drupal links are stoopid
    $('#region-menu ul li.active').parents('li').addClass('active-trail');
    }
  };
})(jQuery);
