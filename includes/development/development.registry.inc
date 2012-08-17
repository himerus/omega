<?php

/**
 * @file
 * Main extension file for the 'development' extension.
 */

/**
 * Implements hook_extension_EXTENSION_registry_alter().
 */
function omega_extension_development_theme_registry_alter(&$registry) {
  if (theme_get_setting('omega_browser_width_indicator') || theme_get_setting('omega_dummy_blocks')) {
    $registry['html']['includes'][] = drupal_get_path('theme', 'omega') . '/includes/development/development.inc';
    $registry['html']['preprocess functions'][] = 'omega_extension_development_preprocess_html';
  }
}