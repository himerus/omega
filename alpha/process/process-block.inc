<?php

/*
 * @todo
 */
function alpha_alpha_process_block(&$vars) {
  if($vars['block']->module == 'alpha' && $vars['block']->delta == 'branding') {
    $vars['site_name_attributes'] = isset($vars['site_name_attributes_array']) ? drupal_attributes($vars['site_name_attributes_array']) : '';
    $vars['site_slogan_attributes'] = isset($vars['site_slogan_attributes_array']) ? drupal_attributes($vars['site_slogan_attributes_array']) : '';
  }
}