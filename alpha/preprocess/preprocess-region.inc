<?php

function alpha_alpha_preprocess_region(&$vars) {
  $region = $vars['elements']['#region'];
  
  $vars['region'] = $region['region'];
  
  $vars['theme_hook_suggestions'][] = 'region';
  $vars['theme_hook_suggestions'][] = 'region__' . $vars['region'];
  
  $vars['prefix'] = $region['prefix'];
  $vars['suffix'] = $region['suffix'];
  $vars['columns'] = $region['columns'];
  $vars['content'] = $vars['elements']['#children'];
  $vars['rid'] = drupal_html_class('region-' . $vars['region']);
  $vars['classes_array'] = array('region', $vars['rid']);

  if (!empty($region['css'])) {
    $vars['classes_array'] = array_merge($vars['classes_array'], array_map('drupal_html_class', explode(' ', $region['css'])));
  }
  
  if ($vars['columns']) {
    $vars['classes_array'][] = 'grid-' . $vars['columns'];
    
    if ($vars['prefix']) {
      $vars['classes_array'][] = 'prefix-' . $vars['prefix'];
    }
    
    if ($vars['suffix']) {
      $vars['classes_array'][] = 'suffix-' . $vars['suffix'];
    }    
  }
}