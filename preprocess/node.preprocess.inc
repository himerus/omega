<?php

 /**
 * Implements hook_preprocess_node().
 *
 * Copied from Sam Richard's (Snugug) fabulous Aurora Base Theme.
 *
 * Backports the following changes made to Drupal 8:
 * - #1077602: Convert node.tpl.php to HTML5.
 */
function omega_preprocess_node(&$variables) {
  // Add article ARIA role.
  $variables['attributes_array']['role'] = 'article';
  $variables['content_attributes_array']['class'][] = 'content';
}
