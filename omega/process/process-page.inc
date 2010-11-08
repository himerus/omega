<?php
// $Id$

/**
 * @file
 * Contents of omega_process_page().
 */

// fix messages 
// this should NOT be an empty string if it's freaking empty
if (strlen($vars['messages']) == 0) {
  unset($vars['messages']);
}

global $theme_key, $theme_info;
// theme_key is the name of the current theme
$vars['theme_key'] = $theme_key;
// theme_info is the array of theme information (region, settings, zones, etc.)
$vars['theme_info'] = $theme_info;
// default container width will be used in special zones and zones without a 
// container width defined in theme settings
$vars['default_container_width'] = theme_get_setting('omega_default_container_width');
// pulling just the zone data out of the theme_info array
$theme_zones = $theme_info->info['zones'];
// creating empty array to hold our custom zone[group] data
$zones = array(
  'before' => array(),
  'content' => array(),
  'after' => array(),
);
// separate out the specific content zone (a very special case)
$content_zone = $theme_zones['content'];
// zone keys give us a way to find the numerical position of the content zone
// thus giving us a way to split up the array into before and after content zones
$zone_keys = array_keys($theme_zones);
// content_position is the numberical location of the content zone
$content_position = array_search('content', $zone_keys);
// zones_before_content are all zones that appear before content in the array
$zones_before_content = array_slice($theme_zones, 0, $content_position, TRUE);
// zones_after_content are all zones that appear after content in the array
$zones_after_content = array_slice($theme_zones, $content_position + 1, count($theme_zones), TRUE);

foreach ($theme_zones as $zone_ref => $regions) {
  $zone = array();
  $zone['zid'] = $zone_ref;
  if(array_key_exists($zone_ref, $zones_before_content)) {
    $zone['type'] = 'before';
  }
  elseif(array_key_exists($zone_ref, $zones_after_content)) {
    $zone['type'] = 'after';
  }
  else {
    $zone['type'] = 'content';
  }
  $zone['enabled'] = theme_get_setting('omega_'. $zone_ref .'_enabled') || theme_get_setting('omega_'. $zone_ref .'_enabled') == 0 ? theme_get_setting('omega_'. $zone_ref .'_enabled') : 1;
  $zone['wrapper'] = theme_get_setting('omega_'. $zone_ref .'_wrapper') ? theme_get_setting('omega_'. $zone_ref .'_wrapper') : 0;
  $zone['zone_type'] = theme_get_setting('omega_'. $zone_ref .'_zone_type') ? theme_get_setting('omega_'. $zone_ref .'_zone_type') : 'static';
  $zone['container_width'] = theme_get_setting('omega_'. $zone_ref .'_container_width') ? theme_get_setting('omega_'. $zone_ref .'_container_width') : $vars['default_container_width'];
  
  $zone['regions'] = array();
  foreach($regions as $region) {
    $zone['regions'][$region] = isset($vars['page'][$region]) ? $vars['page'][$region] : '';
  }
  if ($zone['enabled']) {
  	if ($zone['zid'] == 'branding') {
  		// for the branding zone, we need to send additional vars to the zone process functions
  		// that includes basic page data like slogan, logo, etc.
  		$zone['info'] = array(
        'slogan' => $vars['site_slogan'],
        'site_name' => $vars['site_name'],
        'logo' => $vars['logo'],
        'main_menu' => $vars['main_menu'],
        'secondary_menu' => $vars['secondary_menu'],
      );
  	}
  	if($zone['zid'] == 'content') {
  		// let's send some standard stuff to the content region template like
  		// the title & attributes, along with the tabs for the page.
  		$zone['info'] = array(
        'title' => $vars['title'],
  		  'title_prefix' => $vars['title_prefix'],
  		  'title_suffix' => $vars['title_suffix'],
  		  'title_attributes_array' => $vars['title_attributes_array'],
        'tabs' => $vars['tabs'],
      );
  	}
    $zones[$zone['type']][$zone['zid']] = theme(array('zone__' . $zone['zid'], 'zone__' . $zone['type'], 'zone'), $zone);
  }
}

// zones appearing before content on page
$vars['zones_above'] = '';
$before = array_keys($zones_before_content);
foreach($before as $k => $zone) {
  if (isset($zones['before'][$zone])) {
    $vars['zones_above'] .= $zones['before'][$zone];
  }
}

// required content zone
$vars['content_zone'] = $zones['content']['content'];

// zones appearing after content on page
$vars['zones_below'] = '';
$after = array_keys($zones_after_content);
foreach($after as $k => $zone) {
  if (isset($zones['after'][$zone])) {
    $vars['zones_below'] .= $zones['after'][$zone];
  }
}
//krumo($vars);