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
  
  if ($data['equal_height']) {
    drupal_add_js(drupal_get_path('theme', 'omega') . '/js/jquery.equalheights.js', array('group' => JS_THEME));
    
    $vars['content_attributes_array']['class'][] = 'equal-height-container';
  }
}