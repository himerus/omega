<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_block(&$vars) {
  $vars['content_attributes_array']['class'] = array('content', 'clearfix');
  
  $vars['attributes_array']['class'] = $vars['classes_array'];  
  $vars['attributes_array']['class'][] = drupal_html_class('block-' . $vars['block']->delta);
  
  $vars['attributes_array']['class'][] = $vars['block_html_id'];
  
  if ($vars['block']->module == 'alpha') {
    if($vars['block']->delta == 'grid-indicator') {
      $vars['title_attributes_array']['class'][] = 'element-invisible';
    }
    else if (isset($vars['block']->debug)) {
      $vars['attributes_array']['class'][] = 'alpha-debug-block';
    }
  } 
}