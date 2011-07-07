<?php

require_once dirname(__FILE__) . '/includes/alpha.inc';

/**
 * Implements hook_form_system_theme_settings_alter()
 */
function alpha_form_system_theme_settings_alter(&$form, &$form_state) {  
  require_once DRUPAL_ROOT . '/' . drupal_get_path('theme', 'alpha') . '/includes/theme-settings-general.inc';
  require_once DRUPAL_ROOT . '/' . drupal_get_path('theme', 'alpha') . '/includes/theme-settings-structure.inc';
  
  drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/alpha-theme-settings.css', array('group' => CSS_THEME, 'weight' => 100));
  
  $theme = $form_state['build_info']['args'][0];
  
  alpha_register_grids($theme);
  alpha_register_css($theme);
  alpha_register_libraries($theme);
  
  $form_state['alpha_settings'] = alpha_settings($theme);
  $form_state['alpha_zones'] = alpha_zones(NULL, $theme);
  $form_state['alpha_regions'] = alpha_regions(NULL, $theme);
  $form_state['alpha_containers'] = alpha_container_options($form_state['alpha_settings']['grid'], $theme);

  $form['alpha_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => -10,
    '#prefix' => t('<h3>Layout configuration</h3>'),
  );
  
  alpha_theme_settings_general($form, $form_state);
  alpha_theme_settings_structure($form, $form_state);
  
  $form['#validate'][] = 'alpha_theme_settings_form_validate';
}

/**
 * Form element validation handler for replacing the value "_none" with NULL. 
 */
function alpha_theme_settings_validate_not_empty(&$element, &$form_state) {
  if ($element['#value'] == '_none') {
    form_set_value($element, NULL, $form_state);
  }  
}

/**
 * Form element validation handler for validating the primary region setting for zones.
 */
function alpha_theme_settings_validate_primary(&$element, &$form_state) {
  if ($element['#value'] != '_none') {
    $values = $form_state['values'];
    
    if ($values['alpha_region_' . $element['#value'] . '_zone'] != $element['#zone']) {
      form_set_value($element, NULL, $form_state);
    }
    else {
      $theme = $form_state['build_info']['args'][0];
      $regions = alpha_regions(NULL, $theme);
      $zone = alpha_zones($element['#zone'], $theme);
      $element['#sum'] = 0;
      
      foreach ($regions as $region => $item) {
        if ($values['alpha_region_' . $region . '_zone'] == $element['#zone']) {
          $element['#sum'] += $values['alpha_region_' . $region . '_columns'];
          $element['#sum'] += $values['alpha_region_' . $region . '_prefix'];
          $element['#sum'] += $values['alpha_region_' . $region . '_suffix'];
        }
      }
      
      if ($element['#sum'] > $values['alpha_zone_' . $element['#zone'] . '_columns']) {
        form_error($element, t('You have specified the %region region as the primary region for the %zone zone but the summed region width is greater than the number of available columns for that zone.', array('%region' => $regions[$element['#value']]['name'], '%zone' => $zone['name'])));
      }
    }
  }
}

/**
 * Form element validation handler for validating the region order manipulation setting for zones.
 */
function alpha_theme_settings_validate_order(&$element, &$form_state) {
  if ($element['#value']) {
    $values = $form_state['values'];
    $theme = $form_state['build_info']['args'][0];
    $regions = alpha_regions(NULL, $theme);
    $zone = alpha_zones($element['#zone'], $theme);
    $element['#sum'] = 0;

    foreach ($regions as $region => $item) {
      if ($values['alpha_region_' . $region . '_zone'] == $element['#zone']) {
        $element['#sum'] += $values['alpha_region_' . $region . '_columns'];
        $element['#sum'] += $values['alpha_region_' . $region . '_prefix'];
        $element['#sum'] += $values['alpha_region_' . $region . '_suffix'];
      }
    }

    if ($element['#sum'] > $values['alpha_zone_' . $element['#zone'] . '_columns']) {
      form_error($element, t('You have chosen to manipulate the region positioning of the %zone zone but the summed region width is greater than the number of available columns for that zone.', array('%zone' => $zone['name'])));
    }
  }
}

/**
 * @todo
 */
function alpha_theme_settings_form_validate($form, &$form_state) {
  unset($form_state['values']['alpha_settings__active_tab']);
}