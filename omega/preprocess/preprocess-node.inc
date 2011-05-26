<?php

/**
 * @file
 * Container for hook_preprocess_node().
 */

/**
 * Implements hook_preprocess_node().
 * 
 * @see
 *   alpha_preprocess().
 */
function omega_alpha_preprocess_node(&$vars) {
  // Prepare the arrays to handle the classes and ids for the node container.
  $vars['attributes_array']['id'] = drupal_html_id('node-' . $vars['type'] . '-' . $vars['nid']);  
  $vars['attributes_array']['class'] = &$vars['classes_array'];
  
  // Add a class to allow styling based on publish status.
  if ($vars['status']) {
    $vars['attributes_array']['class'][] = 'node-published';
  }
  
  // Add a class to allow styling based on promotion.
  if (!$vars['promote']) {
    $vars['attributes_array']['class'][] = 'node-not-promoted';
  }
  
  // Add a class to allow styling based on sticky status.
  if (!$vars['sticky']) {
    $vars['attributes_array']['class'][] = 'node-not-sticky';
  }
  
  // Add a class to allow styling of nodes being viewed by the author of the node in question.
  if ($vars['uid'] == $vars['user']->uid) {
    $vars['attributes_array']['class'][] = 'self-posted';
  }
  
  // Add a class to allow styling based on the node author.
  $vars['attributes_array']['class'][] = drupal_html_class('author-' . $vars['node']->name);
  
  // Add a class to allow styling for zebra striping.
  $vars['attributes_array']['class'][] = drupal_html_class($vars['zebra']);
  
  // Add a class to make the node container self clearing.
  $vars['attributes_array']['class'][] = 'clearfix';
  
  $vars['content_attributes_array']['class'] = array('content', 'clearfix');
  
  // Adding a class to the title attributes
  $vars['title_attributes_array']['class'] = 'node-title';
}