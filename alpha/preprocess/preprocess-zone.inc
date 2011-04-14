<?php

function alpha_alpha_preprocess_zone(&$vars) {
  alpha_include_grid($vars['elements']['#page']['#alpha']['grid'], $vars['elements']['#zone']['columns']);

  $vars['zone'] = $vars['elements']['#zone']['zone'];  
  
  $vars['theme_hook_suggestions'][] = 'zone';
  $vars['theme_hook_suggestions'][] = 'zone__' . $vars['zone'];
  
  $vars['columns'] = $vars['elements']['#zone']['columns'];
  $vars['wrapper'] = $vars['elements']['#zone']['wrapper'];
  $vars['content'] = $vars['elements']['#children'];
  $vars['zid'] = drupal_html_class('zone-' . $vars['zone']);
  $vars['classes_array'] = array('zone', $vars['zid'], 'container-' . $vars['columns'], 'clearfix');
}