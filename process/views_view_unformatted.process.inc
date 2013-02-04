<?php

/**
 * Implements hook_preprocess_views_view().
 *
 * @see omega_preprocess_views_view_unformatted()
 */
function omega_process_views_view_unformatted(&$variables) {
  foreach ($variables['row_attributes_array'] as $delta => $item) {
    // Convert the previously set row attributes arrays into strings.
    $variables['row_attributes'][$delta] = !empty($variables['row_attributes_array'][$delta]) ? drupal_attributes($variables['row_attributes_array'][$delta]) : '';
  }
}
