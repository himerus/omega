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
    $variables['theme_hook_suggestion'] = 'omega_layout_page';
  }
}

/**
 * Theme callback for rendering an Omega layout.
 */
function theme_omega_layout_page($variables) {
  if ($layout = omega_layout()) {
    // Clean up the theme hook suggestion so we don't end up in an infinite loop.
    unset($variables['theme_hook_suggestion'], $variables['theme_hook_suggestions']);
    return theme('omega_render_layout', array('type' => 'page', 'layout' => $layout, 'variables' => $variables));
  }
}

/**
 * Theme callback for rendering an Omega layout.
 */
function theme_omega_layout_renderer($variables) {
  return theme($variables['layout']['name'] . '_layout', $variables['variables']);
}
