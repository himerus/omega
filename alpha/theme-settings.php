<?php

require_once dirname(__FILE__) . '/includes/alpha.inc';

/**
 * Implements hook_form_system_theme_settings_alter()
 */
function alpha_form_system_theme_settings_alter(&$form, &$form_state) {
  drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/theme-settings.css', array('group' => CSS_THEME, 'weight' => 100));
  
  $theme = $form_state['build_info']['args'][0];
  
  $form_state['#zones'] = alpha_zones($theme);
  $form_state['#regions'] = alpha_regions($theme);
  $form_state['#containers'] = alpha_container_options($theme, alpha_theme_get_setting('alpha_grid', $theme, 'default'));
     
  require_once DRUPAL_ROOT . '/' . drupal_get_path('theme', 'alpha') . '/includes/theme-settings-general.inc';
  require_once DRUPAL_ROOT . '/' . drupal_get_path('theme', 'alpha') . '/includes/theme-settings-sections.inc';
  require_once DRUPAL_ROOT . '/' . drupal_get_path('theme', 'alpha') . '/includes/theme-settings-regions.inc';
  
  if (module_exists('delta') && arg(2) != 'delta') {
    drupal_set_message(t('You have the !delta module installed, enabling advanced contextual theme settings. The settings provided on this page serve as the default theme settings when creating a new !delta theme settings template, yet can be overwritten by !delta based on the settings of your !delta templates and overrides.', array('!delta' => '<a href="http://drupal.org/project/delta"><strong>Delta</strong></a>')));
  }
  
  alpha_theme_settings_general($form, $form_state);
  alpha_theme_settings_sections($form, $form_state);
  alpha_theme_settings_regions($form, $form_state);
}

function alpha_theme_settings_validate_not_empty($element, &$form_state) {
  if ($element['#value'] == '_none') {
    form_set_value($element, NULL, $form_state);
  }  
}