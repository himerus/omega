<?php
// $Id$

/**
 * @file
 * Contents of omega_preprocess_page().
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



/**
 * Cycle each zone to build out our actual page template data
 */
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

  $zones[$zone['type']][$zone['zid']] = theme('zone', $zone);
}


// zones appearing before content on page
$vars['zones_above'] = '';
$before = array_keys($zones_before_content);
foreach($before as $k => $zone) {
  $vars['zones_above'] .= $zones['before'][$zone];
}
// required content zone
$vars['content_zone'] = $zones['content']['content'];
// zones appearing after content on page
$vars['zones_below'] = '';
$after = array_keys($zones_after_content);
foreach($after as $k => $zone) {
  $vars['zones_below'] .= $zones['after'][$zone];
}

//krumo($zones);

/**
 * STATIC REGION BUILDER EXAMPLE
 */
/**

$vars['postscript_container_width'] = theme_get_setting('omega_postscript_container_width');
$postscript_regions = array(
  'postscript_first' => array(
    'data' => $vars['page']['postscript_first'],
    'width' => theme_get_setting('omega_postscript_first_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_first_prefix'),
      'suffix' => theme_get_setting('omega_postscript_first_suffix')),
    ),
  'postscript_second' => array(
    'data' => $vars['page']['postscript_second'],
    'width' => theme_get_setting('omega_postscript_second_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_second_prefix'),
      'suffix' => theme_get_setting('omega_postscript_second_suffix')),
    ),
  'postscript_third' => array(
    'data' => $vars['page']['postscript_third'],
    'width' => theme_get_setting('omega_postscript_third_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_third_prefix'),
      'suffix' => theme_get_setting('omega_postscript_third_suffix'))
  ),
  'postscript_fourth' => array(
    'data' => $vars['page']['postscript_fourth'],
    'width' => theme_get_setting('omega_postscript_fourth_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_fourth_prefix'),
      'suffix' => theme_get_setting('omega_postscript_fourth_suffix'))
  ),
);
$vars = static_region_builder($postscript_regions, theme_get_setting('postscript_container_width'), $vars); 
*/

// Set a variable for the site name title and logo alt attributes text.
$slogan_text = $vars['site_slogan'];
$site_name_text = $vars['site_name'];
$vars['site_name_and_slogan'] = $site_name_text . ' ' . $slogan_text;
if (isset($vars['logo'])) {
  //$vars['logo_img'] = theme('image', substr($vars['logo'], strlen(base_path())), t($vars['site_name']), t($vars['site_name']));
  // @todo - Why no theme_image()?
  $vars['logo_img'] = '<img src="' . $vars['logo'] . '" alt="' . $vars['site_name_and_slogan'] . '" id="logo" />';
}

// NINESIXTY - For easy printing of variables.
if (isset($vars['logo_img'])) {
  $vars['linked_logo_img'] = l($vars['logo_img'], '<front>', array('rel' => 'home', 'title' => t($vars['site_name']), 'html' => TRUE));
}
if (isset($vars['site_name'])) {
  $vars['linked_site_name'] = l($vars['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home')));
}

  if (isset($vars['main_menu'])) {
    $vars['primary_nav'] = theme('links__system_main_menu', array(
      'links' => $vars['main_menu'],
      'attributes' => array(
        'class' => array('links', 'main-menu'),
      ),
      'heading' => array(
        'text' => t('Main menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      )
    ));
  }
  else {
    $vars['primary_nav'] = FALSE;
  }
  if (isset($vars['secondary_menu'])) {
    $vars['secondary_nav'] = theme('links__system_secondary_menu', array(
      'links' => $vars['secondary_menu'],
      'attributes' => array(
        'class' => array('links', 'secondary-menu'),
      ),
      'heading' => array(
        'text' => t('Secondary menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      )
    ));
  }
  else {
    $vars['secondary_nav'] = FALSE;
  }

if (user_access('search content') && theme_get_setting('omega_render_search_box')) {
  $vars['search_region'] = drupal_get_form('search_block_form');
  $vars['search_region_classes'] = 'grid-' . theme_get_setting('omega_search_width');
}
