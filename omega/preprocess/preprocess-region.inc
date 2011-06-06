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
    
    if ($data['equal_height']) {
      drupal_add_js(drupal_get_path('theme', 'omega') . '/js/jquery.equalheight.js', array('group' => JS_THEME, 'weight' => 100));
      
      $vars['content_attributes_array']['class'][] = 'equal-height-container';
    }
      
    if ($zone = alpha_zones($data['zone'])) {
      if ($zone['equal_height']) {
        $vars['attributes_array']['class'][] = 'equal-height-element';
      }
    }
  }
}