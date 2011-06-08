<?php

/**
 * @file
 * Container for hook_preprocess_zone().
 */

/**
 * Implements hook_preprocess_zone().
 * 
 * @see
 *   alpha_preprocess().
 */
function omega_alpha_preprocess_zone(&$vars) {
  $data = $vars['elements']['#data'];
  
  if (alpha_library_active('omega_formalize')) {
    $zone = alpha_zones($data['zone']);

    if ($data['equal_height']) {
      $vars['content_attributes_array']['class'][] = 'equal-height-container';
    }
  }
}