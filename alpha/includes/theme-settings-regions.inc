<?php

function alpha_theme_settings_regions(&$form, &$form_state) {
  $theme = $form_state['build_info']['args'][0];
  $zones = $form_state['#zones'];
  $regions = $form_state['#regions'];
  $containers = $form_state['#containers'];
  $options = alpha_zone_options($theme);
  $columns = $spacing = alpha_column_options(max(array_keys($containers)));  
  
  unset($columns[0]);
  array_pop($spacing);
  
  $form['alpha_settings']['regions'] = array(
    '#type' => 'fieldset',
    '#title' => t('Region configuration'),
    '#weight' => -8,
  ); 
  
  // adding in section wrappers (header,content,footer)
  $sections = alpha_sections();
  $section_keys = array();
  // create outer fieldsets for the sections
  foreach ($sections as $key => $section) {
    $form['alpha_settings']['regions']['section_' . $key] = array(
      '#type' => 'fieldset',
      '#title' => $section . ' ' . t('Section'),
      '#description' => t('This section is empty.'),
    );
    $section_keys[$key] = $key;
  }
  $zone_sections = array();
  foreach ($zones as $key => $zone) {
    $zone_sections[$key] = $zone['section'];
    $class = $zone['enabled'] ? 'alpha-zone-enabled' : 'alpha-zone-disabled';
    
    $form['alpha_settings']['regions']['section_' . $zone['section']]['zone_' . $key] = array(
      '#type' => 'fieldset',
      '#title' => $zone['name'] . ' ' . t('Zone'),
      '#description' => t('This zone is empty.'),
      '#weight' => $zone['weight'],
      '#attributes' => array(
        'class' => array($class),
      ),
    );
  }
  
  $form['alpha_settings']['regions']['unassigned'] = array(
    '#type' => 'fieldset',
    '#title' => t('Unassigned regions'),
    '#description' => t('There are no unassigned regions.'),
    '#weight' => 100,
    '#attributes' => array(
      'class' => array('alpha-unassigned'),
    ),
  );
  
  foreach($regions as $region => $item) {
    $section = isset($zone_sections[$item['zone']]) ? 'section_' . $zone_sections[$item['zone']] : 'unassigned';
    
    $zone = isset($zones[$item['zone']]) ? 'zone_' . $item['zone'] : 'unassigned';
    unset($form['alpha_settings']['regions'][$section]['#description']);
    unset($form['alpha_settings']['regions'][$section][$zone]['#description']);

    $form['alpha_settings']['regions'][$section][$zone][$region] = array(
      '#type' => 'fieldset',
      '#title' => $item['name'],
      '#weight' => $item['weight'],
      '#attributes' => array(
        'class' => array('alpha-inline'),
      ),
    );
           
    $form['alpha_settings']['regions'][$section][$zone][$region]['alpha_region_' . $region . '_zone'] = array(
      '#type' => 'select',
      '#title' => t('Zone'),
      '#default_value' => !empty($item['zone']) ? $item['zone'] : array('_none'),
      '#element_validate' => array('alpha_theme_settings_validate_not_empty'),
      '#options' => array('_none' => '- None -') + $options,
    );
    
    $form['alpha_settings']['regions'][$section][$zone][$region]['alpha_region_' . $region . '_prefix'] = array(
      '#type' => 'select',
      '#title' => t('Prefix'),
      '#default_value' => $item['prefix'],
      '#options' => $spacing,
    );
    
    $form['alpha_settings']['regions'][$section][$zone][$region]['alpha_region_' . $region . '_columns'] = array(
      '#type' => 'select',
      '#title' => t('Width'),
      '#default_value' => $item['columns'],
      '#options' => $columns,
    );
    
    $form['alpha_settings']['regions'][$section][$zone][$region]['alpha_region_' . $region . '_suffix'] = array(
      '#type' => 'select',
      '#title' => t('Suffix'),
      '#default_value' => $item['suffix'],
      '#options' => $spacing,
    );
    
    $form['alpha_settings']['regions'][$section][$zone][$region]['alpha_region_' . $region . '_weight'] = array(
      '#type' => 'weight',
      '#delta' => 50,
      '#title' => t('Weight'),
      '#default_value' => $item['weight'],
    );
    
    $form['alpha_settings']['regions'][$section][$zone][$region]['alpha_region_' . $region . '_css'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional region classes'),
      '#size' => 50,
      '#default_value' => $item['css'],
    );
  }
}

