<?php

/**
 * @file
 * Container for hook_preprocess_html().
 */

/**
 * Implements hook_preprocess_html().
 * 
 * @see
 *   alpha_preprocess().
 */
function alpha_alpha_preprocess_html(&$vars) { 
  $settings = alpha_settings();
  $css = alpha_css();
  $libraries = alpha_libraries();
  
  $vars['attributes_array']['class'] = &$vars['classes_array'];
  
  foreach (array('two-sidebars', 'one-sidebar sidebar-first', 'one-sidebar sidebar-second', 'no-sidebars') as $exclude) {
    if ($index = array_search($exclude, $vars['attributes_array']['class'])) {      
      unset($vars['attributes_array']['class'][$index]);
    }
  }
  
  // Add a CSS class based on the current page context.
  if (!drupal_is_front_page()) {
    $context = explode('/', drupal_get_path_alias());
    $context = reset($context);
    
    if (!empty($context)) {
      $vars['attributes_array']['class'][] = drupal_html_class('context-' . $context);
    }
  }
  
  if (($settings['debug']['grid'] || $settings['debug']['block']) && $settings['debug']['access']) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/alpha-debug.css', array('group' => CSS_THEME, 'weight' => -5));   
    drupal_add_js(drupal_get_path('theme', 'alpha') . '/js/alpha-debug.js', array('group' => JS_THEME, 'weight' => -5));
    
    if ($settings['debug']['grid'] && $settings['debug']['grid_active']) {
      $vars['attributes_array']['class'][] = 'alpha-grid-debug';
    }
    
    if ($settings['debug']['block'] && $settings['debug']['block_active']) {
      $vars['attributes_array']['class'][] = 'alpha-region-debug';
    }
  }

  foreach (array_filter($settings['libraries']) as $item) {
    if (isset($libraries[$item])) {
      if (!empty($libraries[$item]['js'])) {
        foreach ($libraries[$item]['js'] as $include) {
          drupal_add_js($include['path'], $include['options']);
        }
      }

      if (!empty($libraries[$item]['css'])) {
        foreach ($libraries[$item]['css'] as $include) {
          drupal_add_css($include['path'], $include['options']);
        }
      }
    }
  }

  foreach (array_filter($settings['css']) as $item) {
    if (isset($css[$item])) {
      drupal_add_css($css[$item]['path'], $css[$item]['options']);
    }
  }

  if($settings['responsive'] && $settings['viewport']['enabled']) {
    $meta = array(
      '#tag' => 'meta',
      '#attributes' => array(
        'name' => 'viewport',
        'content' => 'width=device-width, initial-scale=' . $settings['viewport']['initial'] . ', maximum-scale=' . $settings['viewport']['max'] . ', minimum-scale=' . $settings['viewport']['min'] . ', user-scalable=' . ($settings['viewport']['user'] ? 'yes' : 'no'),
      ),
    );

    drupal_add_html_head($meta, 'alpha-viewport');
  }
}