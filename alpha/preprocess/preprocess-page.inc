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
  
  foreach (element_children($vars['page']) as $name) {   
    $item = $vars['page'][$name];
    unset($vars['page'][$name]);
    
    if (isset($regions[$name]) && (!empty($item) || $regions[$name]['force']) && $regions[$name]['enabled'] && $zones[$regions[$name]['zone']]['enabled']) {        
      $region = $regions[$name];
      $zone = $region['zone'];
      $section = $zones[$zone]['section'];
      
      $vars['page'][$section][$zone][$name] = $item;       
      $vars['page'][$section][$zone][$name]['#region'] = $name;      
      $vars['page'][$section][$zone][$name]['#data'] = $region;
      $vars['page'][$section][$zone][$name]['#page'] = &$vars;
      $vars['page'][$section][$zone][$name]['#sorted'] = FALSE;
      $vars['page'][$section][$zone][$name]['#weight'] = (int) $region['weight'];
      
      if (empty($vars['page'][$section][$zone][$name])) {
        $vars['page'][$section][$zone][$name]['#theme_wrappers'] = array('region');
      }
          
      $children = element_children($item, TRUE);
      if (!empty($children)) {
        $first = reset($children);
        $last = end($children);
        
        foreach ($children as $element) {
          $vars['page'][$section][$zone][$name][$element]['#first'] = $element == $first;
          $vars['page'][$section][$zone][$name][$element]['#last'] = $element == $last;
          $vars['page'][$section][$zone][$name][$element]['#page'] = &$vars;
        }        
      }
    }
    else {
      unset($vars['page'][$name]);
    }
  }

  foreach ($zones as $zone => $item) {
    if ($item['enabled'] && ($item['force'] || isset($vars['page'][$item['section']][$zone]))) {
      if (!empty($item['primary']) && isset($vars['page'][$item['section']][$zone][$item['primary']])) {
        $primary = &$vars['page'][$item['section']][$zone][$item['primary']];
        $primary['#weight'] = -999;
        $primary['#data']['columns'] = $item['columns'] - $primary['#data']['prefix'] - $primary['#data']['suffix'];
        $primary['#data']['width'] = $item['columns'];
           
        foreach ($vars['page'][$item['section']][$zone] as $region => $info) {
          if (!$info['#data']['primary']) {
            $primary['#data']['columns'] -= $info['#data']['width'];
            $primary['#data']['width'] -= $info['#data']['width'];

            if ($primary['#data']['weight'] > $info['#data']['weight']) {
              $primary['#data']['push'] += $info['#data']['width'];              
            }
          }         
        }
        
        foreach ($vars['page'][$item['section']][$zone] as $region => &$info) {
          if ($region != $item['primary'] && $primary['#data']['weight'] > $info['#data']['weight']) {
            $info['#data']['pull'] = $primary['#data']['width'];
          }
        }
      }
      
      $item['type'] = $item['primary'] && isset($vars['page'][$item['section']][$zone][$item['primary']]) ? 'dynamic' : 'static';
      $vars['page'][$item['section']][$zone]['#theme_wrappers'][] = 'zone';
      $vars['page'][$item['section']][$zone]['#data'] = $item;
      $vars['page'][$item['section']][$zone]['#zone'] = $zone;
      $vars['page'][$item['section']][$zone]['#weight'] = (int) $item['weight'];
      $vars['page'][$item['section']][$zone]['#sorted'] = FALSE;
      $vars['page'][$item['section']][$zone]['#page'] = &$vars;
    }
  }

  foreach ($sections as $section => $item) {
    if (isset($vars['page'][$section])) {   
      $vars['page'][$section]['#theme_wrappers'][] = 'section';
      $vars['page'][$section]['#section'] = $section;
      $vars['page'][$section]['#page'] = &$vars;
    }
  }

  $vars['attributes_array']['id'] = 'page';
  $vars['attributes_array']['class'] = array('clearfix');
}