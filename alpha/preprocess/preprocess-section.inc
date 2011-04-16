<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_section(&$vars) {
  $vars['section'] = $vars['elements']['#section'];

  $vars['theme_hook_suggestions'][] = 'section__' . $vars['section'];
  
  $vars['content'] = $vars['elements']['#children'];
  $vars['sid'] = drupal_html_class('section-' . $vars['section']);
  $vars['classes_array'] = array('section', $vars['sid']);  
}