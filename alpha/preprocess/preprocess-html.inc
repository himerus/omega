<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_html(&$vars) {
  global $theme_key;
  
  $settings = alpha_settings($theme_key);
  
  foreach ($settings['css'] as $item) {
    drupal_add_css($item['path'], array('media' => $item['media']));
  }
  
  if($settings['debug']['grid'] && alpha_debug_access($vars['user'], $settings['debug']['roles'])) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/debug.css');
    
    $vars['classes_array'][] = 'alpha-grid-debug';
  }  
}