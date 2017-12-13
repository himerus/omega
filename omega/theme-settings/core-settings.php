<?php

/**
 * @file
 * Implements custom theme settings adjustments for Omega Five.
 */

// Move the default settings to our custom vertical tabs for theme settings.
$form['core'] = [
  '#type' => 'vertical_tabs',
  '#attributes' => ['class' => ['entity-meta']],
  '#weight' => -899,
];

$form['theme_settings']['#group'] = 'core';
$form['logo']['#group'] = 'core';
$form['favicon']['#group'] = 'core';

$form['theme_settings']['#open'] = FALSE;
$form['logo']['#open'] = FALSE;
$form['favicon']['#open'] = FALSE;

// Disable the default theme settings for both Omega
// and a theme where "force export" is turned on.
if ($theme == 'omega' || $force_theme_export) {
  $form['core']['#access'] = FALSE;
  $form['theme_settings']['#access'] = FALSE;
  $form['logo']['#access'] = FALSE;
  $form['favicon']['#access'] = FALSE;
}
