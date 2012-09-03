<?php

/**
 * @file
 * Main extension file for the 'development' extension.
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
  if (!isset($variables['theme_hook_suggestion']) && !empty($variables['page']['#omega_layout'])) {
    $registry = theme_get_registry();
    $suggestion = 'page__' . $variables['page']['#omega_layout'] . '_layout';

    // Check if the provided layout is valid.
    if (isset($registry[$suggestion]) && isset($registry[$suggestion]['layout'])) {
      $variables['theme_hook_suggestion'] = $suggestion;

      if (isset($registry[$suggestion]['layout']['attached'])) {
        // Load all the assets for the active layout.
        drupal_process_attached(array('#attached' => $registry[$suggestion]['layout']['attached']));
      }
    }
  }
}