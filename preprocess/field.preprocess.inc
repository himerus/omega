<?php

/**
 * Implements hook_preprocess_block().
 */
function omega_preprocess_field(&$variables) {
  // Change modifier classes to use BEM syntax.
  $variables['classes_array'] = str_replace('field-', 'field--', $variables['classes_array']);
}
