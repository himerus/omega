<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_html(&$vars) {
  global $theme_key;
  
  $settings = alpha_settings($theme_key);
  $access = alpha_debug_access($vars['user'], $settings['debug']['roles']);
  
  foreach ($settings['css'] as $item) {
    drupal_add_css($item['path'], array('media' => $item['media']));
  }
  
  if(($settings['debug']['grid'] || $settings['debug']['block']) && $access) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/debug.css', array('weight' => 1));

    if ($settings['debug']['grid']) {
      $vars['classes_array'][] = 'alpha-grid-debug';
    }
    
    if ($settings['debug']['block']) {
      $vars['classes_array'][] = 'alpha-region-debug';
    }
  }
}