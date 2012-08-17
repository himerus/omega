<?php

/**
 * @file
 * Main extension file for the 'development' extension.
 */

/**
 * Implements hook_extension_EXTENSION_process_page().
 */
function omega_extension_layouts_process_layout(&$variables) {
  if (!isset($variables['theme_hook_suggestion']) && !empty($variables['page']['#omega_layout'])) {
    $registry = theme_get_registry();
    $suggestion = 'page__' . $variables['page']['#omega_layout'] . '_layout';

    // Check if the provided layout is valid.
    if (isset($registry[$suggestion]) && isset($registry[$suggestion]['layout']) && $registry[$suggestion]['layout']['supported']) {
      $variables['theme_hook_suggestion'] = $suggestion;

      // Load all the assets for the active layout.
      drupal_process_attached(array('#attached' => $registry[$suggestion]['layout']['attached']));

      foreach (array_filter($registry[$suggestion]['layout']['includes']) as $file) {
        // Load all include files.
        require_once $file;
      }

      foreach (array_filter($registry[$suggestion]['layout']['hooks']) as $hook) {
        // Invoke all defined callbacks.
        $hook($variables, $variables['page']['#omega_layout'], $registry[$suggestion]['layout']);
      }
    }
  }
}