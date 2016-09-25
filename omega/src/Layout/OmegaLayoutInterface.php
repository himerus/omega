<?php

namespace Drupal\omega\Layout;

/**
 * Defines an interface for managing an Omega layout.
 */
interface OmegaLayoutInterface {

  /**
   * Method to save layout to database.
   */
  public static function saveLayoutData();

  /**
   * Method to save layout to filesystem.
   */
  public static function saveLayoutFiles();

  /**
   * Method to export layout to .yml.
   */
  public static function exportLayout();

  /**
   * Method to compile layout to SCSS.
   */
  public static function compileLayout();

  /**
   * Method to generate SCSS from array of variables.
   */
  public static function compileLayoutScss();

  /**
   * Method to generate CSS from SCSS.
   */
  public static function compileLayoutCss();

  /**
   * Method to return the available layouts (and config) for a given Omega theme/subtheme.
   * @param $theme
   * @return
   */
  public static function getAvailableLayouts($theme);

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
   */
  public static function layoutAdjust();

  /**
   * Function returns the trimmed name of the breakpoint id
   * converting omega.standard.all to simply 'all'
   *
   * @param \Drupal\breakpoint\Breakpoint $breakpoint
   * @return string
   */
  public static function cleanBreakpointId(\Drupal\breakpoint\Breakpoint $breakpoint);
}
