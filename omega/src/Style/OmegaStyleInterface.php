<?php

namespace Drupal\omega\Style;

/**
 * Defines an interface for managing Omega styles.
 */
interface OmegaStyleInterface {

  /**
   * Function to scan for and update any SCSS we have in the theme.
   */
  public function themeStylesUpdate();

  /**
   * Updates the variables scss file.
   */
  public function scssVariablesUpdate();

  /**
   * Renders SCSS from variables.
   */
  public function compileScss();

  /**
   * Renders CSS from SCSS.
   */
  public function compileCss();

  /**
   * Function to be used by compileCSS() to gather the appropriate
   * import paths to use for generating CSS in a theme.
   */
  public function getImportPaths();

  /**
   * Function to set import paths.
   */
  public function setImportPaths();

  /**
   * Return an array of SCSS compiler options.
   */
  public function getScssOptions();
}
