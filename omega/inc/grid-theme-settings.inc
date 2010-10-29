<?php
// $Id$
$themes = list_themes();
$theme = $form_state['build_info']['args'][0];
$settings_theme_info = $themes[$theme]->info;

for ($i = 0; $i <= 24; $i++) {
  $grids[$i] = $i;
}
for ($i = 0; $i <= 23; $i++) {
  $spacing[$i] = $i;
}
$containers = array(
  '12' => '12 column grid (940 usable pixels)',
  '16' => '16 column grid (940 usable pixels)',
  '24' => '24 column grid (950 usable pixels)',
);
  
// Region Settings
$form['omega_regions'] = array(
  '#type' => 'vertical_tabs',
  '#prefix' => t('<h2 class="omega-config-title">960gs Region Settings</h2>'),
  '#weight' => -10,
  '#description' => t('Configure how your regions are rendered. This area is currently a quick implementation of an interface to allow end users to quickly build out and adjust the default page layout. This feature will be improved over time, and include much more flexibility.'),
);
$form['omega_regions']['defaults'] = array(
  '#type' => 'fieldset',
  '#title' => t('Default Configuration'),
);
$form['omega_regions']['defaults']['omega_default_container_width'] = array(
  '#type' => 'select',
  '#title' => t('Default container width'),
  '#default_value' => theme_get_setting('omega_default_container_width'),
  '#options' => $containers,
  '#weight' => -50,
  '#description' => t('This width is used for regions like $help, $messages and other non-important regions in page.tpl.php'),
);
$default_omega_layout = theme_get_setting('omega_fixed_fluid') ? theme_get_setting('omega_fixed_fluid') : 'fixed';
$form['omega_regions']['defaults']['omega_fixed_fluid'] = array(
  '#type' => 'radios',
  '#description' => t('You may select fluid layout, or the default fixed width layout.'),
  '#title' => t('Fixed / Fluid Layout'),
  '#default_value' => $default_omega_layout,
  '#options' => array(
    'fixed' => t('Fixed width (theme default)'),
    'fluid' => t('Fluid width'),
  ),
);

/**
 * Creating a wrapper fieldset for Zones
 */
$form['omega_regions']['zones'] = array(
  '#type' => 'fieldset',
  '#title' => t('Zone Configuration'),
);
$zones = $settings_theme_info['zones'];
$all_regions = $settings_theme_info['regions'];
/**
 * Looping over each zone, and region in that zone.
 */
