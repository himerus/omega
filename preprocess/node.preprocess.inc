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
  dpm($variables);
  $node_type = $variables['type'];
  $view_mode = $variables['view_mode'];
  // Add article ARIA role.
  $variables['attributes_array']['role'] = 'article';
  // Add BEM element classes.
  $variables['title_attributes_array']['class'][] = 'node__title';
  $variables['content_attributes_array']['class'][] = 'node__content';
  $variables['content']['links']['#attributes']['class'][] = 'node__links';
  // Change modifier classes to use BEM syntax.
  $variables['classes_array'] = str_replace('node-' . drupal_clean_css_identifier($node_type), 'node--' . drupal_clean_css_identifier($node_type), $variables['classes_array']);
  $variables['classes_array'] = str_replace('node-promoted', 'node--promoted', $variables['classes_array']);
  $variables['classes_array'] = str_replace('node-sticky', 'node--sticky', $variables['classes_array']);
  // Add modifier classes for view mode.
  $variables['classes_array'][] = 'node--' . drupal_clean_css_identifier($view_mode);
  $variables['classes_array'][] = 'node--' . drupal_clean_css_identifier($node_type) . '-' . drupal_clean_css_identifier($view_mode);
}
