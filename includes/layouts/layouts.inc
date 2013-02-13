<?php

/**
 * @file
 * Main extension file for the 'layouts' extension.
 */

/**
 * Implements hook_extension_EXTENSION_registry_alter().
 */
function omega_extension_layouts_theme_registry_alter(&$registry) {
  $registry['page']['includes'][] = drupal_get_path('theme', 'omega') . '/includes/layouts/layouts.inc';
  $registry['page']['process functions'][] = 'omega_extension_layouts_process_layout';
}

/**
 * Implements hook_extension_EXTENSION_process_page().
 */
function omega_extension_layouts_process_layout(&$variables) {
  if (empty($variables['theme_hook_suggestion']) && $layout = omega_layout()) {
    $variables['theme_hook_suggestion'] = 'omega';
    $variables['omega_layout'] = $layout;
  }
}

/**
 * Theme callback for rendering an Omega layout.
 */
function theme_omega_layout(&$variables) {
  $layout = $variables['omega_layout'];

  // Clean up the theme hook suggestion so we don't end up in an infinite loop.
  unset($variables['theme_hook_suggestion'], $variables['theme_hook_suggestions']);

  // Load all the CSS and JS files provided by the layout.
  drupal_process_attached(array('#attached' => $layout['attached']));

  return theme($layout['name'] . '_layout', $variables);
}
