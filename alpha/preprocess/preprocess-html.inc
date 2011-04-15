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

  if($settings['responsive']['enabled'] && $settings['viewport']['enabled']) {
    $meta = array(
  	  '#tag' => 'meta',
      '#attributes' => array(
        'name' => 'viewport',
        'content' => 'width=device-width; initial-scale=' . $settings['viewport']['initial'] . '; maximum-scale=' . $settings['viewport']['max'] . '; minimum-scale=' . $settings['viewport']['min'] . '; user-scalable=' . $settings['viewport']['user'] . ';',
      ),
    );

    drupal_add_html_head($meta, 'alpha-viewport');
  }  
}