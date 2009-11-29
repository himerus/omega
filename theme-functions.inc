<?php
<<<<<<< HEAD:theme-functions.inc
// $Id: theme-functions.inc,v 1.5.2.3 2009/10/24 14:59:26 himerus Exp $
=======
// $Id$
>>>>>>> 63c9d0c1bc2fbe6086ea93a046fcff1c467e9bf0:theme-functions.inc
/**
 * Return the theme settings' default values from the .info and save them into the database.
 *
 * @param $theme
 *   The name of theme.
 */
function omega_theme_get_default_settings($theme) {
  $themes = list_themes();
<<<<<<< HEAD:theme-functions.inc
  //krumo($themes[$theme]->info);
=======

>>>>>>> 63c9d0c1bc2fbe6086ea93a046fcff1c467e9bf0:theme-functions.inc
  // Get the default values from the .info file.
  $defaults = !empty($themes[$theme]->info['settings']) ? $themes[$theme]->info['settings'] : array();

  if (!empty($defaults)) {
    // Get the theme settings saved in the database.
    $settings = theme_get_settings($theme);
    // Don't save the toggle_node_info_ variables.
    if (module_exists('node')) {
      foreach (node_get_types() as $type => $name) {
        unset($settings['toggle_node_info_' . $type]);
      }
    }
    // Save default theme settings.
    variable_set(
      str_replace('/', '_', 'theme_' . $theme . '_settings'),
      array_merge($defaults, $settings)
    );
    // If the active theme has been loaded, force refresh of Drupal internals.
    if (!empty($GLOBALS['theme_key'])) {
      theme_get_setting('', TRUE);
    }
  }
  // Return the default settings.
  return $defaults;
<<<<<<< HEAD:theme-functions.inc
}

function omega_theme_generate_region_settings() {
  
}
function omega_theme_generate_region_options(){
  
=======
>>>>>>> 63c9d0c1bc2fbe6086ea93a046fcff1c467e9bf0:theme-functions.inc
}