<?php

/**
 * @file
 * Container for hook_preprocess_block().
 */

/**
 * Implements hook_preprocess_block().
 * 
 * @see
 *   alpha_preprocess().
 */
function alpha_alpha_preprocess_block(&$vars) {
  $vars['content_attributes_array']['class'] = array('content', 'clearfix');  
  $vars['attributes_array']['class'] = $vars['classes_array'];  
  $vars['attributes_array']['class'][] = drupal_html_class('block-' . $vars['block']->delta);  
  $vars['attributes_array']['class'][] = $vars['block_html_id'];

  if ($vars['block']->module == 'alpha-indicator') {
    $vars['title_attributes_array']['class'][] = 'element-invisible';
    $vars['content_attributes_array']['class'][] = 'element-invisible';
  }
}