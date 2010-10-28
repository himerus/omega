<?php
// $Id$

/**
 * @file
 * Theme settings for the Omega theme.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 * @param $form_state
 *   A keyed array containing the current state of the form.
 */
function omega_form_system_theme_settings_alter(&$form, &$form_state) {
	// provide a warning if the Delta module is not installed
	if (!module_exists('delta')) {
		drupal_set_message('<p>Without the <a href="http://himer.us/delta-module"><strong>Delta</strong></a> module, you will only be able to configure grid settings for your theme via the standard form.</p><p>The <a href="http://himer.us/delta-module"><strong>Delta</strong></a> module enables both contextual theme settings and an advanced UI for configuring your <a href="http://himer.us/omega-theme"><strong>Omega</strong></a> subtheme.</p>', 'warning');
	}
	// include general theme functions required both in template.php AND theme-settings.php
  require_once(drupal_get_path('theme', 'omega') . '/inc/theme-functions.inc');
  
  // Add the form's custom CSS
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/omega_theme_settings.css', 
    array(
      'weight' => 1000,
    )
  );
  
 
  
  //drupal_add_library('system', 'ui.dialog');
  //drupal_add_library('system', 'ui.draggable');
  //drupal_add_library('system', 'ui.droppable');
  
  
  // Add javascript to show/hide optional settings
  drupal_add_js(drupal_get_path('theme', 'omega') . '/js/omega_admin.js', 
    array(
      'weight' => 1000, 
      'type' => 'file', 
      'cache' => FALSE,
    )
  );

  
  // include general theme settings
  require_once(drupal_get_path('theme', 'omega') . '/inc/default-theme-settings.inc');
  
  // include Omega (grid) specific theme settings
  require_once(drupal_get_path('theme', 'omega') . '/inc/grid-theme-settings.inc');
  
  // include administrative functions for theme settings
  require_once(drupal_get_path('theme', 'omega') . '/inc/admin-theme-settings.inc');
  //krumo($form);
}