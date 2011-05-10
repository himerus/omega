<?php

/**
 * @file
 * Container for hook_preprocess_page().
 */

/**
 * Implements hook_preprocess_page().
 * 
 * @see
 *   alpha_preprocess().
 */
function alpha_alpha_preprocess_page(&$vars) {
  $sections = alpha_sections();
  $zones = alpha_zones($GLOBALS['theme_key']);
  $settings = $vars['#alpha'] = alpha_settings($GLOBALS['theme_key']);
  $regions = alpha_regions($GLOBALS['theme_key']);
  
  $vars['feed_icons'] = $settings['toggle']['feed_icons'] ? $vars['feed_icons'] : NULL;
  $vars['tabs'] = $settings['toggle']['tabs'] ? $vars['tabs'] : NULL;
  $vars['action_links'] = $settings['toggle']['action_links'] ? $vars['action_links'] : NULL;
  $vars['show_messages'] = $settings['toggle']['messages'] ? $vars['show_messages'] : FALSE;
  
  $vars['site_name_hidden'] = $settings['hidden']['site_name'];
  $vars['site_slogan_hidden'] = $settings['hidden']['site_slogan'];
  $vars['title_hidden'] = $settings['hidden']['title'];
  
  $zone_regions = array();
  foreach ($regions as $region => $item) {
    if ($item['enabled'] && $zones[$item['zone']]['enabled'] && ($item['force'] || !empty($vars['page'][$region]))) {
      $zone_regions[$item['zone']][$region] = $vars['page'][$region];
      $zone_regions[$item['zone']][$region]['#region'] = $region;      
      $zone_regions[$item['zone']][$region]['#data'] = $item;
      $zone_regions[$item['zone']][$region]['#page'] = &$vars;
      $zone_regions[$item['zone']][$region]['#sorted'] = FALSE;
      $zone_regions[$item['zone']][$region]['#weight'] = (int) $item['weight'];
      
      if (!isset($zones[$item['zone']]['width'])) {
        $zones[$item['zone']]['width'] = 0;
      }
      
      if (!$item['primary']) {
        $zones[$item['zone']]['width'] += $item['width'];
      }
      
      if (!isset($zone_regions[$item['zone']][$region]['#theme_wrappers']) || !in_array('region', $zone_regions[$item['zone']][$region]['#theme_wrappers'])) {
        $zone_regions[$item['zone']][$region]['#theme_wrappers'][] = 'region';
      }
      
      $children = element_children($zone_regions[$item['zone']][$region]);
      if (!empty($children)) {
        $zone_regions[$item['zone']][$region][array_pop($children)]['#block']->last = TRUE;
      }
    }
    
    unset($vars['page'][$region]); 
  }

  foreach ($zones as $zone => $item) {
    if ($item['enabled'] && ($item['force'] || isset($zone_regions[$zone]))) {
      if (!empty($item['primary']) && isset($zone_regions[$zone][$item['primary']])) {
        if ($item['width'] < $item['columns']) {
          $primary = &$zone_regions[$zone][$item['primary']];

          $primary['#weight'] = -100;
          $primary['#data']['columns'] = $item['columns'] - $primary['#data']['prefix'] - $primary['#data']['suffix'];
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
      }
      
      $item['type'] = $item['primary'] && isset($zone_regions[$zone][$item['primary']]) && $item['width'] <= $item['columns'] ? 'dynamic' : 'static';
      
      if (isset($zone_regions[$zone])) {
        $vars['page'][$item['section'] . '_section'][$zone] = $zone_regions[$zone];
      }
      
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