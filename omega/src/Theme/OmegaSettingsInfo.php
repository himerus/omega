<?php

/**
 * @file
 * Contains \Drupal\omega\Theme\OmegaSettingsInfo.
 */

namespace Drupal\omega\Theme;

/**
 * OmegaSettingsInfo declares methods used to return theme info for use in
 * theme-settings.php. Note the constructor calls system_rebuild_theme_data()
 * which is not statically cached therefor only used in the backend, however
 * it always returns fresh data.
 */
class OmegaSettingsInfo extends OmegaInfo {

  /**
   * Constructs a theme info object.
   *
   * @param string $theme
   *  The theme name
   */
  public function __construct($theme) {
    $this->theme = $theme;
    $this->themes = \Drupal::service('theme_handler')->rebuildThemeData();
  }

  /**
   * Check if a theme name already exists.
   * Looks in the list of themes to see if a theme name already exists, if so
   * returns TRUE. This is the callback method for the form field machine_name
   *
   * @param $machine_name
   *   A themes machine name.
   *
   * @return boolean
   */
  public function omegaThemeExists($machine_name) {
    $result = FALSE;
    if (array_key_exists($machine_name, $this->themes)) {
      $result = TRUE;
    }
    return $result;
  }
  
} // end OmegaSettingsInfo class
