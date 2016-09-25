<?php

namespace Drupal\omega\Style;

/**
 * Defines an interface for managing Omega styles.
 */
interface OmegaStyleInterface {

  /**
   * Function to scan for and update any SCSS we have in the theme.
   */
  public static function themeStylesUpdate();

  /**
   * Updates the variables scss file.
   */
  public static function scssVariablesUpdate();

  /**
   * Renders SCSS from variables.
   */
  public static function compileScss();

  /**
   * Renders CSS from SCSS.
   */
  public static function compileCss();

  /**
   * Function to be used by compileCSS() to gather the appropriate
   * import paths to use for generating CSS in a theme.
   */
  public static function getImportPaths();

  /**
   * Function to set import paths.
   */
  public static function setImportPaths();

  /**
   * Return an array of SCSS compiler options.
   */
  public static function getScssOptions();

  /**
   * Returns array of optional Libraries that can be enabled/disabled in theme settings
   * for Omega, and Omega sub-themes. The listings here are tied to entries in omega.libraries.yml.
   * @param $theme
   * @return
   */
  public static function getOptionalLibraries($theme);
}
