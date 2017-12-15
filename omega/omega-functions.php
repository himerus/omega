<?php

/**
 * @file
 * This file is to be removed prior to a stable 8.x release of Omega Five.
 */

/* @codingStandardsIgnoreStart */

use Drupal\omega\Layout\OmegaLayout;
use Drupal\omega\Style\OmegaStyle;
use Drupal\breakpoint\Breakpoint;

/**
 * Function returns the trimmed name of the breakpoint id.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::cleanBreakpointId();
 *
 * @deprecated
 */
function omega_return_clean_breakpoint_id(Breakpoint $breakpoint) {
  return OmegaLayout::cleanBreakpointId($breakpoint);
}

/**
 * Function to return the available layouts for a given Omega theme/subtheme.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::getAvailableLayouts();
 *
 * @deprecated
 */
function omega_return_layouts($theme) {
  return OmegaLayout::getAvailableLayouts($theme);
}

/**
 * Custom function to return the theme that is providing a layout.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::getLayoutProvider();
 *
 * @deprecated
 */
function omega_find_layout_provider($theme) {
  return OmegaLayout::getLayoutProvider($theme);
}

/**
 * Custom function to return the active layout to be used for the active page.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::getActiveLayout();
 *
 * @deprecated
 */
function omega_return_active_layout() {
  return OmegaLayout::getActiveLayout();
}

/**
 * Takes $theme as argument, and returns ALL breakpoint groups available.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::getAvailableBreakpoints();
 *
 * @deprecated
 */
function _omega_getAvailableBreakpoints($theme) {
  return OmegaLayout::getAvailableBreakpoints($theme);
}

/**
 * Returns active breakpoints.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::getActiveBreakpoints();
 *
 * @deprecated
 */
function _omega_getActiveBreakpoints($layout, $theme) {
  return OmegaLayout::getActiveBreakpoints($layout, $theme);
}

/**
 * Returns array of optional Libraries that can be enabled/disabled.
 *
 * Use: Drupal\omega\Layout\OmegaStyle::getOptionalLibraries();
 *
 * @deprecated
 */
function _omega_optional_libraries($theme) {
  return OmegaStyle::getOptionalLibraries($theme);
}
/* @codingStandardsIgnoreEnd */
