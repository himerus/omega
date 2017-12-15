<?php

namespace Drupal\omega\Style;

/**
 * Defines an interface for managing Omega styles.
 */
interface OmegaStyleInterface {

  /**
   * Function to scan for and update any SCSS we have in the theme.
   *
   * @param string $source
   *   The full path to directory where source files are found.
   * @param string $theme
   *   The name of the themen we are operating on.
   * @param string $filetype
   *   The file extension we are looking to update. Defaults to scss.
   * @param string $ignore
   *   Regular expression of files to ignore when parsing directories.
   */
  public static function themeStylesUpdate($source, $theme, $filetype = 'scss', $ignore = '/^(\.(\.)?|CVS|_omega-style-vars\.scss|layout|\.sass-cache|\.svn|\.git|\.DS_Store)$/');

  /**
   * Updates the variables scss file.
   *
   * @param array $styles
   *   An array of scss variables to be parsed to a custom variables file.
   * @param string $theme
   *   The theme we are operating on.
   */
  public static function scssVariablesUpdate(array $styles, $theme);

  /**
   * Renders SCSS from variables.
   *
   * @todo: Implement compileScss() method.
   */
  public static function compileScss();

  /**
   * Renders CSS from SCSS.
   *
   * @param string $scss
   *   String value of SCSS to be converted to CSS.
   * @param array $options
   *   Options for CSS compiler.
   *
   * @return string
   *   Returns the resulting CSS code.
   */
  public static function compileCss($scss, array $options);

  /**
   * Function to gather appropriate import paths.
   *
   * Function to be used by compileCSS() to gather the appropriate
   * import paths to use for generating CSS in a theme.
   *
   * @param string $theme
   *   The theme we are operating on.
   *
   * @return array
   *   Array of import paths to use with the SCSS compiler.
   */
  public static function getImportPaths($theme);

  /**
   * Function to set import paths.
   *
   * @todo: Implement setImportPaths() method.
   */
  public static function setImportPaths();

  /**
   * Return an array of SCSS compiler options.
   *
   * @param string $relativeSource
   *   Relative source directory we are operating on.
   * @param string $file
   *   The file basename.
   * @param string $theme
   *   The theme we are operating on.
   *
   * @return array
   *   Array of options to be passed to the SCSS compiler.
   */
  public static function getScssOptions($relativeSource, $file, $theme);

  /**
   * Returns array of optional Libraries.
   *
   * Returns array of optional Libraries that can be enabled/disabled
   * in theme settings for Omega, and Omega sub-themes. The listings here
   * are tied to entries in omega.libraries.yml.
   *
   * @param string $theme
   *   The theme we are operating on.
   *
   * @return array
   *   Array of CSS/JS libraries to be used/configured/enabled..
   */
  public static function getOptionalLibraries($theme);

}
