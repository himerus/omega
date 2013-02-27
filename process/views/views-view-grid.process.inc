<?php

/**
 * Implements hook_process_views_view_grid().
 *
 * @see hook_preprocess_views_view_grid()
 */
function omega_process_views_view_grid(&$variables) {
  foreach ($variables['rows'] as $delta => $row) {
    // Convert the previously set row attributes arrays into strings.
    $variables['row_attributes'][$delta] = !empty($variables['row_attributes_array'][$delta]) ? drupal_attributes($variables['row_attributes_array'][$delta]) : '';

    foreach ($columns as $column => $content) {
      // Convert the previously set column attributes arrays into strings.
      $variables['column_attributes'][$delta][$column] = !empty($variables['column_attributes_array'][$delta][$column]) ? drupal_attributes($variables['columns_attributes_array'][$delta][$column]) : '';
    }
  }
}
