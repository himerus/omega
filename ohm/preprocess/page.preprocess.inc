<?php

/**
 * Implements hook_preprocess_TEMPLATE().
 */
function ohm_preprocess_page(&$variables) {
  // Ohm should only be enabled as an example theme, and not used in production.
  drupal_set_message(t('Ohm is a demonstration subtheme and will therefore be constantly evolving with latest best practices. Explore, break it and learn but don\'t use it in production directly, or as a base theme.'), 'warning');
}
