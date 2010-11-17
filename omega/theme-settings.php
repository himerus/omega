<?php
// $Id$

/**
 * @file
 * Theme settings for the Omega theme.
 * 
 * @see admin-theme-settings.inc
 * @see default-theme-settings.inc
 * @see grid-theme-settings.inc
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
	if (!module_exists('omega_ui')) {
		drupal_set_message('<p>Without the <a href="http://himer.us/omega-ui"><strong>Omega UI</strong></a> module, you will only be able to configure grid settings for your theme via the standard form.</p><p>The <a href="http://himer.us/omega-ui"><strong>Omega UI</strong></a> module enables both contextual theme settings and an advanced UI for configuring your <a href="http://himer.us/omega-theme"><strong>Omega</strong></a> subtheme.</p>', 'warning');
	}
	
	// include general theme functions required both in template.php AND theme-settings.php
  require_once(drupal_get_path('theme', 'omega') . '/inc/theme-functions.inc');
  
  // Add the form's custom CSS
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/omega_theme_settings.css', 
    array(
      'weight' => 1000,
    )
  );
  
  // Add javascript to show/hide optional settings
  drupal_add_js(drupal_get_path('theme', 'omega') . '/js/omega_admin.js', 
    array(
      'weight' => 1000, 
      'type' => 'file', 
      'cache' => FALSE,
    )
  );
  $form['omega_grid'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => -10,
    '#default_tab' => 'defaults',
    '#prefix' => t('960gs Omega Settings'),
  );
  $form['omega_general'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => -10,
    '#default_tab' => 'defaults',
    '#prefix' => t('General Omega Settings'),
  );
  // include Omega (grid) specific theme settings
  require_once(drupal_get_path('theme', 'omega') . '/inc/grid-theme-settings.inc');
	
	// include general theme settings
  require_once(drupal_get_path('theme', 'omega') . '/inc/default-theme-settings.inc');

  // include administrative functions for theme settings
  require_once(drupal_get_path('theme', 'omega') . '/inc/admin-theme-settings.inc');
  
  $form['theme_settings']['#collapsible'] = TRUE;
  $form['theme_settings']['#collapsed'] = TRUE;
  $form['logo']['#collapsible'] = TRUE;
  $form['logo']['#collapsed'] = TRUE;
  $form['favicon']['#collapsible'] = TRUE;
  $form['favicon']['#collapsed'] = TRUE;
  //krumo($form);
}