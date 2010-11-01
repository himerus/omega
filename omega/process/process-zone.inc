<?php
// $Id$

/**
 * @file
 * Contents of omega_preprocess_zone().
 */

// set the basic attributes for this zone
$zone = $vars;
$vars['attributes_array'] = array(
  'class' => array(
    'clearfix', 
    'container-' . $zone['container_width'],
    'zone-' . $zone['zone_type'],
    'zone-' . $zone['type'],
  ),
  'id' => array($zone['zid'] . '-container'),
);

// set the extra classes to assign to this zone.
$zone_classes = explode(' ', theme_get_setting('omega_'. $zone['zid'] . '_zone_extra_classes'));
foreach ($zone_classes as $class) {
  if($class) {
    $vars['attributes_array']['class'][] = $class;
  }
}
$vars['classes'] = implode(' ', $vars['attributes_array']['class']);
unset($vars['attributes_array']['class']);
// render the attributes to the template
$vars['attributes'] = drupal_attributes($vars['attributes_array']);
$zone_regions = $zone['regions'];
switch($zone['zone_type']) {
  case 'static':
    $regions = array();
    foreach($zone_regions AS $region => $region_data) {
      $regions[$region] = array(
        //'data' => render($zone_regions[$region]),
        'data' => TRUE,
        'width' => theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_width'),
        'spacing' => array(
          'prefix' => theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_prefix'),
          'suffix' => theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_suffix')
        ),
      );
    
    }
    // run static_region_builder() to return classes appropriate to each zone
    $vars['regions'][$region]['#omega_attributes'] = array();
    $vars['regions'][$region]['#omega_classes'] = array();
    $region_classes = static_region_builder($regions, $zone['container_width']);
    
    foreach($region_classes AS $region => $classes) {
      $vars['regions'][$region]['#omega_classes'] = $region_classes[$region];
      $vars['regions'][$region]['#omega_attributes']['id'] = array('region-'. $region);
    }
    
    //$vars['content'] = render($vars['regions']);
    //$vars['content'] = theme('region', $vars['regions']);
    
    break;
  case 'dynamic':
    
    break;
}
//krumo($vars);
//drupal_set_message('<strong><em>preprocess-zone.inc</em></strong> was called for <strong>'. $zone['zid'] .'</strong>');
//krumo($region_classes);