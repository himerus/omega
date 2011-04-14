<?php

function alpha_alpha_preprocess_region(&$vars) {
  $vars['region'] = $vars['elements']['#region']['region'];
  
  $vars['theme_hook_suggestions'][] = 'region';
  $vars['theme_hook_suggestions'][] = 'region__' . $vars['region'];
  
  $vars['prefix'] = $vars['elements']['#region']['prefix'];
  $vars['suffix'] = $vars['elements']['#region']['suffix'];
  $vars['columns'] = $vars['elements']['#region']['columns'];
  $vars['content'] = $vars['elements']['#children'];
  $vars['rid'] = drupal_html_class('region-' . $vars['region']);
  $vars['classes_array'] = array('region', $vars['rid']);
  
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