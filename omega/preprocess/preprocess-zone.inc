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
  
  if  (alpha_zone_get_setting('equal_height', $vars['elements']['#zone'], FALSE)) {
    drupal_add_js(drupal_get_path('theme', 'omega') . '/js/jquery.equalheight.js', array('group' => JS_THEME, 'weight' => -10));
    
    $vars['attributes_array']['class'][] = 'equal-height-container';
  }
}