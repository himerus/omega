<?php

/**
 * @file
 * Main extension file for the 'development' extension.
 */

/**
 * Implements hook_extension_EXTENSION_preproces_html().
 */
function omega_extension_development_preprocess_html(&$variables) {
  $path = drupal_get_path('theme', 'omega');
  drupal_add_css($path . '/css/omega.development.css', array('group' => CSS_THEME, 'weight' => -10));

  if (theme_get_setting('omega_dummy_blocks')) {
    $variables['attributes_array']['class'][] = 'omega-dummy-blocks';
  }

  if (!module_exists('overlay') || overlay_get_mode() == 'parent') {
    if (theme_get_setting('omega_browser_width_indicator')) {
      $variables['attributes_array']['class'][] = 'omega-browser-width-indicator';
      drupal_add_js($path . '/js/omega.development.js', array('group' => JS_THEME, 'weight' => -10));
    }
  }
}