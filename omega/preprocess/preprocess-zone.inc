<?php
// $Id: preprocess-zone.inc,v 1.1.2.5 2010/11/29 16:25:34 himerus Exp $

/**
 * @file
 * Contents of omega_preprocess_zone().
 * 
 * The functionality here is actually quite simple, applying to the container
 * regions of the 960 grid. 
 * 
 * There is not much processing needed to be done to the container elements
 * excluding checking for the appropriate classes/id to assign to the element
 */


/**
 * @file
 * Contents of omega_preprocess_zone().
 */

$zone = $vars['zone'];

// set the basic attributes for this zone
$vars['attributes_array'] = array(
  'class' => array(
    'clearfix', 
    'container-' . $zone['container_width'],
    'zone-' . $zone['zone_type'],
    'zone-' . $zone['type'],
    'zone',
  ),
  'id' => array($zone['zid'] . '-container'),
);

// set the extra classes to assign to this zone.
$zone_classes = explode(' ', omega_theme_get_setting('omega_'. $zone['zid'] . '_zone_extra_classes'));
foreach ($zone_classes as $class) {
  if(isset($class)) {
    $vars['attributes_array']['class'][] = $class;
  }
}
$vars['classes_array'] = $vars['attributes_array']['class'];
$vars['attributes'] = drupal_attributes($vars['attributes_array']);

$populated = TRUE;

$zone_regions = $zone['#regions'];
$zone['populated'] = $populated;

$regions = array();
$region_order = array();
foreach($zone_regions AS $region => $region_data) {
  $region_data = is_array($zone_regions[$region]) ? $zone_regions[$region] : FALSE;
  $regions[$region] = array(
    'data' => $region_data,
    'width' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_width'),
    'spacing' => array(
      'prefix' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_prefix'),
      'suffix' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_suffix')
    ),
  );
  if($zone['zone_type'] == 'dynamic') {
    // dynamic regions take a bit more configuration to apply content first ordering
    $primary_region = omega_theme_get_setting('omega_'. $zone['zid'] .'_dynamic_zone_primary_region') == $region ? TRUE : FALSE;
    // let's put in the array of related zones.
    if ($primary_region) {
      $regions[$region]['primary'] = TRUE;
      $related_regions = array();
      foreach($zone_regions AS $r => $d) {
        // leave out the current region in related info
        if ($r != $region) {
          $related_regions[$r] = array(
            'width' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $r . '_region_width'),
          );
        }
        $regions[$region]['related'] = $related_regions;
      }
    }
    $region_weight = intval(omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_order'));
    $region_order[$region_weight] = $region;
  }
}


// run the appropriate region builder
// all this does is assign the appropriate 960.gs classes to the regions
switch($zone['zone_type']) {
  case 'static':
    $region_classes = static_region_builder($regions, $zone['container_width']);    
    break;
  case 'dynamic':
    $region_order = omega_order_reorder($region_order);
    $region_classes = dynamic_region_builder($regions, $zone['container_width'], $region_order);
    break;
}

// let's handle some special regions
if(isset($zone['#omega'])) {
  $cvars = $zone['#omega']['page_data'];
  foreach($cvars as $var => $val) {
    $vars[$var] = $val;
  }
}

// cycle regions again and assign custom data for later rendering
foreach($zone_regions AS $region => $region_data) {
  $zone['#regions'][$region]['#omega'] = array(
    'classes' => isset($region_classes[$region]) ? $region_classes[$region] : array(),
    'attributes' => array('id' => 'region-' . $region),
    'page_data' => array(
      'title_attributes_array' => $vars['title_attributes_array'],
      'content_attributes_array' => $vars['content_attributes_array'],
      'title_prefix' => $vars['title_prefix'],
      'title_suffix' => $vars['title_suffix'],
      'feed_icons' => $vars['feed_icons'],
      'logo' => $vars['logo'],
      'main_menu' => $vars['main_menu'],
      'secondary_menu' => $vars['secondary_menu'],
      'site_name' => $vars['site_name'],
      'tabs' => $vars['tabs'],
      'menu_data' => $vars['menu_data'],
    ),
  );
  
}


$vars['enabled'] = $zone['enabled'];
$vars['populated'] = $zone['populated'];
$vars['wrapper'] = $zone['wrapper'];
$vars['zid'] = $zone['zid'];
// moving the region data out of $vars['zone'] so that it can be rendered as before in zone.tpl.php
$vars['regions'] = $zone['#regions'];

if($zone['zid'] == 'user') {
  //krumo($vars);
  //krumo($region_classes);
}