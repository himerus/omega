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
function omega_alpha_preprocess_block(&$vars) {
  $vars['attributes_array']['class'] = &$vars['classes_array'];  
  
  // Adding a class to the title attributes
  $vars['title_attributes_array']['class'][] = 'block-title';
  
  if (!empty($vars['elements']['#first'])) {
    $vars['attributes_array']['class'][] = 'first';
  }

  if (!empty($vars['elements']['#last'])) {
    $vars['attributes_array']['class'][] = 'last';
  }  

  // Add odd/even zebra classes into the array of classes
  $vars['attributes_array']['class'][] = $vars['block_zebra'];
  
  if(empty($vars['block']->subject) && (string) $vars['block']->subject != '0') {
    // Add a class to provide CSS for blocks without titles.
    $vars['attributes_array']['class'][] = 'block-without-title';
  }
  
  if ($vars['block']->module != 'alpha-debug' && isset($vars['block']->region) && $region = alpha_regions($vars['block']->region)) {
    if ($region['equal_height']) {
      $vars['attributes_array']['class'][] = 'equal-height-element';
    }
  }
}