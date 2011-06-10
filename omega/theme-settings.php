<?php

/**
 * Implements hook_form_system_theme_settings_alter()
 */
function omega_form_system_theme_settings_alter(&$form, &$form_state) {
  $theme = $form_state['build_info']['args'][0];
  $zones = $form_state['alpha_zones'];
  $regions = $form_state['alpha_regions'];
  $sections = alpha_sections();
  
  foreach($regions as $region => $item) {
    $zone = $item['enabled'] ? $item['zone'] : '__unassigned__';
    $section = $item['enabled'] && $zones[$item['zone']]['enabled'] ? $zones[$item['zone']]['section'] : '__unassigned__';
    
    $form['alpha_settings']['structure'][$section][$zone]['regions'][$region]['alpha_region_' . $region . '_equal_height'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force equal height for all child elements'),
      '#description' => t('Force equal height for all child elements.'),
      '#default_value' => $item['equal_height'],
      '#weight' => -10,
      '#states' => array(
        'visible' => array(
          ':input[name="alpha_libraries[omega_equalheights]"]' => array('checked' => TRUE),
        ),
      ),
    );
  }
   
  foreach ($zones as $zone => $item) {
    $section = $item['enabled'] ? $item['section'] : 'unassigned';
    
    $form['alpha_settings']['structure'][$section][$zone]['zone']['alpha_zone_' . $zone . '_equal_height'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force equal height for all child elements.'),
      '#description' => t('Force equal height for all child elements.'),
      '#default_value' => $item['equal_height'],
      '#weight' => -10,
      '#states' => array(
        'visible' => array(
          ':input[name="alpha_libraries[omega_equalheights]"]' => array('checked' => TRUE),
        ),
      ),
    );
  }
}