<?php

/**
 * @file
 * Theme settings for regions.
 */

/**
 * @todo
 */
function alpha_theme_settings_regions(&$form, &$form_state) {
  $theme = $form_state['build_info']['args'][0];
  $zones = $form_state['alpha_zones'];
  $regions = $form_state['alpha_regions'];
  $containers = $form_state['alpha_containers'];
  $options = alpha_zone_options($theme);
  $columns = $spacing = !empty($containers) ? alpha_column_options(max(array_keys($containers))) : array();  
  $sections = alpha_sections();
  
  unset($columns[0]);
  array_pop($spacing);
  
  $form['alpha_settings']['regions'] = array(
    '#type' => 'fieldset',
    '#title' => t('Region configuration'),
    '#weight' => -8,
  ); 
  
  $form['alpha_settings']['regions']['unassigned'] = array(
    '#type' => 'fieldset',
    '#title' => t('Unassigned zones'),
    '#description' => t('There are no unassigned zones.'),
    '#weight' => 100,
    '#attributes' => array(
      'class' => array('alpha-unassigned'),
    ),
  );
  
  $form['alpha_settings']['regions']['unassigned']['unassigned'] = array(
    '#type' => 'fieldset',
    '#title' => t('Unassigned regions'),
    '#description' => t('There are no unassigned regions.'),
    '#weight' => 100,
    '#attributes' => array(
      'class' => array('alpha-unassigned'),
    ),
  );
  
  foreach ($sections as $key => $section) {
    $form['alpha_settings']['regions']['section_' . $key] = array(
      '#type' => 'fieldset',
      '#title' => $section . ' ' . t('Section'),
      '#description' => t('This section is empty.'),
    );
  }
  
  foreach ($zones as $zone => $item) {
    $section = $item['enabled'] ? 'section_' . $item['section'] : 'unassigned';

    unset($form['alpha_settings']['regions'][$section]['#description']);
    
    $form['alpha_settings']['regions'][$section]['zone_' . $zone] = array(
      '#type' => 'fieldset',
      '#title' => $item['name'] . ' ' . t('Zone'),
      '#description' => t('This zone is empty.'),
      '#weight' => $item['weight'],
    );
  }
  
  foreach($regions as $region => $item) {
    $zone = $item['enabled'] ? 'zone_' . $item['zone'] : 'unassigned';
    $section = $item['enabled'] && $zones[$item['zone']]['enabled'] ? 'section_' . $zones[$item['zone']]['section'] : 'unassigned';
    
    unset($form['alpha_settings']['regions'][$section][$zone]['#description']);

    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region] = array(
      '#type' => 'fieldset',
      '#title' => $item['name'],
      '#weight' => $item['weight'],
      '#attributes' => array(
        'class' => array('alpha-inline'),
        'id' => drupal_html_id('edit-region-' . $region . '-configuration'),
      ),
    );
    
    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region]['alpha_region_' . $region . '_force'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force this region to be rendered'),
      '#description' => t('Enabling this will always render this region, even if it is empty.'),
      '#default_value' => $item['force'],
    );
      
    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region]['alpha_region_' . $region . '_zone'] = array(
      '#type' => 'select',
      '#title' => t('Zone'),
      '#default_value' => !empty($item['zone']) ? $item['zone'] : array('_none'),
      '#element_validate' => array('alpha_theme_settings_validate_not_empty'),
      '#options' => array('_none' => '- None -') + $options,
    );
    
    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region]['alpha_region_' . $region . '_prefix'] = array(
      '#type' => 'select',
      '#title' => t('Prefix'),
      '#default_value' => $item['prefix'],
      '#options' => $spacing,
    );
    
    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region]['alpha_region_' . $region . '_columns'] = array(
      '#type' => 'select',
      '#title' => t('Width'),
      '#default_value' => $item['columns'],
      '#options' => $columns,
    );
    
    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region]['alpha_region_' . $region . '_suffix'] = array(
      '#type' => 'select',
      '#title' => t('Suffix'),
      '#default_value' => $item['suffix'],
      '#options' => $spacing,
    );
    
    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region]['alpha_region_' . $region . '_weight'] = array(
      '#type' => 'weight',
      '#delta' => 50,
      '#title' => t('Weight'),
      '#default_value' => $item['weight'],
    );
    
    $form['alpha_settings']['regions'][$section][$zone]['region_' . $region]['alpha_region_' . $region . '_css'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional region classes'),
      '#size' => 50,
      '#default_value' => $item['css'],
    );
  }
}

