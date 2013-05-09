<?php

/**
 * Implements hook_preprocess_TEMPLATE().
 */
function ohm_preprocess_region(&$variables) {
  if ($variables['region'] == 'highlighted') {
    $variables['attributes_array']['class'][] = 'l-constrained';
  }
}
