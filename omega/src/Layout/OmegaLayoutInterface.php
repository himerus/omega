<?php

namespace Drupal\omega\Layout;

use Drupal\breakpoint\Breakpoint;

/**
 * Defines an interface for managing an Omega layout.
 */
interface OmegaLayoutInterface {

  /**
   * Custom function to save the layout changes to appropriate config variables.
   *
   * Currently performs the following operations:
   *  - Compares layout submitted to function with the original in database
   *  - If they do not match, performs save() with updated values sent
   *  from function call
   *  - Check if $generated flag was passed as TRUE and save updated data to
   *  $theme.layout.$layout_id.generated which signifies that the layout
   *  variables HAVE been converted to SCSS/CSS.
   *
   * @param array $layout
   *   Array of layout data.
   * @param string $layout_id
   *   String value representing layout name/id.
   * @param string $theme
   *   The theme we are operating on.
   * @param bool $generate
   *   True if layout should be written, False if just saved to DB config.
   *
   * @return bool
   *   Returns TRUE on success, FALSE on failure.
   */
  public static function saveLayoutData(array $layout, $layout_id, $theme, $generate = FALSE);

  /**
   * Method to save layout to filesystem.
   *
   * @param string $scss
   *   String value of SCSS to be written to file.
   * @param string $theme
   *   The theme we are operating on.
   * @param string $layout_id
   *   The machine name of the layout we are editing.
   * @param array $options
   *   Array of options to pass the SCSS compiler.
   */
  public static function saveLayoutFiles($scss, $theme, $layout_id, array $options);

  /**
   * Method to export layout to .yml.
   *
   * @todo: Implement exportLayout() method.
   */
  public static function exportLayout();

  /**
   * Method to generate CSS from a specified layouts SCSS.
   *
   * @param array $layout
   *   The SCSS representing the layout.
   * @param string $layout_id
   *   The machine name of the layout we are editing.
   * @param string $theme
   *   The theme we are operating on.
   */
  public static function compileLayout(array $layout, $layout_id, $theme);

  /**
   * Method to generate layout SCSS from layout variables.
   *
   * Currently performs the following operations:
   *  - Cycles a given layout for breakpoints.
   *  - Cycles a breakpoint for region groups.
   *  - Cycles a region group for regions.
   *  - Cycles a region for various settings to apply to the region.
   *  - Returns SCSS designed to be passed to _omega_compile_css.
   *
   * @param array $layout
   *   Array of data representing the SCSS values to be used for the layout.
   * @param string $layoutName
   *   Machine name of the layout.
   * @param array $options
   *   Array of options to pass the SCSS compiler.
   * @param string $theme
   *   The theme we are operating on.
   *
   * @todo: Refactor all the things in OmegaLayout::compileLayoutScss().
   *
   * @return string
   *   Returns string representation of SCSS for layout.
   */
  public static function compileLayoutScss(array $layout, $layoutName, array $options, $theme = 'omega');

  /**
   * Method to compile CSS.
   *
   * @todo: Implement compileLayoutCss() method.
   * @todo: This method might not be needed?
   */
  public static function compileLayoutCss();

  /**
   * Method to return the layouts (and config) for an Omega theme/subtheme.
   *
   * @param string $theme
   *   The theme we are operating on.
   *
   * @return array
   *   Return array of layouts.
   */
  public static function getAvailableLayouts($theme);

  /**
   * Method to return an array of $options to pass to select menu.
   *
   * @param array $layouts
   *   Array of layouts.
   *
   * @return array
   *   Array of options to be used in form select field.
   */
  public static function getAvailableLayoutFormOptions(array $layouts);

  /**
   * Method to return the active layout to be used for the active page.
   */
  public static function getActiveLayout();

  /**
   * Method to return the theme that is providing a layout.
   *
   * This is either the theme itself ($theme) or a parent theme.
   *
   * @param string $theme
   *   The theme we are operating on.
   *
   * @return mixed
   *   Returns string theme name, else FALSE if theme isn't found.
   */
  public static function getLayoutProvider($theme);

  /**
   * Method to get all available breakpoints.
   *
   * @param string $theme
   *   The theme we are operating on.
   *
   * @return mixed
   *   Returns string theme name, else FALSE if theme isn't found.
   */
  public static function getAvailableBreakpoints($theme);

  /**
   * Method to get active breakpoints.
   *
   * @param string $layout
   *   Layout name.
   * @param string $theme
   *   Theme name.
   *
   * @return array
   *   Array of breakpoints.
   */
  public static function getActiveBreakpoints($layout, $theme);

  /**
   * Function calculates the width/push/pull/prefix/suffix of a primary region.
   *
   * @param array $main
   *   Array of width, push, pull, prefix and suffix from settings.
   * @param array $empty_regions
   *   Array of empty regions and their data in the same region group.
   * @param int $cols
   *   Total number of columns assigned using row(); for the region group.
   *
   * @return array
   *   Array of width, push, pull, prefix and suffix of adjusted primary region.
   */
  public static function layoutAdjust(array $main, array $empty_regions, $cols);

  /**
   * Function returns the trimmed name of the breakpoint id.
   *
   * Converts omega.standard.all to simply 'all'.
   *
   * @param \Drupal\breakpoint\Breakpoint $breakpoint
   *   Breakpoint object.
   *
   * @return string
   *   Trimmed breakpoint name.
   */
  public static function cleanBreakpointId(Breakpoint $breakpoint);

}
