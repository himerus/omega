<?php

/**
 * Implements hook_preprocess_region().
 */
function omega_preprocess_region(&$variables) {
  $variables['debug'] = FALSE;

  // Sidebar regions common template suggestion for all sidebars.
  if (strpos($variables['region'], 'sidebar') === 0) {
    // Allow a region-specific template to override region--sidebar.
    array_unshift($variables['theme_hook_suggestions'], 'region__sidebar');
  }
  // Use a template with no wrapper for the content region.
  elseif ($variables['region'] == 'content') {
    // Allow a region-specific template to override region--minimal.
    array_unshift($variables['theme_hook_suggestions'], 'region__minimal');
  }
}
