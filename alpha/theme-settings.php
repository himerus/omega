<?php

require_once dirname(__FILE__) . '/includes/alpha.inc';

/**
 * Implements hook_form_system_theme_settings_alter()
 */
function alpha_form_system_theme_settings_alter(&$form, &$form_state) {
  drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/theme-settings.css');
    
  require_once DRUPAL_ROOT . '/' . drupal_get_path('theme', 'alpha') . '/includes/theme-settings-zones.inc';
  require_once DRUPAL_ROOT . '/' . drupal_get_path('theme', 'alpha') . '/includes/theme-settings-general.inc';
  
  if (module_exists('delta') && arg(2) != 'delta') {
    drupal_set_message(t('You have the %delta module installed, enabling advanced contextual theme settings. The settings provided on this page serve as the default theme settings when creating a new %delta theme settings template, yet can be overwritten by %delta based on the settings of your %delta templates and overrides.', array('%delta' => '<a href="http://drupal.org/project/delta"><strong>Delta</strong></a>')));
  }
  
  alpha_theme_settings_zones($form, $form_state);   
  alpha_theme_settings_general($form, $form_state);
}