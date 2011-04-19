<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_block(&$vars) {
  // adding a class to the title attributes
  $vars['title_attributes_array']['class'][] = 'block-title';
  
  $vars['classes_array'][] = drupal_html_class('block-' . $vars['block']->delta);
  
  if(isset($vars['block']->subject) && strlen($vars['block']->subject) == 0) {
    // add a class to provide CSS for blocks without titles
    // in those cases, we want to chop out that lower border.
    $vars['classes_array'][] = 'block-without-title';
  }
  
  // add odd/even zebra classes into the array of classes
  $vars['classes_array'][] = $vars['block_zebra'];
  
  if($vars['block_id'] == 1) {
    $vars['classes_array'][] = 'first';
  }

  if (isset($vars['block']->last)) {
    $vars['classes_array'][] = 'last';
  }
  
  if ($vars['block']->module == 'alpha') {
    switch ($vars['block']->delta) {
      case 'debug':
        $vars['block_html_id'] = drupal_html_id('block-' . $vars['block']->module . '-' . $vars['block']->delta . '-' . $vars['block']->region);
        break;
       
      case 'grid':
        $vars['title_attributes_array']['class'][] = 'element-invisible';
        break;
    }
  }
  $vars['attributes_array']['class'] = $vars['classes_array'];
  $vars['attributes_array']['id'] = $vars['block_html_id'];
}