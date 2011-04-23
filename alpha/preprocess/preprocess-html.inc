<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_html(&$vars) {
  global $theme_key;

  $vars['attributes_array']['class'] = $vars['classes_array'];
  
  $settings = alpha_settings($theme_key);
  $css = alpha_css($theme_key);
  $grid = alpha_grids($theme_key, $settings['grid']);
  
  if ($settings['responsive']['enabled'] && $settings['debug']['grid'] && $grid['type'] == 'fixed' && $settings['debug']['access']) {
    if (empty($vars['page']['page_bottom'])) {
      $vars['page']['page_bottom']['#region'] = 'page_bottom';
      $vars['page']['page_bottom']['#theme_wrappers'] = array('region');
      $vars['page']['page_bottom']['#sorted'] = FALSE;
    }
    
    $vars['page']['page_bottom']['alpha_grid'] = alpha_grid_block($grid['name']);
  }

  foreach ($settings['css'] as $item => $enabled) {
    if (isset($css[$item]) && $enabled) {
      $weight = $css[$item]['weight'] >= 0 ? $css[$item]['weight'] : $css[$item]['weight'] - 3;
      
      drupal_add_css($css[$item]['path'], array('media' => $css[$item]['media'], 'weight' => $weight));
    }
  }
  
  if(($settings['debug']['grid'] || $settings['debug']['block']) && $settings['debug']['access']) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/debug.css', array('weight' => -5));

    if ($settings['debug']['grid']) {
      $vars['attributes_array']['class'][] = 'alpha-grid-debug';
    }
    
    if ($settings['debug']['block']) {
      $vars['attributes_array']['class'][] = 'alpha-region-debug';
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