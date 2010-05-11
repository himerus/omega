// $Id$
function populateElement(selector, defvalue) {
    if(omega.trim(omega(selector).val()) == "") {
        omega(selector).val(defvalue);
    }
    omega(selector).focus(function() {
        if(omega(selector).val() == defvalue) {
            omega(selector).val("");
        }
    });
    omega(selector).blur(function() {
        if(omega.trim(omega(selector).val()) == "") {
            omega(selector).val(defvalue);
        }
    });
 }

omega = jQuery.noConflict();
omega(document).ready(function(){
	// give the search box some fancy stuff
	populateElement('#search-box input.form-text, #search-block-form input.form-text', 'search...');
});