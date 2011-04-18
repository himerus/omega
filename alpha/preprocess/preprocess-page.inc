<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_page(&$vars) {
  global $theme_key;
  
  $sections = alpha_sections();
  $zones = alpha_zones($theme_key);
  $settings = $vars['#alpha'] = alpha_settings($theme_key);
  $regions = alpha_regions($theme_key);

  foreach (alpha_special_blocks() as $block => $subject) {
    $region = $settings['blocks'][$block]['region'];    
    
    if ($settings['blocks'][$block]['enabled'] && isset($regions[$region])) {
      if ($blockify = alpha_blockify($block, $subject, $settings['blocks'][$block], $vars)) {
        $vars['page'][$region][$block] = $blockify;
      }
    }
  }
  
  $zone_regions = array();
  foreach ($regions as $region => $item) {
    if (isset($zones[$item['zone']]) && $zones[$item['zone']]['enabled']) {
      if (!empty($vars['page'][$region]) || $vars['#alpha']['debug']['block']) {
        $zone_regions[$item['zone']][$region] = $vars['page'][$region];
        $zone_regions[$item['zone']][$region]['#region'] = $region;      
        $zone_regions[$item['zone']][$region]['#data'] = $item;
        $zone_regions[$item['zone']][$region]['#page'] = &$vars;
        $zone_regions[$item['zone']][$region]['#sorted'] = FALSE;
        $zone_regions[$item['zone']][$region]['#weight'] = (int) $item['weight'];
        
        if ($vars['#alpha']['debug']['block']) {
          $zone_regions[$item['zone']][$region]['alpha_debug_'. $region] = alpha_debug_block($region, $item);
          $zone_regions[$item['zone']][$region]['#theme_wrappers'] = array('region');
        }
        
        $children = element_children($zone_regions[$item['zone']][$region]);
        if (!empty($children)) {
          $zone_regions[$item['zone']][$region][array_pop($children)]['#block']->last = TRUE;
        }   
      }
      
      unset($vars['page'][$region]);
    }   
  }

  foreach ($zones as $zone => $item) {
    if (isset($item['section']) && isset($sections[$item['section']]) && isset($zone_regions[$zone])) {
      if (!empty($item['primary']) && isset($zone_regions[$zone][$item['primary']])) {
        $primary = &$zone_regions[$zone][$item['primary']];
        
        $primary['#weight'] = -100;
        $primary['#data']['columns'] = $item['columns'] - $primary['#region']['prefix'] - $primary['#data']['suffix'];
        $primary['#data']['width'] = $item['columns'];
      
        $pull = array();
        
        foreach ($zone_regions[$zone] as $region => $info) {
          if ($region != $item['primary']) {
            $primary['#data']['columns'] -= $info['#data']['width'];
            $primary['#data']['width'] -= $info['#data']['width'];

            if ($primary['#data']['weight'] > $info['#data']['weight']) {
              $primary['#data']['push'] += $info['#data']['width'];              
              $pull[$region] = &$primary['#data']['width'];
            }
          }         
        }
        
        foreach ($pull as $region => $value) {
          $zone_regions[$zone][$region]['#data']['pull'] = $value;
        }
      }
      
      $item['type'] = $item['primary'] && isset($zone_regions[$zone][$item['primary']]) ? 'dynamic' : 'static';
      
      $vars['page'][$item['section'] . '_section'][$zone] = $zone_regions[$zone];
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