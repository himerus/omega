<?php
// $Id: process-zone.inc,v 1.1.2.15 2011/01/14 14:08:47 himerus Exp $

/**
 * @file
 * Contents of omega_preprocess_zone().
 */
// set the basic attributes for this zone
$zone = $vars;
if($zone['zid'] == 'content') {
  //krumo($zone);
}
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
$zone_classes = explode(' ', omega_theme_get_setting('omega_'. $zone['zid'] . '_zone_extra_classes'));
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
// Let's provide our custom cases for branding, menu, breadcrumb and search
// we will need to accomodate moving the custom REGIONS into a new zone as well,
// but renaming the branding and location zones is pretty much forbade at this point.
$info = isset($zone['info']) ? $zone['info'] : array();


//$vars['regions'][$region_id]['#custom'] = array();


switch($zone['zid']) {
  case 'content':
    foreach($zone_regions as $region_id => $region) {
      switch($region_id) {
        case 'content':
          $vars['regions'][$region_id]['#custom'] = array();
          $vars['regions'][$region_id]['#custom']['title'] = $info['title'];
          $vars['regions'][$region_id]['#custom']['tabs'] = $info['tabs']; 
          $vars['regions'][$region_id]['#custom']['title_prefix'] = $info['title_prefix'];
          $vars['regions'][$region_id]['#custom']['title_suffix'] = $info['title_suffix']; 
          $vars['regions'][$region_id]['#custom']['title_attributes'] = $info['title_attributes_array'];
          // set some default title settings.
          $vars['regions'][$region_id]['#custom']['title_attributes_array']['id'] = 'page-title';
          $classes = array(
            'title-header',
            'header-main',
          );
          // assign the content_float region if it exists
          // this will be inserted into region--content.tpl.php
          if(isset($info['content_float'])) {
            //$vars['regions'][$region_id]['#custom']['content_float'] = render($info['content_float']);
          }
          // assign classes & attributes before sending along
          $title_classes = $vars['regions'][$region_id]['#custom']['title_classes'] = implode(' ', $classes);
          unset($vars['regions'][$region_id]['#custom']['title_attributes_array']['class']); 
          $title_attr = $vars['regions'][$region_id]['#custom']['title_attributes'] = drupal_attributes($vars['regions'][$region_id]['#custom']['title_attributes_array']);
          //krumo($vars['regions'][$region_id]);
          break;
      }
    }
  case 'branding':
    foreach($zone_regions as $region_id => $region) {
      switch($region_id) {
        case 'branding':
          // custom build the branding zone with our "custom" regions inside there
          // Set a variable for the site name title and logo alt attributes text.
          $vars['regions'][$region_id]['#custom'] = array();
          $site_name_and_slogan = $vars['regions'][$region_id]['#custom']['site_name_and_slogan'] = $info['site_name'] . ' ' . $info['slogan'];
          $slogan = $vars['regions'][$region_id]['#custom']['slogan'] = $info['slogan'];
          $site_name = $vars['regions'][$region_id]['#custom']['site_name'] = $info['site_name'];
          if (isset($info['logo'])) {
            $logo_img = $vars['regions'][$region_id]['#custom']['logo_img'] = '<img src="' . $info['logo'] . '" alt="' . $site_name_and_slogan . '" id="logo" />';
            $linked_logo_img = $vars['regions'][$region_id]['#custom']['linked_logo_img'] = l($logo_img, '<front>', array('rel' => 'home', 'title' => t($info['site_name']), 'html' => TRUE)); 
          }
          if (isset($info['site_name'])) {
            $vars['regions'][$region_id]['#custom']['linked_site_name'] = l($info['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home')));
          }
          
          $vars['regions'][$region_id]['#theme_wrappers'] = array('region');
          $vars['regions'][$region_id]['#markup'] = 'LOGO';
          $vars['regions'][$region_id]['#region'] = $region_id;

          break;
        case 'menu': 
          $vars['regions'][$region_id]['#custom'] = array();
          if (isset($info['main_menu'])) {
            $vars['regions'][$region_id]['#custom']['main_menu'] = $info['main_menu'];
          }
          if (isset($info['secondary_menu'])) {
            $vars['regions'][$region_id]['#custom']['secondary_menu'] = $info['secondary_menu'];
          }
          $vars['regions'][$region_id]['#theme_wrappers'] = array('region');
          $vars['regions'][$region_id]['#markup'] = 'MENU';
          $vars['regions'][$region_id]['#region'] = $region_id;
          break;
      }
    }
    
    break;
  case 'location':
    // custom build the location zone with our "custom" regions inside there
    foreach($zone_regions as $region_id => $region) {
      switch($region_id) {
        case 'breadcrumb':
          
          $vars['regions'][$region_id]['#custom']['breadcrumb'] = theme('breadcrumb', array('breadcrumb' => drupal_get_breadcrumb()));
          
          $vars['regions'][$region_id]['#theme_wrappers'] = array('region');
          $vars['regions'][$region_id]['#markup'] = $vars['regions'][$region_id]['#custom']['breadcrumb'];
          $vars['regions'][$region_id]['#region'] = $region_id; 
          break;
      }
    }
    break;
  default:
    foreach($zone_regions as $region_id => $region) {
      $vars['regions'][$region_id]['#custom'] = array();
    }
    break;
}

