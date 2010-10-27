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
$form['omega_regions']['zones'] = array(
  '#type' => 'fieldset',
  '#title' => t('Zone Configuration'),
);
$zones = $settings_theme_info['zones'];
$all_regions = $settings_theme_info['regions'];
foreach ($zones AS $zone => $regions) {
   $form['omega_regions']['zones'][$zone] = array(
    '#type' => 'fieldset',
    '#title' => t(ucwords($zone) . ' Configuration'),
   '#prefix' => t('<h3 class="omega-accordion"><a href="#">' . ucwords($zone) . ' Zone Config</a></h3><div class="omega-accordion-content">'),
   '#suffix' => '</div>', 
  );
  foreach($regions as $region) {
  	if (!isset($all_regions[$region]) || $region == 'content') {
  		$locked_flag = ' (locked region)';
  	}
  	else {
  		$locked_flag = '';
  	}
    $form['omega_regions']['zones'][$zone][$region] = array(
      '#type' => 'item',
      '#title' => t($region . $locked_flag),
      //'#markup' => t($region),
    );
  }
}

krumo($zones);
krumo($all_regions);
krumo($form);
//krumo($settings_theme_info);