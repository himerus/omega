<?php

/*
 * @todo
 */
function alpha_alpha_process_zone(&$vars) {
  $vars['wrapper_attributes'] = $vars['wrapper'] && isset($vars['wrapper_attributes_array']) ? drupal_attributes($vars['wrapper_attributes_array']) : '';
}