// Let's make sure to pass the zone id to the region processing function so we can use it later
$zone = $vars;
$zone_regions = $zone['regions'];
foreach($zone_regions AS $region => $region_data) {
  $vars['regions'][$region]['#custom']['zid'] = $zone['zid'];
}


switch($zone['zone_type']) {
  case 'static':
    $regions = array();
    foreach($zone_regions AS $region => $region_data) {
      $regions[$region] = array(
        'data' => element_children($zone_regions[$region]),
        //'data' => TRUE, // a test to enable data to always be true
        'width' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_width'),
        'spacing' => array(
          'prefix' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_prefix'),
          'suffix' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_suffix')
        ),
      );
      // special case for branding & location zones which are built in a different way (logo/slogan/menu)
      if($zone['zid'] == 'branding' || $zone['zid'] == 'location') {
        $regions[$region]['data'] = TRUE;
      }
    }
    
    // run static_region_builder() to return classes appropriate to each zone
    $vars['regions'][$region]['#omega_attributes'] = array();
    $vars['regions'][$region]['#omega_classes'] = array();
    $region_classes = static_region_builder($regions, $zone['container_width']);
    // add the classes & attributes to the region to be processed on render()
    foreach($region_classes AS $region => $classes) {
      $vars['regions'][$region]['#omega_classes'] = $region_classes[$region];
      $vars['regions'][$region]['#omega_attributes']['id'] = array('region-'. $region);
      if(array_key_exists('hidden', $classes)) {
        // edge case; unset regions that are defined as zero width or "hidden"
        unset($vars['regions'][$region]);
      }
    }
    break;
  case 'dynamic':
    $regions = array();
    $region_order = array();
    foreach($zone_regions AS $region => $region_data) {
      $primary_region = omega_theme_get_setting('omega_'. $zone['zid'] .'_dynamic_zone_primary_region') == $region ? TRUE : FALSE;
      $regions[$region] = array(
        'data' => element_children($zone_regions[$region]),
        'width' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_width'),
        'spacing' => array(
          'prefix' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_prefix'),
          'suffix' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_suffix')
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
              'width' => omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $r . '_region_width'),
            );
          }
          $regions[$region]['related'] = $related_regions;
        }
      }
      $region_weight = intval(omega_theme_get_setting('omega_' . $zone['zid'] . '_' . $region . '_region_order'));
      $region_order[$region_weight] = $region;
    }
    $region_order = omega_order_reorder($region_order);
    // run dynamic_region_builder() to return classes appropriate to each zone
    $vars['regions'][$region]['#omega_attributes'] = array();
    $vars['regions'][$region]['#omega_classes'] = array();
    $region_classes = dynamic_region_builder($regions, $zone['container_width'], $region_order);
    // add the classes & attributes to the region to be processed on render()
    foreach($region_classes AS $region => $classes) {
      $vars['regions'][$region]['#omega_classes'] = $region_classes[$region];
      $vars['regions'][$region]['#omega_attributes']['id'] = array('region-'. $region);    
      if(array_key_exists('hidden', $classes)) {
        // edge case; unset regions that are defined as zero width or "hidden"
        unset($vars['regions'][$region]);
      }
    }
    break;
}
if ($zone['zid'] == 'content') {
  //krumo($vars);
}