<?php

namespace Drupal\omega\Style;

/**
 * Defines an interface for managing Omega styles.
 */
interface OmegaStyleInterface {

  /**
   * Function to scan for and update any SCSS we have in the theme.
   * @param $source
   * @param $theme
   * @param string $filetype
   * @param string $ignore
   * @return
   */
  public static function themeStylesUpdate($source, $theme, $filetype = 'scss', $ignore = '/^(\.(\.)?|CVS|_omega-style-vars\.scss|layout|\.sass-cache|\.svn|\.git|\.DS_Store)$/');

  /**
   * Updates the variables scss file.
   * @param $styles
   * @param $theme
   * @return
   */
  public static function scssVariablesUpdate($styles, $theme);

  /**
   * Renders SCSS from variables.
   */
  public static function compileScss();

  /**
   * Renders CSS from SCSS.
   * @param $scss
   * @param $options
   * @return string CSS
   */
  public static function compileCss($scss, $options);

  /**
   * Function to be used by compileCSS() to gather the appropriate
   * import paths to use for generating CSS in a theme.
   * @param $theme
   * @return
   */
  public static function getImportPaths($theme);

  /**
   * Function to set import paths.
   */
  public static function setImportPaths();

  /**
   * Return an array of SCSS compiler options.
   * @param $relativeSource
   * @param $file
   * @param $theme
   * @return
   */
  public static function getScssOptions($relativeSource, $file, $theme);

  /**
   * Returns array of optional Libraries that can be enabled/disabled in theme settings
   * for Omega, and Omega sub-themes. The listings here are tied to entries in omega.libraries.yml.
   * @param $theme
   * @return
   */
  public static function getOptionalLibraries($theme);
}
