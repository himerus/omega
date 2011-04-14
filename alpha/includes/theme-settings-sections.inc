<?php

/*
 * @todo
 */
function alpha_theme_settings_sections(&$form, &$form_state) {  
  $sections = alpha_sections();
  $zones = $form_state['#zones'];

  $form['alpha_settings']['sections'] = array(
    '#weight' => -9,
    '#type' => 'fieldset',
    '#title' => t('Section configuration'),
  );
  
  $form['alpha_settings']['sections']['unassigned'] = array(
    '#type' => 'fieldset',
    '#title' => t('Unassigned zones'),
    '#description' => t('There are no unassigned zones.'),
    '#weight' => 100,
    '#attributes' => array(
      'class' => array('alpha-unassigned'),
    ),
  );
  
  foreach ($sections as $key => $section) {
    $form['alpha_settings']['sections']['section_' . $key] = array(
      '#type' => 'fieldset',
      '#title' => $section,
      '#description' => t('This section is empty.'),
    );
  }
    
  foreach ($zones as $zone => $item) {
    $section = isset($sections[$item['section']]) ? 'section_' . $item['section'] : 'unassigned';
    
    unset($form['alpha_settings']['sections'][$section]['#description']);
    
    $form['alpha_settings']['sections'][$section]['zone_' . $zone] = array(
      '#type' => 'fieldset',
      '#title' => $item['name'],
      '#weight' => $item['weight'],
      '#attributes' => array(
        'class' => array('alpha-inline'),
      ),
    );
    
    $form['alpha_settings']['sections'][$section]['zone_' . $zone]['alpha_zone_' . $zone . '_section'] = array(
      '#type' => 'select',
      '#title' => t('Section'),
      '#default_value' => $item['section'],
      '#element_validate' => array('alpha_theme_settings_validate_not_empty'),
      '#options' => array('_none' => '- None -') + $sections,
    );
    
    $form['alpha_settings']['sections'][$section]['zone_' . $zone]['alpha_zone_' . $zone . '_weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#default_value' => $item['weight'],
    );
  }
}