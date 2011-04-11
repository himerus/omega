<?php

/*
 * @todo
 */
function alpha_theme_settings_zones(&$form, &$form_state) {  
  $themes = list_themes();
  $theme = $form_state['build_info']['args'][0];
  $zones = alpha_zones($theme);
  $containers = alpha_container_options($theme, alpha_theme_get_setting('alpha_grid', 'default'));
  
  $form['alpha_zones'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => -9,
    '#prefix' => t('<h3>Zone configuration</h3>'),
  );
  
  // Looping over each zone, and region in that zone.
  foreach ($zones as $zone => $item) {
    $width = 'alpha_' . $zone . '_container_width';
    $width = !empty($form_state['values'][$width]) ? $form_state['values'][$width] : alpha_theme_get_setting($width);
    $width = !empty($width) ? $width : min(array_keys($containers));
    
    // Creating an additional fieldset for each zone 
    $form['alpha_zones'][$zone] = array(
      '#type' => 'fieldset',
      '#title' => $item['name'],
    );
    
    $shortzone = &$form['alpha_zones'][$zone];
    
    // Provide a quick enable/disable option for this zone
    $shortzone['alpha_' . $zone . '_enabled'] = array(
  	  '#type' => 'checkbox',
      '#title' => t('Enable this zone'),
      '#description' => t('Disabling this zone will instantly hide the container and all regions within from being rendered on page.'),
      '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_enabled', TRUE),
      '#weight' => -50,
    );
    
    $shortzone['settings'] = array(
      '#type' => 'container',
      '#states' => array(
        'visible' => array(
          ':input[name="alpha_' . $zone . '_enabled"]' => array('checked' => TRUE),
        ),         
      ),
    );
    
    // Provide a full width wrapper around the zone (allowing for design elements outside the grid)
    $shortzone['settings']['alpha_' . $zone . '_wrapper'] = array(
      '#type' => 'checkbox',
      '#title' => t('Provide full width wrapper around this zone'),
      '#description' => t('Enabling this feature will give a &lt;div&gt; wrapper around the zone itself, allowing you to theme in elements that appear outside the 960 pixel container zone.'),
      '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_wrapper', TRUE),
    );
    
    // Create a container width selection menu for EACH zone
    $shortzone['settings']['alpha_' . $zone . '_container_width'] = array(
      '#type' => 'select',
      '#title' => t('Container width'),
      '#default_value' => $width,
      '#options' => $containers,
    );
    
    // Create a chooser for static/dynamic zone
    $shortzone['settings']['alpha_'. $zone .'_zone_type'] = array(
      '#type' => 'radios',
      '#description' => t('<p><em>Static zones</em> are displayed either inline or stacked based on settings.</p><p><em>Dynamic zones</em> are always displayed inline, with a primary region and must fit inside the container wrap. Dynamic zones also allow for push & pull classes for content first ordering.'),
      '#title' => t('Zone Type'),
      '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_zone_type', 'static'),
      '#options' => array(
        'static' => t('Static zone'),
        'dynamic' => t('Dynamic zone'),
      ),
    );
        
    // Decide which region is the primary item.
    // The primary region is the one that will absorb the size of empty regions that are related in the same zone.
    $shortzone['settings']['alpha_' . $zone . '_dynamic_zone_primary_region'] = array(
      '#type' => 'select',
      '#title' => t('Primary Region'),
      '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_dynamic_zone_primary_region'),
      '#options' => $item['regions'],
      '#description' => t('The primary region is the one that will gain width when associated regions are empty.'),
      '#states' => array(
        'visible' => array(
          ':input[name="alpha_' . $zone . '_zone_type"]' => array('value' => t('dynamic')),
        ),
      ),
    );
    
    // Allow for zone classes
    $shortzone['settings']['alpha_' . $zone . '_zone_extra_classes'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional zone classes'),
      '#size' => 60,
      '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_zone_extra_classes'),
      '#description' => t('You can quickly add custom CSS classes to your zone here.'),
    );

    // Create friendly names for regions
    // - Normal regions provide these automatically
    // - Special regions (branding, menu, breadcrumb, search need to have a friendly name created on the fly with ucwords()    
    if (!empty($item['regions'])) {
      $options = drupal_map_assoc(range(1, count($item['regions'])));
      
      $shortzone['settings']['regions'] = array(
        '#type' => 'item',
        '#title' => t('Region configuration'),
        '#id' => 'edit-zone-' . str_replace('_', '-', $zone) . '-regions',
      );
    
      foreach($item['regions'] as $region => $name) {
        $columns = $spacing = alpha_column_options($width);  
        
        array_pop($spacing);
        
        $shortzone['settings']['regions'][$region] = array(
          '#type' => 'fieldset',
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
          '#title' => $name,
          '#attributes' => array(
            'id' => 'edit-zone-' . str_replace('_', '-', $zone) . '-region-' . str_replace('_', '-', $region),
            'class' => array('clearfix'),
          ),
        );
        
        $shortregion = &$shortzone['settings']['regions'][$region];        

        $shortregion['alpha_' . $zone . '_' . $region . '_region_prefix'] = array(
          '#type' => 'select',
          '#title' => t('Prefix'),
          '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_' . $region . '_region_prefix'),
          '#options' => $spacing,
          '#prefix' => '<div class="alpha-region-configuration clearfix">',
        );
        
        $shortregion['alpha_' . $zone . '_' . $region . '_region_width'] = array(
          '#type' => 'select',
          '#title' => t('Width'),
          '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_' . $region . '_region_width'),
          '#options' => $columns,
        );
        
        $shortregion['alpha_' . $zone . '_' . $region . '_region_suffix'] = array(
          '#type' => 'select',
          '#title' => t('Suffix'),
          '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_' . $region . '_region_suffix'),
          '#options' => $spacing,
        );
        
        $shortregion['alpha_' . $zone . '_' . $region . '_region_order'] = array(
          '#type' => 'select',
          '#title' => t('Order'),
          '#options' => $options,
          '#default_value' => alpha_theme_get_setting('alpha_' . $zone . '_' . $region . '_region_order'),
        );
        
        $shortregion['alpha_' . $zone . '_' . $region . '_region_extra_classes'] = array(
          '#type' => 'textfield',
          '#title' => t('Additional region classes'),
          '#size' => 60,
          '#default_value' => t(alpha_theme_get_setting('alpha_' . $zone . '_' . $region . '_region_extra_classes')),
          '#suffix' => '</div>',
        );
      }     
    }
  }
}