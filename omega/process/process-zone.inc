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
        'data' => render($zone_regions[$region]),
        //'data' => TRUE, // a test to enable data to always be true
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
    // add the classes & attributes to the region to be processed on render()
    foreach($region_classes AS $region => $classes) {
      $vars['regions'][$region]['#omega_classes'] = $region_classes[$region];
      $vars['regions'][$region]['#omega_attributes']['id'] = array('region-'. $region);
    }
    break;
  case 'dynamic':
    $regions = array();
    $region_order = array();
    foreach($zone_regions AS $region => $region_data) {
    	$primary_region = theme_get_setting('omega_'. $zone['zid'] .'_dynamic_zone_primary_region') == $region ? TRUE : FALSE;
      $regions[$region] = array(
        'data' => render($zone_regions[$region]),
        'width' => theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_width'),
        'spacing' => array(
          'prefix' => theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_prefix'),
          'suffix' => theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_suffix')
        ),
      );
      // let's put in the array of related zones.
      if ($primary_region) {
      	$regions[$region]['primary'] = TRUE;
      	$related_regions = array();
      	foreach($zone_regions AS $r => $d) {
          // leave out the current region in related info
      		if ($r != $region) {
          	$related_regions[$r] = array(
          	  'width' => theme_get_setting('omega_' . $zone['zid'] . '_' . $r . '_region_width'),
          	);
          }
          $regions[$region]['related'] = $related_regions;
      	}
      }
      $region_weight = intval(theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_order'));
      //krumo($region_weight);
      $region_order[$region_weight] = $region;
    }
    $region_order = omega_order_reorder($region_order);
    // run dynamic_region_builder() to return classes appropriate to each zone
    $vars['regions'][$region]['#omega_attributes'] = array();
    $vars['regions'][$region]['#omega_classes'] = array();
    $region_classes = dynamic_region_builder($regions, $zone['container_width'], $region_order);
    //krumo($region_classes);
    // add the classes & attributes to the region to be processed on render()
    foreach($region_classes AS $region => $classes) {
      $vars['regions'][$region]['#omega_classes'] = $region_classes[$region];
      $vars['regions'][$region]['#omega_attributes']['id'] = array('region-'. $region);
    }
    break;
}
//krumo($vars);