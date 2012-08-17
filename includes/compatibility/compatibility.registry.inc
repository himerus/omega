<?php

/**
 * @file
 * Main extension file for the 'compatibility' extension.
 */

/**
 * Implements hook_extension_EXTENSION_registry_alter().
 */
function omega_extension_compatibility_theme_registry_alter(&$registry) {
  $registry['html']['includes'][] = drupal_get_path('theme', 'omega') . '/includes/compatibility/compatibility.inc';
  $registry['html']['preprocess functions'][] = 'omega_extension_compatibility_preprocess_html';
}