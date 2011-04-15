<?php

function alpha_alpha_preprocess_region(&$vars) {  
  $region = $vars['elements']['#region'];
  $zone = $vars['elements']['#zone'];
  
  $vars['region'] = $region['region'];
  
  $vars['theme_hook_suggestions'][] = 'region';
  $vars['theme_hook_suggestions'][] = 'region__' . $vars['region'];
  
  $vars['columns'] = $region['columns'];
  $vars['content'] = $vars['elements']['#children'];
  $vars['rid'] = drupal_html_class('region-' . $vars['region']);
  $vars['classes_array'] = array('region', $vars['rid'], 'grid-' . $vars['columns']);
  
  foreach (array('prefix', 'suffix', 'push', 'pull') as $quality) {
    if (!empty($region[$quality])) {
      $vars[$quality] = $region[$quality];
      
      $vars['classes_array'][] = $quality . '-' . $region[$quality];
    }
  }

  if (!empty($region['css'])) {
    $vars['classes_array'] = array_merge($vars['classes_array'], array_map('drupal_html_class', explode(' ', $region['css'])));
  }
}