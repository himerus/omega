<?php

/**
 * Implements hook_preprocess_views_view().
 */
function omega_preprocess_views_view_unformatted(&$variables) {
  // For some reason views puts row classes into the classes array. Instead of
  // classes arrays we should always use proper attributes arrays and never
  // abuse the default versions of those for row classes. Instead, we should use
  // a custom variable for that, which is exactly what we convert it to here.
  foreach ($variables['classes_array'] as $delta => $item) {
    if (!empty($item)) {
      $variables['row_attributes_array'][$delta]['class'] = array($item);
    }
  }

  // Empty the classes array so we don't end up with bogus classes on the
  // wrapper if someone decides to use it like that.
  $variables['classes_array'] = array();
}
