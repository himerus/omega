<?php
// $Id$

/**
 * @file
 * Contents of omega_preprocess_region().
 */
global $theme_key, $theme_info;
$theme_zones = $theme_info->info['zones'];

$region = $vars['region'];
foreach($theme_zones as $zone => $regions) {
  $search = array_search($region, $theme_zones[$zone]);
  if (is_int($search)) {
  	$parent_zone = $zone;
  }
}
if (isset($parent_zone)) {
	$region_classes = explode(' ', theme_get_setting('omega_'. $parent_zone . '_' . $region .'_region_extra_classes'));
	foreach ($region_classes as $class) {
	  if($class) {
	    $vars['classes_array'][] = $class;
	  }
	}
	$vars['classes_array'][] = 'grid-' . theme_get_setting('omega_' . $parent_zone . '_' . $region . '_region_width');
}
