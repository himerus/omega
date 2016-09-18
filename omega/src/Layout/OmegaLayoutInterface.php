<?php

namespace Drupal\omega\Layout;

/**
 * Defines an interface for managing an Omega layout.
 */
interface OmegaLayoutInterface {

  /**
   * Method to save layout to database.
   */
  public function saveLayoutData();

  /**
   * Method to save layout to filesystem.
   */
  public function saveLayoutFiles();

  /**
   * Method to export layout to .yml.
   */
  public function exportLayout();

  /**
   * Method to compile layout to SCSS.
   */
  public function compileLayout();

  /**
   * Method to generate SCSS from array of variables.
   */
  public function compileLayoutScss();

  /**
   * Method to generate CSS from SCSS.
   */
  public function compileLayoutCss();

  /**
   * Method to get active breakpoints.
   */
  public function getActiveBreakpoints();

  /**
   * Helper function to calculate the new width/push/pull/prefix/suffix of a primary region
   * $main is the primary region for a group which will actually be the one we are adjusting
   * $empty_regions is an array of region data for regions that would be empty
   * $cols is the total number of columns assigned using row(); for the region group
   */
  public function layoutAdjust();
}
