<?php

namespace Drupal\omega\Layout;

/**
 * Defines an interface for managing an Omega layout.
 */
interface OmegaLayoutInterface {

  /**
   * Custom function to save the layout changes to appropriate config variables
   * Currently performs the following operations:
   *  - Compares layout submitted to function with the original in database
   *  - If they do not match, performs save() with updated values sent from function call
   *  - Check if $generated flag was passed as TRUE and save updated data to $theme.layout.$layout_id.generated
   *    which signifies that the layout variables HAVE been converted to SCSS/CSS
   *
   *  Difference between $theme.layout.$layout_id and $theme.layout.$layout_id.generated
   *  - $theme.layout.$layout_id = latest layout configuration changes saved to database
   *    - saved to 'config' table on theme install through $theme.layout.$layout_id.yml
   *  - $theme.layout.$layout_id.generated = latest layout configuration changes to be generated into SCSS/CSS
   *    - saved/updated to 'config' table after "Save & Generate Layout" is called
   *
   */
  public static function saveLayoutData($layout, $layout_id, $theme, $generate = FALSE);

  /**
   * Method to save layout to filesystem.
   * @param $scss
   * @param $theme
   * @param $layout_id
   * @param $options
   * @return
   */
  public static function saveLayoutFiles($scss, $theme, $layout_id, $options);

  /**
   * Method to export layout to .yml.
   */
  public static function exportLayout();

  /**
   * Method to generate CSS from a specified layout.
   *
   * @param $layout
   * @param $layout_id
   * @param $theme
   * @return
   */
  public static function compileLayout($layout, $layout_id, $theme);

  /**
   * Method to generate layout SCSS from layout variables
   *
   * Currently performs the following operations:
   *  - Cycles a given layout for breakpoints
   *  - Cycles a breakpoint for region groups
   *  - Cycles a region group for regions
   *  - Cycles a region for various settings to apply to the region
   *  - Returns SCSS designed to be passed to _omega_compile_css
   *  @todo: Refactor all the things in OmegaLayout::compileLayoutScss().
   * @param $layout
   * @param $layoutName
   * @param string $theme
   * @param $options
   * @return
   */
  public static function compileLayoutScss($layout, $layoutName, $theme = 'omega', $options);

  /**
   * Method to compile CSS
   * @todo: This method might not be needed?
   */
  public static function compileLayoutCss();

  /**
   * Method to return the available layouts (and config) for a given Omega theme/subtheme.
   * @param $theme
   * @return
   */
  public static function getAvailableLayouts($theme);

  /**
   * Method to return an array of $options to pass to select menu via
   * Drupal Form API.
   *
   * @param $layouts
   * @return mixed
   */
  public static function getAvailableLayoutFormOptions($layouts);

  /**
   * Method to return the active layout to be used for the active page.
   */
  public static function getActiveLayout();

  /**
   * Method to return the theme that is providing a layout.
   * This is either the theme itself ($theme) or a parent theme.
   * @param $theme
   * @return
   */
  public static function getLayoutProvider($theme);

  /**
   * Method to get all available breakpoints.
   * @param $theme
   * @return
   */
  public static function getAvailableBreakpoints($theme);

  /**
   * Method to get active breakpoints.
   * @param $layout
   * @param $theme
   * @return
   */
  public static function getActiveBreakpoints($layout, $theme);

  /**
   * Helper function to calculate the new width/push/pull/prefix/suffix of a primary region
   * $main is the primary region for a group which will actually be the one we are adjusting
   * $empty_regions is an array of region data for regions that would be empty
   * $cols is the total number of columns assigned using row(); for the region group
   *
   * @param $main
   * @param array $empty_regions
   * @param $cols
   * @return array contains:
   *  - width, push, pull, prefix and suffix of adjusted primary region
   */
  public static function layoutAdjust($main, $empty_regions = array(), $cols);

  /**
   * Function returns the trimmed name of the breakpoint id
   * converting omega.standard.all to simply 'all'
   *
   * @param \Drupal\breakpoint\Breakpoint $breakpoint
   * @return string
   */
  public static function cleanBreakpointId(\Drupal\breakpoint\Breakpoint $breakpoint);
}
