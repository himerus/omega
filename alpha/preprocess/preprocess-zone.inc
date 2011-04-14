<?php

function alpha_alpha_preprocess_zone(&$vars) {
  alpha_include_grid($vars['elements']['#page']['#alpha']['grid'], $vars['elements']['#zone']['columns']);

  $zone = $vars['elements']['#zone'];
  $vars['zone'] = $zone['zone'];  
  
  $vars['theme_hook_suggestions'][] = 'zone';
  $vars['theme_hook_suggestions'][] = 'zone__' . $vars['zone'];
  
  $vars['columns'] = $zone['columns'];
  $vars['wrapper'] = $zone['wrapper'];
  $vars['content'] = $vars['elements']['#children'];
  $vars['zid'] = drupal_html_class('zone-' . $vars['zone']);
  $vars['classes_array'] = array('zone', $vars['zid'], 'container-' . $vars['columns'], 'clearfix');
  
  if (!empty($zone['css'])) {
    $vars['classes_array'] = array_merge($vars['classes_array'], array_map('drupal_html_class', explode(' ', $zone['css'])));
  }
}