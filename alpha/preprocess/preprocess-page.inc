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
  $regions = alpha_regions($GLOBALS['theme_key']);  
  $reference = &drupal_static('alpha_regions');
  
  $vars['#alpha'] = alpha_settings($GLOBALS['theme_key']);  
  $vars['feed_icons'] = $vars['#alpha']['toggle']['feed_icons'] ? $vars['feed_icons'] : NULL;
  $vars['tabs'] = $vars['#alpha']['toggle']['tabs'] ? $vars['tabs'] : NULL;
  $vars['action_links'] = $vars['#alpha']['toggle']['action_links'] ? $vars['action_links'] : NULL;
  $vars['show_messages'] = $vars['#alpha']['toggle']['messages'] ? $vars['show_messages'] : FALSE;  
  $vars['site_name_hidden'] = $vars['#alpha']['hidden']['site_name'];
  $vars['site_slogan_hidden'] = $vars['#alpha']['hidden']['site_slogan'];
  $vars['title_hidden'] = $vars['#alpha']['hidden']['title'];
  
  foreach (element_children($vars['page']) as $name) {   
    $item = $vars['page'][$name];
    unset($vars['page'][$name]);
    
    if (isset($regions[$name]) && (!empty($item) || $regions[$name]['force']) && $regions[$name]['enabled'] && $zones[$regions[$name]['zone']]['enabled']) {        
      $region = $regions[$name];
      $zone = $region['zone'];
      $section = $zones[$zone]['section'];
             
      $item['#region'] = $name;      
      $item['#data'] = $region;
      $item['#page'] = &$vars;
      $item['#sorted'] = FALSE;
      $item['#weight'] = (int) $region['weight'];
      
      if (empty($vars['page'][$section][$zone][$name])) {
        $item['#theme_wrappers'] = array('region');
      }
      
      $children = element_children($item);
      if (!empty($children)) {
        $last = count($children) - 1;
        
        foreach ($children as $element) {
          $item[$element]['#first'] = $element == $children[0];
          $item[$element]['#last'] = $element == $children[$last];
          $item[$element]['#page'] = &$vars;
        }        
      }
      
      $vars['page'][$section][$zone][$name] = $item;
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
        
        $reference[$item['primary']]['columns'] = $primary['#data']['columns'];
        $reference[$item['primary']]['width'] = $primary['#data']['width'];
        $reference[$item['primary']]['push'] = $primary['#data']['push'];
        
        foreach ($vars['page'][$item['section']][$zone] as $region => $info) {
          if (!$info['#data']['primary'] && $primary['#data']['weight'] > $info['#data']['weight']) {
            $vars['page'][$item['section']][$zone][$region]['#data']['pull'] = $reference[$item['primary']]['pull'] = $primary['#data']['width'];
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