<?php

/**
 * @file
 * Container for hook_preprocess_region().
 */

/**
 * Implements hook_preprocess_region().
 * 
 * @see
 *   alpha_preprocess().
 */
function omega_alpha_preprocess_region(&$vars) {
  if (isset($vars['elements']['#data'])) {
    $data = $vars['elements']['#data'];    
    
    if (alpha_library_active('omega_formalize')) {
      $zone = alpha_zones($data['zone']);
      
      if ($data['equal_height']) {      
        $vars['content_attributes_array']['class'][] = 'equal-height-container';
      }

      if ($zone['equal_height']) {
        $vars['attributes_array']['class'][] = 'equal-height-element';
      }
    }
  }
}