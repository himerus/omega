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
function alpha_alpha_preprocess_region(&$vars) {
  $vars['attributes_array']['id'] = drupal_html_id('region-' . $vars['region']);
  $vars['attributes_array']['class'] = &$vars['classes_array'];
  $vars['content_attributes_array']['class'][] = 'region-inner';
  $vars['content_attributes_array']['class'][] = $vars['attributes_array']['id'] . '-inner';
  
  if (isset($vars['elements']['#data'])) {
    $data = $vars['elements']['#data'];  
    $vars['columns'] = $data['columns'];

    foreach (array('prefix', 'suffix', 'push', 'pull') as $quality) {
      if (!empty($data[$quality])) {
        $vars[$quality] = $data[$quality];
        
        array_unshift($vars['attributes_array']['class'], $quality . '-' . $data[$quality]);
      }
    }
    
    array_unshift($vars['attributes_array']['class'], 'grid-' . $data['columns']);

    if (!empty($data['css'])) {
      $extra = array_map('drupal_html_class', explode(' ', $data['css']));
      
      foreach ($extra as $class) {
        $vars['attributes_array']['class'][] = $class;
      }
    }
  }
}