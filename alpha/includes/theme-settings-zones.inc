<?php

/*
 * @todo
 */
function alpha_theme_settings_zones(&$form, &$form_state) {
  $zones = $form_state['#zones'];
  $regions = $form_state['#regions'];
  $containers = $form_state['#containers'];  
  
  foreach ($regions as $region => $info) {
    if (isset($zones[$info['zone']])) {
      $zones[$info['zone']]['regions'][$region] = $info['name'];
    }
  }

  $form['alpha_zones'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => -9,
    '#prefix' => t('<h3>Zone configuration</h3>'),
  );
  
  // Looping over each zone, and region in that zone.
  foreach ($zones as $zone => $item) {
    $item['regions'] = !empty($item['regions']) ? $item['regions'] : array();
    
    // Creating an additional fieldset for each zone 
    $form['alpha_zones'][$zone] = array(
      '#type' => 'fieldset',
      '#title' => $item['name'],
    );

    // Provide a quick enable/disable option for this zone
    $form['alpha_zones'][$zone]['alpha_zone_' . $zone . '_enabled'] = array(
  	  '#type' => 'checkbox',
      '#title' => t('Enable this zone'),
      '#description' => t('Disabling this zone will instantly hide the container and all regions within from being rendered on page.'),
      '#default_value' => $item['enabled'],
      '#weight' => -50,
    );
    
    $form['alpha_zones'][$zone]['settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('@name zone configuration', array('@name' => $item['name'])),
      '#states' => array(
        'visible' => array(
          ':input[name="alpha_zone_' . $zone . '_enabled"]' => array('checked' => TRUE),
        ),
      ),
    );
    
    // Provide a full width wrapper around the zone (allowing for design elements outside the grid)
    $form['alpha_zones'][$zone]['settings']['alpha_zone_' . $zone . '_wrapper'] = array(
      '#type' => 'checkbox',
      '#title' => t('Provide full width wrapper around this zone'),
      '#description' => t('Enabling this feature will give a &lt;div&gt; wrapper around the zone itself, allowing you to theme in elements that appear outside the 960 pixel container zone.'),
      '#default_value' => $item['wrapper'],
    );
       
    // Create a container width selection menu for EACH zone
    $form['alpha_zones'][$zone]['settings']['alpha_zone_' . $zone . '_columns'] = array(
      '#type' => 'select',
      '#title' => t('Column count'),
      '#default_value' => $item['columns'],
      '#options' => $containers,
    );
    
    // Decide which region is the primary item.
    // The primary region is the one that will absorb the size of empty regions that are related in the same zone.
    $form['alpha_zones'][$zone]['settings']['alpha_zone_' . $zone . '_primary'] = array(
      '#type' => 'select',
      '#title' => t('Primary Region'),
      '#default_value' => $item['primary'],
      '#options' => array('_none' => t('- None -')) + $item['regions'],
      '#element_validate' => array('alpha_theme_settings_validate_not_empty'),
      '#description' => t('The primary region is the one that will gain width when associated regions are empty. Selecting a primary region will cause this zone to be treated as a <em>dynamic</em> zone.'),
    );
    
    // Allow for zone classes
    $form['alpha_zones'][$zone]['settings']['alpha_zone_' . $zone . '_css'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional zone classes'),
      '#size' => 60,
      '#default_value' => $item['css'],
      '#description' => t('You can quickly add custom CSS classes to your zone here.'),
    );
  }
}

/*
 * @todo
 */
function alpha_theme_settings_primary_region_validate($element, &$form_state) {
  if ($element['#value'] == '_none') {
    form_set_value($element, NULL, $form_state);
  }  
}