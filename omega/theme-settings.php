<?php

/**
 * Implements hook_form_system_theme_settings_alter()
 */
function omega_form_system_theme_settings_alter(&$form, &$form_state) {  
  $zones = $form_state['alpha_zones'];
  $regions = $form_state['alpha_regions'];
  $sections = alpha_sections();
   
  foreach($regions as $region => $item) {
    $zone = $item['enabled'] ? 'zone_' . $item['zone'] : 'unassigned';
    $section = $item['enabled'] && $zones[$item['zone']]['enabled'] ? 'section_' . $zones[$item['zone']]['section'] : 'unassigned';
    
    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region]['alpha_region_' . $region . '_equal_height'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force equal height for all child elements'),
      '#description' => t('This sets all child elements to the same height.'),
      '#default_value' => omega_region_get_setting('equal_height', $region, FALSE),
      '#weight' => -10,
    );
  }
   
  foreach ($zones as $zone => $item) {
    $section = $item['enabled'] ? 'section_' . $item['section'] : 'unassigned';
    $item['regions'] = !empty($item['regions']) ? $item['regions'] : array();
    
    $form['alpha_settings']['zones'][$section]['zone_' . $zone]['alpha_zone_' . $zone . '_equal_height'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force equal height for all child elements'),
      '#description' => t('This sets all child elements to the same height.'),
      '#default_value' => omega_zone_get_setting('equal_height', $zone, FALSE),
      '#weight' => -10,
    );
  }
}