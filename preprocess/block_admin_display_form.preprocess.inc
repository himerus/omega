<?php

/**
 * Implements hook_admin_display_form_preprocess().
 */
function omega_block_admin_display_form_preprocess(&$variables) {
  // Add table javascript.
  drupal_add_js('misc/tableheader.js');
  drupal_add_js(drupal_get_path('module', 'block') . '/block.js');
  foreach ($variables['block_regions'] as $region => $title) {
    drupal_add_tabledrag('blocks', 'match', 'sibling', 'block-region-select', 'block-region-' . $region, NULL, FALSE);
    drupal_add_tabledrag('blocks', 'order', 'sibling', 'block-weight', 'block-weight-' . $region);
  }
}
