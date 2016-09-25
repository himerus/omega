<?php
/**
 * This functions file is to be deprecated & removed prior to a stable 8.x
 * release of Omega Five.
 */

use Drupal\omega\Layout\OmegaLayout;
use Drupal\omega\Style\OmegaStyle;

/**
 * Function returns the trimmed name of the breakpoint id
 * converting omega.standard.all to simply 'all'
 *
 * Use: Drupal\omega\Layout\OmegaLayout::cleanBreakpointId();
 *
 * @deprecated
 * @param \Drupal\breakpoint\Breakpoint $breakpoint
 * @return mixed
 */
function omega_return_clean_breakpoint_id(\Drupal\breakpoint\Breakpoint $breakpoint) {
  return OmegaLayout::cleanBreakpointId($breakpoint);
}

/**
 * Custom function to return the available layouts (and config) for a given Omega theme/subtheme
 *
 * Use: Drupal\omega\Layout\OmegaLayout::getAvailableLayouts();
 *
 * @deprecated
 * @param $theme
 * @return array|mixed|null
 */
function omega_return_layouts($theme) {
  return OmegaLayout::getAvailableLayouts($theme);
}

/**
 * Custom function to return the theme that is providing a layout
 * This is either the theme itself ($theme) or a parent theme
 *
 * Use: Drupal\omega\Layout\OmegaLayout::getLayoutProvider();
 *
 * @deprecated
 * @param $theme
 * @return int|string
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
 * Takes $theme as argument, and returns ALL breakpoint groups available to this theme
 * which includes breakpoints defined by the theme itself or any base theme of this theme
 *
 * Use: Drupal\omega\Layout\OmegaLayout::getAvailableBreakpoints();
 *
 * @deprecated
 * @param $theme
 * @return mixed
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
 * @param $layout
 * @param $theme
 * @return mixed
 */
function _omega_getActiveBreakpoints($layout, $theme) {
  return OmegaLayout::getActiveBreakpoints($layout, $theme);
}

/**
 *  Returns array of optional Libraries that can be enabled/disabled in theme settings
 *  for Omega, and Omega sub-themes. The listings here are tied to entries in omega.libraries.yml.
 *
 * Use: Drupal\omega\Layout\OmegaStyle::getOptionalLibraries();
 *
 * @deprecated
 * @param $theme
 * @return array
 */

function _omega_optional_libraries($theme) {
  return OmegaStyle::getOptionalLibraries($theme);
}
