<?php

/**
 * Implements hook_preprocess_html().
 */
function {{ THEME }}_preprocess_html(&$variables) {
  // Load the fallback.css for Internet Explorer 8 and lower.
  $path = drupal_get_path('theme', '{{ THEME }}');
  drupal_add_css("$path/css/{{ THEME }}.fallback.css", array(
    'browsers' => array(
      '!IE' => FALSE,
      'IE' => 'lte IE 8',
    ),
    'group' => CSS_THEME,
    'weight' => 1000,
  ));
}
