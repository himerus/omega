<?php

function alpha_alpha_preprocess_page(&$vars) {
  global $theme_key;
  
  $sections = alpha_sections();
  $zones = alpha_zones($theme_key);
  
  $vars['#alpha'] = alpha_settings($theme_key);
  
  alpha_include_grid($vars['#alpha']['grid']);

  $regions = array();
  foreach (alpha_regions($theme_key) as $region => $item) {
    if (isset($zones[$item['zone']]) && $zones[$item['zone']]['enabled']) {
      if (!empty($vars['page'][$region]) || $vars['#alpha']['debug']['block']) {
        foreach ($vars['page'][$region] as $key => $block) {
          // If the user -god knows why- didnt assignt the "Main page content" block
          // we need to give it a #weight manually so we can still sort it properly.
          if ($key[0] !== '#' && !isset($block['#weight'])) {
            $vars['page'][$region][$key]['#weight'] = 0;
          }
        }
        
        $regions[$item['zone']][$region] = $vars['page'][$region];      
        $regions[$item['zone']][$region]['#region'] = $item;
        $regions[$item['zone']][$region]['#page'] = &$vars;
        
        if ($vars['#alpha']['debug']['block']) {
          $regions[$item['zone']][$region]['alpha_debug_'. $region] = alpha_debug_block($region, $item);
          $regions[$item['zone']][$region]['#sorted'] = FALSE;
          $regions[$item['zone']][$region]['#region'] = $item;
          $regions[$item['zone']][$region]['#weight'] = (int) $item['weight'];
          $regions[$item['zone']][$region]['#zone'] = $zones[$item['zone']];
          $regions[$item['zone']][$region]['#theme_wrappers'] = array('region');
        }
      }
      
      unset($vars['page'][$region]);
    }   
  }


  foreach ($zones as $zone => $item) {
    if (isset($item['section']) && isset($sections[$item['section']]) && isset($regions[$zone])) {
      if (!empty($item['primary']) && isset($regions[$zone][$item['primary']])) {
        $primary = &$regions[$zone][$item['primary']];
        
        $primary['#weight'] = -100;
        $primary['#region']['columns'] = $item['columns'] - $primary['#region']['prefix'] - $primary['#region']['suffix'];
        $primary['#region']['width'] = $item['columns'];
      
        foreach ($regions[$zone] as $region => $info) {
          if ($region != $item['primary']) {
            $primary['#region']['columns'] -= $info['#region']['width'];
            $primary['#region']['width'] -= $info['#region']['width'];

            if ($primary['#region']['weight'] > $info['#region']['weight']) {
              $primary['#region']['push'] += $info['#region']['width'];              
              $regions[$zone][$region]['#region']['pull'] = &$primary['#region']['width'];
            }
          }         
        }
      } 
      
      $vars['page'][$item['section'] . '_section'][$zone] = $regions[$zone];
      $vars['page'][$item['section'] . '_section'][$zone]['#theme_wrappers'][] = 'zone';
      $vars['page'][$item['section'] . '_section'][$zone]['#zone'] = $item;
      $vars['page'][$item['section'] . '_section'][$zone]['#weight'] = (int) $item['weight'];
      $vars['page'][$item['section'] . '_section'][$zone]['#sorted'] = FALSE;
      $vars['page'][$item['section'] . '_section'][$zone]['#page'] = &$vars;
    }
  }
  
  foreach ($sections as $section => $item) {
    if (!empty($vars['page'][$section . '_section'])) {   
      $vars['page'][$section . '_section']['#theme_wrappers'][] = 'section';
      $vars['page'][$section . '_section']['#section'] = $section;
      $vars['page'][$section . '_section']['#page'] = &$vars;
    }
  }
}