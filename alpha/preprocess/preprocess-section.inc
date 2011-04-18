<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_section(&$vars) {
  $vars['section'] = $vars['elements']['#section'];

  $vars['theme_hook_suggestions'][] = 'section__' . $vars['section'];
  
  $vars['content'] = $vars['elements']['#children'];
  $vars['section_html_id'] = drupal_html_id('section-' . $vars['section']);
  $vars['classes_array'] = array('section', $vars['section_html_id']);  
  
  $vars['attributes_array']['class'] = $vars['classes_array'];
  $vars['attributes_array']['id'] = $vars['section_html_id'];
}