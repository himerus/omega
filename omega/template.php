<?php

require_once dirname(__FILE__) . '/includes/omega.inc';
require_once dirname(__FILE__) . '/includes/omega.theme.inc';

/**
 * Implements hook_alpha_regions_alter().
 */
function omega_alpha_regions_alter(&$regions, $theme) {
  foreach ($regions as $region => &$item) {
    $item['equal_height'] = alpha_region_get_setting('equal_height', $region, FALSE, $theme);
  }
}

/**
 * Implements hook_alpha_zones_alter().
 */
function omega_alpha_zones_alter(&$zones, $theme) {
  foreach ($zones as $zone => &$item) {
    $item['equal_height'] = alpha_zone_get_setting('equal_height', $zone, FALSE, $theme);
  }
}

/**
 * Implements hook_alpha_grid_registry_alter().
 */
function omega_alpha_grids_registry_alter(&$grids, $theme) {
  $path = drupal_get_path('theme', 'omega') . '/css';
  
  if (alpha_library_active('omega_equalheights') && $attached = alpha_find_stylesheet($path, 'omega-equalheights')) {
    foreach ($grids as &$grid) {
      foreach ($grid['layouts'] as &$layout) {
        array_unshift($layout['attached'], $attached);
      }
    }    
  }
}