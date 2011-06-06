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