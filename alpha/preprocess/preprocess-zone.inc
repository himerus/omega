<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_zone(&$vars) {
  $settings = $vars['elements']['#page']['#alpha'];
  $zone = $vars['elements']['#zone'];
  
  alpha_include_grid($settings['grid'], $zone['columns']);
  
  if($settings['debug']['grid'] && alpha_debug_access($vars['user'], $settings['debug']['roles'])) {
    alpha_debug_grid($settings, $zone['columns']);
  } 
  
  $vars['zone'] = $zone['zone'];  
  
  $vars['theme_hook_suggestions'][] = 'zone__' . $vars['zone'];
  
  $vars['columns'] = $zone['columns'];
  $vars['wrapper'] = $zone['wrapper'];
  $vars['content'] = $vars['elements']['#children'];
  $vars['zid'] = drupal_html_class('zone-' . $vars['zone']);
  $vars['type'] = $zone['type'];
  $vars['classes_array'] = array('zone', 'zone-' . $zone['type'], $vars['zid'], 'container-' . $vars['columns'], 'clearfix');
  
  if (!empty($zone['css'])) {
    $vars['classes_array'] = array_merge($vars['classes_array'], array_map('drupal_html_class', explode(' ', $zone['css'])));
  }
}