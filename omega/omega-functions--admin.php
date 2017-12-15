<?php

/**
 * @file
 * This file is to be removed prior to a stable 8.x release of Omega Five.
 */

/* @codingStandardsIgnoreStart */

use Drupal\omega\Layout\OmegaLayout;
use Drupal\omega\Style\OmegaStyle;

/*
 * Require the phpsass library manually.
 * @todo: Avoid using require_once for phpsass.
 * @todo: Replace phpsass with either composer version or leafo/scssphp.
 */
$omegaRoot = DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega');
require_once $omegaRoot . '/src/phpsass/SassParser.php';
require_once $omegaRoot . '/src/phpsass/SassFile.php';

/**
 * Function to take an array of style variables and create SCSS.
 *
 * Use: Drupal\omega\Layout\OmegaStyle::scssVariablesUpdate();
 *
 * @deprecated
 */
function _omega_update_style_scss($styles, $theme) {
  OmegaStyle::scssVariablesUpdate($styles, $theme);
}

/**
 * Use: Drupal\omega\Layout\OmegaStyle::themeStylesUpdate().
 *
 * @deprecated
 * @todo: Update this to leafo/scssphp
 */
function scssDirectoryScan($source, $theme, $filetype = 'scss', $ignore = '/^(\.(\.)?|CVS|_omega-style-vars\.scss|layout|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
  OmegaStyle::themeStylesUpdate($source, $theme, $filetype, $ignore);
}

/**
 * Custom function to save the layout changes to appropriate config variables.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::saveLayoutData().
 *
 * @deprecated
 */
function _omega_save_database_layout($layout, $layout_id, $theme, $generate = FALSE) {
  return OmegaLayout::saveLayoutData($layout, $layout_id, $theme, $generate);
}

/**
 * Use: Drupal\omega\Layout\OmegaLayout::compileLayout().
 *
 * @deprecated
 */
function _omega_compile_layout($layout, $layout_id, $theme) {
  OmegaLayout::compileLayout($layout, $layout_id, $theme);
}

/**
 * Custom function to generate layout CSS from SCSS.
 *
 * Currently performs the following operations:
 *  - Takes SCSS generated from _omega_compile_layout_sass and returns CSS.
 *
 * Use: Drupal\omega\Layout\OmegaStyle::compileCss($scss, $options).
 *
 * @todo: This can't currently be included via class because of limitations with phpsass.
 */
function _omega_compile_css($scss, $options) {
  // Using richthegeek/phpsass.
  $parser = new SassParser($options);
  // Create CSS from SCSS.
  $css = $parser->toCss($scss);

  // Attempting use of leafo/scssphp
  // $compiler = new Compiler();
  // $compiler->setImportPaths(_omega_add_scss_import_paths($theme));
  // $compiler->setFormatter('Leafo\ScssPhp\Formatter\Expanded');
  // $css = $compiler->compile($scss);
  return $css;
}

/**
 * Function to gather the appropriate import paths to use for generating CSS.
 *
 * Use: Drupal\omega\Layout\OmegaStyle::getImportPaths($theme);
 *
 * @deprecated
 */
function _omega_add_scss_import_paths($theme) {
  return OmegaStyle::getImportPaths($theme);
}

/**
 * Custom function to generate layout SCSS from layout variables.
 *
 * Currently performs the following operations:
 *  - Cycles a given layout for breakpoints
 *  - Cycles a breakpoint for region groups
 *  - Cycles a region group for regions
 *  - Cycles a region for various settings to apply to the region
 *  - Returns SCSS designed to be passed to _omega_compile_css
 * Use: Drupal\omega\Layout\OmegaLayout::compileLayoutScss().
 *
 * @deprecated
 */
function _omega_compile_layout_sass($layout, $layoutName, $theme = 'omega', $options) {
  return OmegaLayout::compileLayoutScss($layout, $layoutName, $options, $theme);
}

/**
 * Function to take SCSS/CSS data and save to appropriate files.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::saveLayoutFiles();
 *
 * @deprecated
 */
function _omega_save_layout_files($scss, $theme, $layout_id, $options) {
  OmegaLayout::saveLayoutFiles($scss, $theme, $layout_id, $options);
}

/**
 * Helper function to calculate the new width/push/pull/prefix/suffix.
 *
 * Use: Drupal\omega\Layout\OmegaLayout::layoutAdjust();
 *
 * @deprecated
 */
function _omega_layout_generation_adjust($main, $empty_regions = [], $cols) {
  return OmegaLayout::layoutAdjust($main, $empty_regions, $cols);
}

/**
 * Use: Drupal\omega\Layout\OmegaStyle::getScssOptions().
 *
 * @deprecated
 */
function _omega_layout_select_options($layouts) {
  return OmegaLayout::getAvailableLayoutFormOptions($layouts);
}

/**
 * Use: Drupal\omega\Layout\OmegaStyle::getScssOptions().
 *
 * @deprecated
 */
function _omega_return_scss_options($relativeSource, $file, $theme) {
  return OmegaStyle::getScssOptions($relativeSource, $file, $theme);
}
/* @codingStandardsIgnoreEnd */
