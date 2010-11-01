<?php
// $Id$

/**
 * @file
 * Contents of omega_preprocess_region().
 */
/*
global $theme_key, $theme_info;
$theme_zones = $theme_info->info['zones'];

$region = $vars['region'];

// we need to determine the zone this region is associated with in order to 
// properly be able to gather the variables for this zone as the theme settings
// are stored in a format of 'omega_ZONEID_REGIONID_VARNAME'
foreach($theme_zones as $zone => $regions) {
  $search = array_search($region, $theme_zones[$zone]);
  if (is_int($search)) {
  	$parent_zone = $zone;
  }
}

// we had to have found a parent zone in order to continue.
// there is no reason for this to be false other than a region not being applied
// to a zone in the theme's .info file
if (isset($parent_zone)) {
	// get extra classes to assign to the region on the fly
	$region_classes = explode(' ', theme_get_setting('omega_'. $parent_zone . '_' . $region .'_region_extra_classes'));
	foreach ($region_classes as $class) {
	  if($class) {
	    $vars['classes_array'][] = $class;
	  }
	}
	// get the grid class for this item, along with prefix/suffix classes.
	// this must be changed to use the _static_region_builder functionality
	// or dynamic region_builder based on the zone.
	$vars['classes_array'][] = 'grid-' . theme_get_setting('omega_' . $parent_zone . '_' . $region . '_region_width');
	$prefix = theme_get_setting('omega_' . $parent_zone . '_' . $region . '_region_prefix');
	$suffix = theme_get_setting('omega_' . $parent_zone . '_' . $region . '_region_suffix');
	// if there IS a prefix class assigned, make it so.
	if($prefix) {
    $vars['classes_array'][] = 'prefix-' . $prefix;	
	}
	// if there IS a suffix class assigned, make it so.
	if ($suffix) {
		$vars['classes_array'][] = 'suffix-' . $suffix;
	}
}
*/
//drupal_set_message('<strong><em>preprocess-region.inc</em></strong> was called for <strong>'. $vars['region'] .'</strong>');


//krumo($vars);