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
  
  $zone_regions = array();
  foreach ($regions as $region => $item) {
    if ($item['enabled'] && $zones[$item['zone']]['enabled'] && (!empty($vars['page'][$region]) || $settings['debug']['block'])) {
      $zone_regions[$item['zone']][$region] = $vars['page'][$region];
      $zone_regions[$item['zone']][$region]['#region'] = $region;      
      $zone_regions[$item['zone']][$region]['#data'] = $item;
      $zone_regions[$item['zone']][$region]['#page'] = &$vars;
      $zone_regions[$item['zone']][$region]['#sorted'] = FALSE;
      $zone_regions[$item['zone']][$region]['#weight'] = (int) $item['weight'];
      
      if ($settings['debug']['block']) {
        $zone_regions[$item['zone']][$region]['alpha_debug_'. $region] = alpha_debug_block($region, $item['name']);
        $zone_regions[$item['zone']][$region]['#theme_wrappers'] = array('region');
      }
      
      $children = element_children($zone_regions[$item['zone']][$region]);
      if (!empty($children)) {
        $zone_regions[$item['zone']][$region][array_pop($children)]['#block']->last = TRUE;
      }
    }
    
    unset($vars['page'][$region]); 
  }

  foreach ($zones as $zone => $item) {
    if ($item['enabled'] && isset($zone_regions[$zone])) {
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
      $vars['page'][$item['section'] . '_section'][$zone]['#data'] = $item;
      $vars['page'][$item['section'] . '_section'][$zone]['#zone'] = $zone;
      $vars['page'][$item['section'] . '_section'][$zone]['#weight'] = (int) $item['weight'];
      $vars['page'][$item['section'] . '_section'][$zone]['#sorted'] = FALSE;
      $vars['page'][$item['section'] . '_section'][$zone]['#page'] = &$vars;
    }
  }

  foreach ($sections as $section => $item) {
    if (isset($vars['page'][$section . '_section'])) {   
      $vars['page'][$section . '_section']['#theme_wrappers'][] = 'section';
      $vars['page'][$section . '_section']['#section'] = $section;
      $vars['page'][$section . '_section']['#page'] = &$vars;
    }
  }
  
  $vars['attributes_array']['id'] = 'page';
  $vars['attributes_array']['class'] = array('clearfix');
}