foreach ($zones AS $zone => $regions) {
  // creating an additional fieldset for each zone 
	$form['omega_regions']['zones'][$zone] = array(
    '#type' => 'fieldset',
    '#title' => t(ucwords($zone) . ' Configuration'),
    '#prefix' => t('<h3 class="omega-accordion"><a href="#">' . ucwords($zone) . ' Zone Config</a></h3><div class="omega-accordion-content">'),
    '#suffix' => '</div>', 
  );
  // provide a quick enable/disable option for this zone
  $zone_enabled = theme_get_setting('omega_'. $zone .'_enabled') || theme_get_setting('omega_'. $zone .'_enabled') == 0 ? theme_get_setting('omega_'. $zone .'_enabled') : 1;
  $form['omega_regions']['zones'][$zone]['omega_'. $zone .'_enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable this Zone.'),
    '#description' => t('Disabling this zone will instantly hide the container and all regions within from being rendered on page.'),
    '#default_value' => $zone_enabled,
  '#weight' => -50,
  );
  // provide a full width wrapper around the zone (allowing for design elements outside the grid)
  $zone_wrapper = theme_get_setting('omega_'. $zone .'_wrapper') ? theme_get_setting('omega_'. $zone .'_wrapper') : 0;
  $form['omega_regions']['zones'][$zone]['omega_'. $zone .'_wrapper'] = array(
    '#type' => 'checkbox',
    '#title' => t('Provide full width wrapper around this zone.'),
    '#description' => t('Enabling this feature will give a &lt;div&gt; wrapper around the zone itself, allowing you to theme in elements that appear outside the 960 pixel container zone.'),
    '#default_value' => $zone_wrapper,
  );
  // create a chooser for static/dynamic zone
  $zone_type = theme_get_setting('omega_'. $zone .'_zone_type') ? theme_get_setting('omega_'. $zone .'_zone_type') : 'static';
  
  $form['omega_regions']['zones'][$zone]['omega_'. $zone .'_zone_type'] = array(
    '#type' => 'radios',
    '#description' => t('<p><strong>Static</strong> Zones are displayed either inline or stacked based on settings.</p><p><strong>Dynamic</strong> Zones are always displayed inline, with a primary region and MUST fit inside the container wrap. Dynamic zones also allow for push & pull classes for content first ordering.'),
    '#title' => t('Zone Type'),
    '#default_value' => $zone_type,
    '#options' => array(
      'static' => t('Static Zone'),
      'dynamic' => t('Dynamic Zone'),
    ),
  );
  // create a container width selection menu for EACH zone
  $form['omega_regions']['zones'][$zone]['omega_'. $zone .'_container_width'] = array(
    '#type' => 'select',
    '#title' => t('Container width'),
    '#default_value' => theme_get_setting('omega_'. $zone .'_container_width'),
    '#options' => $containers,
  );
  // provide content first layout ordering via push/pull if this is a dynamic zone
  if($zone_type == 'dynamic') {
  	$options = array();
  	
  	foreach($regions as $region) {
  		$option_val = $region;
  	  if (!isset($all_regions[$region])) {
        $option_txt = ucwords($region);
      }
      else {
        $option_txt = $all_regions[$region];
      }
  		foreach($regions as $other_region)
  		  if($region != $other_region) {
  		  	$option_val .= '-' . $other_region;
  		  	if (!isset($all_regions[$region])) {
  		  	  $option_txt .= ', ' . ucwords($other_region);
  		  	}
  		  	else {
  		  		$option_txt .= ', ' . $all_regions[$other_region];
  		  	}
  		  }
  		$options[$option_val] = t($option_txt);
  	}
  	
  	
  	$form['omega_regions']['zones'][$zone]['omega_'. $zone .'_dynamic_zone_order'] = array(
      '#type' => 'radios',
      '#title' => t('Region Ordering'),
      '#description' => t('Region Ordering uses the push/pull classes in 960.gs for content first ordering. The source order of the zones is how they appear below, but changing this option will allow them to display in an order more suitiable for your visual layout, without damaging SEO.'),
      '#default_value' => theme_get_setting('omega_'. $zone .'_dynamic_zone_order'),
      '#options' => $options,
    );
  }
  // allow for zone classes
  $form['omega_regions']['zones'][$zone]['omega_' . $zone . '_zone_extra_classes'] = array(
    '#type' => 'textfield',
    '#title' => t('Additional Classes'),
    '#size' => 60,
    '#default_value' => theme_get_setting('omega_' . $zone . '_zone_extra_classes'),
    '#description' => t('You can quickly add custom CSS classes to your zone here.'),
  );
  
  foreach($regions as $region) {
  	if (!isset($all_regions[$region]) || $region == 'content') {
  		$locked_flag = ' (special region)';
  		$region_name = ucwords(str_replace('_', ' ', $region));
  	}
  	else {
  		$locked_flag = '';
  		$region_name = $all_regions[$region];
  	}
  	
	  
	  $form['omega_regions']['zones'][$zone]['omega_' . $zone . '_' . $region . '_region_prefix'] = array(
	    '#prefix' => t('<h4 class="region-title">' . $region_name . $locked_flag . '</h4><div class="prefix_config">'),
	    '#type' => 'select',
	    '#title' => t('Prefix'),
	    '#default_value' => theme_get_setting('omega_' . $zone . '_' . $region . '_region_prefix'),
	    '#options' => $spacing,
	    '#suffix' => '</div>',
	  );
	  $form['omega_regions']['zones'][$zone]['omega_' . $zone . '_' . $region . '_region_width'] = array(
      '#prefix' => '<div class="width_config">',
	    '#type' => 'select',
      '#title' => t('Width'),
      '#default_value' => theme_get_setting('omega_' . $zone . '_' . $region . '_region_width'),
      '#options' => $grids,
      '#suffix' => '</div>',
    );
	  $form['omega_regions']['zones'][$zone]['omega_' . $zone . '_' . $region . '_region_suffix'] = array(
	    '#prefix' => '<div class="suffix_config">',
	    '#type' => 'select',
	    '#title' => t('Suffix'),
	    '#default_value' => theme_get_setting('omega_' . $zone . '_' . $region . '_region_suffix'),
	    '#options' => $spacing,
	    '#suffix' => '</div>',
	  );
	  
	  $form['omega_regions']['zones'][$zone]['omega_' . $zone . '_' . $region . '_region_extra_classes'] = array(
      '#type' => 'textfield',
      '#title' => t('Additional Classes'),
      '#size' => 60,
      '#default_value' => theme_get_setting('omega_' . $zone . '_' . $region . '_region_extra_classes'),
      '#description' => t('You can quickly add custom CSS classes to your regions here.'),
    );
  }
}