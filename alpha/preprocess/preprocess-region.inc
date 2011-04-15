<?php

function alpha_alpha_preprocess_region(&$vars) {
  $region = $vars['elements']['#region'];
  $zone = $vars['elements']['#zone'];
  
  $vars['region'] = $region['region'];
  
  $vars['theme_hook_suggestions'][] = 'region';
  $vars['theme_hook_suggestions'][] = 'region__' . $vars['region'];
  
  $vars['prefix'] = $region['prefix'];
  $vars['suffix'] = $region['suffix'];
  $vars['columns'] = $region['columns'];
  $vars['content'] = $vars['elements']['#children'];
  $vars['rid'] = drupal_html_class('region-' . $vars['region']);
  $vars['classes_array'] = array('region', $vars['rid']);

  if (!empty($zone['primary']) && isset($vars['elements']['#companions'][$zone['primary']])) {
    $companions = $vars['elements']['#companions'];
    
    if ($region['primary']) {
      $vars['columns'] = $zone['columns'] - $vars['prefix'] - $vars['suffix'];
      
      foreach ($companions as $key => $companion) {
        if ($region['region'] != $key) {
          $vars['columns'] -= $companion['columns'];
          $vars['columns'] -= $companion['prefix'];
          $vars['columns'] -= $companion['suffix'];
        }
      }
    }
  }
  
  $vars['classes_array'][] = 'grid-' . $vars['columns'];
  
  foreach (array('prefix', 'suffix', 'push', 'pull') as $quality) {
    if (!empty($vars[$quality])) {
      $vars['classes_array'][] = $quality . '-' . $vars[$quality];
    }
  }
  
  if (!empty($region['css'])) {
    $vars['classes_array'] = array_merge($vars['classes_array'], array_map('drupal_html_class', explode(' ', $region['css'])));
  }  
}