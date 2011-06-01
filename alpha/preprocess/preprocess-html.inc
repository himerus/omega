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
  $vars['attributes_array']['class'] = &$vars['classes_array'];
  
  // Add a CSS class based on the current page context.
  if (!drupal_is_front_page()) {
    $context = explode('/', drupal_get_path_alias());
    $context = reset($context);
    
    if (!empty($context)) {
      $vars['attributes_array']['class'][] = drupal_html_class('context-' . $context);
    }
  }
  
  if (($settings['debug']['grid'] || $settings['debug']['block']) && $settings['debug']['access']) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/alpha-debug.css', array('weight' => -5));

    if ($settings['debug']['grid']) {
      $vars['attributes_array']['class'][] = 'alpha-grid-debug';
    }
    
    if ($settings['debug']['block']) {
      $vars['attributes_array']['class'][] = 'alpha-region-debug';
    }
  }  
}