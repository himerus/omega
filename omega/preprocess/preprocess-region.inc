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
    
    if (alpha_region_get_setting('equal_height', $vars['elements']['#region'], FALSE)) {
      drupal_add_js(drupal_get_path('theme', 'omega') . '/js/jquery.equalheight.js', array('group' => JS_THEME, 'weight' => -10));
      
      $vars['content_attributes_array']['class'][] = 'equal-height-container';
    }
      
    if (alpha_zone_get_setting('equal_height', $data['zone'], FALSE)) {
      $vars['attributes_array']['class'][] = 'equal-height-element';
    }
  }
}