<?php
/**
 * Implements hook_preprocess_taxonomy_term().
 */
function omega_preprocess_taxonomy_term(&$variables) {
  $variables['attributes_array']['class'] = array(
    'taxonomy-term--' . drupal_clean_css_identifier($variables['vocabulary_machine_name']),
    'taxonomy-term--' . drupal_clean_css_identifier($variables['view_mode']),
    'taxonomy-term--' . drupal_clean_css_identifier($variables['vocabulary_machine_name']) . '--' . drupal_clean_css_identifier($variables['view_mode']),
  );
  $variables['content_attributes_array']['class'][] = 'taxonomy-term__content';
}
