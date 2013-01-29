<?php

/**
 * @file
 * Main extension file for the 'development' extension.
 */

/**
 * Implements hook_extension_EXTENSION_registry_alter().
 */
function omega_extension_development_theme_registry_alter(&$registry) {
  if ($regions = omega_theme_get_setting('omega_demo_regions', TRUE)) {
    $registry['region']['includes'][] = drupal_get_path('theme', 'omega') . '/includes/development/development.inc';
    $registry['region']['preprocess functions'][] = 'omega_extension_development_preprocess_region';
  }

  if (omega_theme_get_setting('omega_browser_width_indicator', FALSE) || $regions) {
    $registry['html']['includes'][] = drupal_get_path('theme', 'omega') . '/includes/development/development.inc';
    $registry['html']['preprocess functions'][] = 'omega_extension_development_preprocess_html';
  }
}

/**
 * Implements hook_extension_EXTENSION_preproces_html().
 */
function omega_extension_development_preprocess_html(&$variables) {
  $path = drupal_get_path('theme', 'omega');
  drupal_add_css($path . '/css/omega.development.css', array('group' => CSS_THEME, 'weight' => -10, 'every_page' => TRUE));

  if (omega_theme_get_setting('omega_browser_width_indicator', FALSE)) {
    if (!module_exists('overlay') || (!$mode = overlay_get_mode()) || $mode == 'parent') {
      $variables['attributes_array']['class'][] = 'omega-browser-width-indicator';
      drupal_add_js($path . '/js/omega.indicator.js', array('group' => JS_THEME, 'weight' => -10, 'every_page' => TRUE));
    }
  }
}

/**
 * Implements hook_extension_EXTENSION_preproces_region().
 */
function omega_extension_development_preprocess_region(&$variables) {
  if ($variables['debug'] = !empty($variables['elements']['#debug'])) {
    $class = drupal_html_class('region--debug--' . $variables['region']);
    drupal_add_css(".$class:before { content: \"{$variables['elements']['#name']}\"; }", array('type' => 'inline'));

    $variables['attributes_array']['class'][] = 'region--debug';
    $variables['attributes_array']['class'][] = $class;
  }
}
