<?php

use Drupal\omega\Layout\OmegaLayout;
use Drupal\omega\Style\OmegaStyle;

/**
 * Function to take an array of style variables and create the appropriate
 * SCSS based on those variables.
 * Use: Drupal\omega\Layout\OmegaStyle::scssVariablesUpdate();
 *
 * @deprecated
 * @param $styles
 * @param $theme
 */
function _omega_update_style_scss($styles, $theme) {
  OmegaStyle::scssVariablesUpdate($styles, $theme);
}

/**
 * Use: Drupal\omega\Layout\OmegaStyle::themeStylesUpdate();
 *
 * @deprecated
 * @param $source
 * @param $theme
 * @param string $filetype
 * @param string $ignore
 * @todo Update this to leafo/scssphp
 */
function scssDirectoryScan($source, $theme, $filetype = 'scss', $ignore = '/^(\.(\.)?|CVS|_omega-style-vars\.scss|layout|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
  OmegaStyle::themeStylesUpdate($source, $theme, $filetype, $ignore);
}

/**
 * Custom function to save the layout changes to appropriate config variables
 *
 * Use: Drupal\omega\Layout\OmegaLayout::saveLayoutData();
 *
 * @deprecated
 * @param $layout
 * @param $layout_id
 * @param $theme
 * @param bool $generate
 * @return bool
 */
function _omega_save_database_layout($layout, $layout_id, $theme, $generate = FALSE) {
  return OmegaLayout::saveLayoutData($layout, $layout_id, $theme, $generate);
}

/**
 * Use: Drupal\omega\Layout\OmegaLayout::compileLayout();
 *
 * @deprecated
 * @param $layout
 * @param $layout_id
 * @param $theme
 */
function _omega_compile_layout($layout, $layout_id, $theme) {
  OmegaLayout::compileLayout($layout, $layout_id, $theme);
}

/**
 * Custom function to generate layout CSS from SCSS
 * Currently performs the following operations:
 *  - Takes SCSS generated from _omega_compile_layout_sass and returns CSS
 * Use: Drupal\omega\Layout\OmegaStyle::compileCss($scss, $options);
 *
 * @deprecated
 */

function _omega_compile_css($scss, $theme, $options) {
  return OmegaStyle::compileCss($scss, $options);
}

/**
 * Function to be used by _omega_compile_css to gather the appropriate
 * import paths to use for generating CSS in a theme.
 *
 * Use: Drupal\omega\Layout\OmegaStyle::getImportPaths($theme);
 *
 * @deprecated
 * @param $compiler
 * @return array $scss_paths
 */
function _omega_add_scss_import_paths($theme) {
  return OmegaStyle::getImportPaths($theme);
}

/**
 * Custom function to generate layout SCSS from layout variables
 * Currently performs the following operations:
 *  - Cycles a given layout for breakpoints
 *  - Cycles a breakpoint for region groups
 *  - Cycles a region group for regions
 *  - Cycles a region for various settings to apply to the region
 *  - Returns SCSS designed to be passed to _omega_compile_css
 * Use: Drupal\omega\Layout\OmegaLayout::compileLayoutScss();
 *
 * @deprecated
 *
 * @param $layout
 * @param $layoutName
 * @param string $theme
 * @param $options
 * @return string
 */
function _omega_compile_layout_sass($layout, $layoutName, $theme = 'omega', $options) {
  return OmegaLayout::compileLayoutScss($layout, $layoutName, $theme, $options);
}

/**
 * Function to take SCSS/CSS data and save to appropriate files
 *
 * Use: Drupal\omega\Layout\OmegaLayout::saveLayoutFiles();
 *
 * @deprecated
 * @param $scss
 * @param $theme
 * @param $layout_id
 * @param $options
 */

function _omega_save_layout_files($scss, $theme, $layout_id, $options) {
  OmegaLayout::saveLayoutFiles($scss, $theme, $layout_id, $options);
}

/**
 * Helper function to calculate the new width/push/pull/prefix/suffix of a primary region
 * $main is the primary region for a group which will actually be the one we are adjusting
 * $empty_regions is an array of region data for regions that would be empty
 * $cols is the total number of columns assigned using row(); for the region group
 *
 * Use: Drupal\omega\Layout\OmegaLayout::layoutAdjust();
 *
 * @deprecated
 * @return array()
 * array contains width, push, pull, prefix and suffix of adjusted primary region
 */
function _omega_layout_generation_adjust($main, $empty_regions = array(), $cols) {
  return OmegaLayout::layoutAdjust($main, $empty_regions, $cols);
}

/**
 * Use: Drupal\omega\Layout\OmegaStyle::getScssOptions();
 *
 * @deprecated
 * @param $layouts
 * @return
 */
function _omega_layout_select_options($layouts) {
  return OmegaLayout::getAvailableLayoutFormOptions($layouts);
}

/**
 * Use: Drupal\omega\Layout\OmegaStyle::getScssOptions();
 *
 * @deprecated
 * @param $relativeSource
 * @param $file
 * @param $theme
 * @return array
 */
function _omega_return_scss_options($relativeSource, $file, $theme) {
  return OmegaStyle::getScssOptions($relativeSource, $file, $theme);
}
