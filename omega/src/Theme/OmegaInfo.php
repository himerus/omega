<?php

/**
 * @file
 * Contains \Drupal\omega\Theme\OmegaInfo.
 */

namespace Drupal\omega\Theme;

/**
 * omegaInfo declares methods used to return theme info
 * for use in themes, mainly the front end.
 */
class OmegaInfo {

  /**
   * The theme of the theme settings object.
   *
   * @var string
   */
  protected $theme;

  /**
   * The data of the theme settings object.
   *
   * @var array
   */
  protected $data;

  /**
   * Constructs a theme info object.
   *
   * @param string $theme
   *  The theme name
   */
  public function __construct($theme = 'omega') {
    $this->theme = $theme;
    $this->themes = \Drupal::service('theme_handler')->listInfo();
  }

  /**
   * Returns the theme of this theme info object.
   *
   * @return string
   *   The theme of this theme settings object.
   */
  public function getTheme() {
    return $this->theme;
  }

  /**
   * Returns either the whole info array for $this theme or just one key
   * if the $key parameter is set.
   *
   * @param string $key
   *   A string that maps to a key within the theme settings data.
   *
   * @return mixed
   *   The info data that was requested.
   */
  public function getThemeInfo($key = '') {
    if (empty($key)) {
      return $this->themes[$this->theme];
    }
    else {
      return isset($this->themes[$key]) ? $this->themes[$key] : NULL;
    }
  }
  /**
   * Return an array of Omega Sub-Themes ready for use/selection using FAPI #options
   *
   * @return array
   */
  public function omegaSubthemesOptionsList() {
    $omegaSubThemes = array();
    foreach ($this->themes as $theme) {
      if (isset($theme->base_themes) && array_key_exists('omega', $theme->base_themes)) {
        $theme_id = $theme->getName();
        $theme_name = $theme->info['name'];
        $omegaSubThemes[$theme_id] = $theme_name;
      }
    }
    return $omegaSubThemes;
  }

}
