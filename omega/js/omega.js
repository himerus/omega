// $Id: omega.js,v 1.1.2.7 2010/12/13 23:45:39 himerus Exp $
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
    // give the login form some love
    $('#user-login-form .login-submit-link').click(function(){
    	$('#user-login-form').submit();
    	return false;
    });
    /*
    // create some settings for the background images
    Drupal.settings.omega_usernmae_bg = $('#user-login-form #edit-name').css('background-image');
	Drupal.settings.omega_usernmae_bg_full = $('#user-login-form #edit-name').css('background');
	Drupal.settings.omega_pass_bg = $('#user-login-form #edit-pass').css('background-image');
	Drupal.settings.omega_pass_bg_full = $('#user-login-form #edit-pass').css('background');
    // username field
	$('#user-login-form #edit-name').focus(function(){
    	$(this).css('background-image', 'none');
    }).blur(function(){
    	if (!$('#user-login-form #edit-name').val()) {
    		$('#user-login-form #edit-name').css('background', Drupal.settings.omega_usernmae_bg_full);
    	}
    });
    // password field
    $('#user-login-form #edit-pass').focus(function(){
    	$(this).css('background-image', 'none');
    }).blur(function(){
    	if (!$('#user-login-form #edit-pass').val()) {
    		$('#user-login-form #edit-pass').css('background', Drupal.settings.omega_pass_bg_full);
    	}
    });
    // check fields on page load
    if($('#user-login-form #edit-name').val()) {
    	$('#user-login-form #edit-name').css('background-image', 'none');
    }
    if($('#user-login-form #edit-pass').val()) {
    	$('#user-login-form #edit-pass').css('background-image', 'none');
    }
    */
    // fix the switchtheme form
    $('#switchtheme-switch-form #edit-theme').change(function(){
    	$('#switchtheme-switch-form').submit();
    });
    // fix menus that don't respect active trail because drupal links are stoopid
    $('#region-menu ul li.active').parents('li').addClass('active');
  });
})(jQuery);
