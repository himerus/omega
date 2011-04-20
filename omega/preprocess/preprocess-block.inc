<?php

/**
 * @todo
 */
function omega_alpha_preprocess_block(&$vars) {  
  // Adding a class to the title attributes
  $vars['title_attributes_array']['class'][] = 'block-title';
    
  if($vars['block_id'] == 1) {
    $vars['attributes_array']['class'][] = 'first';
  }

  if (isset($vars['block']->last)) {
    $vars['attributes_array']['class'][] = 'last';
  }  

  // Add odd/even zebra classes into the array of classes
  $vars['attributes_array']['class'][] = $vars['block_zebra'];
  
  if(empty($vars['block']->subject) && (string) $vars['block']->subject != '0') {
    // Add a class to provide CSS for blocks without titles.
    $vars['attributes_array']['class'][] = 'block-without-title';
  }
}