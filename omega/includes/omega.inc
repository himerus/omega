<?php 

/**
 * @file
 * Helper functions for the Alpha base theme.
 */

/**
 * A wrapper function for theme_get_settings().
 * 
 * @param $name
 * 	 The name of the setting that you want to retrieve.
 * 
 * @param $theme (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to the global variable $theme_key.
 *   
 * @param $default (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @see 
 * 	 theme_get_setting().
 */
function omega_theme_get_setting($name, $theme = NULL, $default = NULL) {
  return alpha_theme_get_setting($name, $theme, $default);
}

/**
 * A helper function for retrieving zone settings.
 * 
 * @param $name
 * 	 The name of the setting that you want to retrieve.
 * 
 * @param $zone
 * 	 The zone that you want to fetch the setting for.
 * 
 * @param $theme (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to the global variable $theme_key.
 *   
 * @param $default (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @see 
 * 	 alpha_theme_get_setting().
 * 
 * @see
 * 	 theme_get_setting().
 */
function omega_zone_get_setting($name, $zone, $theme = NULL, $default = NULL) {
  return alpha_zone_get_setting($name, $zone, $theme, $default);
}

/**
 * A helper function for retrieving region settings.
 * 
 * @param $name
 * 	 The name of the setting that you want to retrieve.
 * 
 * @param $region
 * 	 The region that you want to fetch the setting for.
 * 
 * @param $theme (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to the global variable $theme_key.
 *   
 * @param $default (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @see 
 * 	 alpha_theme_get_setting().
 * 
 * @see
 * 	 theme_get_setting().
 */
function omega_region_get_setting($name, $region, $theme = NULL, $default = NULL) {
  return alpha_region_get_setting($name, $region, $theme, $default);
}

/**
 * A helper function to gather all non-region and non-zone settings for the
 * theme defined by $key.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the settings for.
 *
 * @return
 *   A nested array of theme settings.
 */
function omega_settings($key) {
  return alpha_settings($key);
}