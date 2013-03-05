<?php

/**
 * Implements hook_preprocess_comment_wrapper().
 */
function omega_preprocess_comment_wrapper(&$variables) {
  // Add a HTML id so it can be used as an anchor target.
  $variables['attributes_array']['id'] = 'comments';
  $variables['attributes_array']['class'][] = 'comments';
}
