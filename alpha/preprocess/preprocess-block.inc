<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_block(&$vars) {
  $vars['content_attributes_array']['class'] = array('content', 'clearfix');
  
  $vars['attributes_array']['class'] = $vars['classes_array'];  
  $vars['attributes_array']['class'][] = drupal_html_class('block-' . $vars['block']->delta);
  
  if ($vars['block']->module == 'alpha') {
    switch ($vars['block']->delta) {
      case 'debug':
        $vars['attributes_array']['id'] = $vars['block_html_id'] = drupal_html_id('block-' . $vars['block']->module . '-' . $vars['block']->delta . '-' . $vars['block']->region);
        break;
       
      case 'grid':
        $vars['title_attributes_array']['class'][] = 'element-invisible';
        break;
    }
  }
  
  $vars['attributes_array']['class'][] = $vars['block_html_id'];
}