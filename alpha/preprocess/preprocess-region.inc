<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_region(&$vars) {
  if (isset($vars['elements']['#page']['#alpha'])) {
    $region = $vars['elements']['#data'];
    
    $vars['region'] = $region['region'];

    $vars['columns'] = $region['columns'];
    $vars['content'] = $vars['elements']['#children'];
    $vars['classes_array'][] = 'grid-' . $vars['columns'];
    
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
  
  $vars['attributes_array']['class'] = $vars['classes_array'];
}