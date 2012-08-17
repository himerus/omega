<?php

/**
 * Implements hook_preprocess_region().
 */
function omega_preprocess_region(&$variables) {
  // Sidebar regions common template suggestion and class for all sidebars.
  if (strpos($variables['region'], 'sidebar_') === 0) {
    $variables['attributes_array']['class'][] = 'sidebar';

    // Allow a region-specific template to override region--sidebar.
    array_unshift($variables['theme_hook_suggestions'], 'region__sidebar');
  }
  // Use a template with no wrapper for the content region.
  elseif ($variables['region'] == 'content') {
    // Allow a region-specific template to override region--minimal.
    array_unshift($variables['theme_hook_suggestions'], 'region__minimal');
  }
}
