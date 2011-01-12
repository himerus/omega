<?php
// $Id: process-region.inc,v 1.1.2.10 2011/01/11 21:26:32 himerus Exp $

/**
 * @file
 * Contents of omega_process_region().
 */
$omega_classes = isset($vars['elements']['#omega_classes']) ? $vars['elements']['#omega_classes'] : array();
$omega_attributes = isset($vars['elements']['#omega_attributes']) ? $vars['elements']['#omega_attributes'] : array();


$zone = isset($vars['elements']['#custom']['zid']) ? $vars['elements']['#custom']['zid'] : array();
$region = $vars['region'];
// set the extra classes to assign to this zone.
$region_classes = explode(' ', omega_theme_get_setting('omega_'. $zone . '_' . $region. '_region_extra_classes'));
foreach ($region_classes as $class) {
  if($class) {
    $vars['classes_array'][] = $class;
  }
}

$vars['classes_array'] = array_merge($vars['classes_array'], $omega_classes);
$vars['attributes_array'] = array_merge($vars['attributes_array'], $omega_attributes);

$vars['classes'] = implode(' ', $vars['classes_array']);
$vars['attributes'] = drupal_attributes($vars['attributes_array']);

// let's handle some special regions
if(isset($vars['elements']['#custom'])) {
  $cvars = $vars['elements']['#custom'];
  foreach($cvars as $var => $val) {
    $vars[$var] = $val;
  }
}