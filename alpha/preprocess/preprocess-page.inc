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
  $theme = $GLOBALS['theme_key'];
  $zones = alpha_zones($theme);  
  $regions = alpha_regions($theme);  
  $reference = &drupal_static('alpha_regions');
  $alpha = array();
  
  $vars['#alpha'] = alpha_settings($theme);
  $vars['feed_icons'] = $vars['#alpha']['toggle']['feed_icons'] ? $vars['feed_icons'] : NULL;
  $vars['tabs'] = $vars['#alpha']['toggle']['tabs'] ? $vars['tabs'] : NULL;
  $vars['action_links'] = $vars['#alpha']['toggle']['action_links'] ? $vars['action_links'] : NULL;
  $vars['show_messages'] = $vars['#alpha']['toggle']['messages'] ? $vars['show_messages'] : FALSE;  
  $vars['site_name_hidden'] = $vars['#alpha']['hidden']['site_name'];
  $vars['site_slogan_hidden'] = $vars['#alpha']['hidden']['site_slogan'];
  $vars['title_hidden'] = $vars['#alpha']['hidden']['title'];
  
  foreach ($regions as $name => $region) {
    if ($region['enabled'] && $zones[$region['zone']]['enabled'] && ($region['force'] || !empty($vars['page'][$name]))) {        
      $zone = $region['zone'];
      $section = $zones[$zone]['section'];

      if (!empty($vars['page'][$name]) && $children = element_children($vars['page'][$name])) {
        $last = count($children) - 1;
        
        foreach ($children as $element) {
          $alpha[$section][$zone][$name][$element]['#first'] = $element == $children[0];
          $alpha[$section][$zone][$name][$element]['#last'] = $element == $children[$last];
          $alpha[$section][$zone][$name][$element]['#page'] = &$vars;
        }
      }
      
      $alpha[$section][$zone][$name] = !empty($vars['page'][$name]) ? $vars['page'][$name] : array();
      $alpha[$section][$zone][$name]['#theme_wrappers'] = array('region');
      $alpha[$section][$zone][$name]['#region'] = $region['region'];      
      $alpha[$section][$zone][$name]['#data'] = $region;
      $alpha[$section][$zone][$name]['#page'] = &$vars;
      $alpha[$section][$zone][$name]['#weight'] = (int) $region['weight'];
    }
    
    unset($vars['page'][$name]);
  }
  
  foreach ($zones as $zone => $item) {
    if ($item['enabled'] && ($item['force'] || isset($alpha[$item['section']][$zone]))) {
      if (!empty($item['primary']) && isset($alpha[$item['section']][$zone][$item['primary']])) {
        $primary = &$alpha[$item['section']][$zone][$item['primary']];
        $primary['#weight'] = -999;
        $primary['#data']['columns'] = $item['columns'] - $primary['#data']['prefix'] - $primary['#data']['suffix'];
        $primary['#data']['width'] = $item['columns'];
           
        foreach ($alpha[$item['section']][$zone] as $region => $info) {
          if (!$info['#data']['primary']) {
            $primary['#data']['columns'] -= $info['#data']['width'];
            $primary['#data']['width'] -= $info['#data']['width'];

            if ($primary['#data']['weight'] > $info['#data']['weight']) {
              $primary['#data']['push'] += $info['#data']['width'];              
            }
          }
        }
        
        $reference[$theme][$item['primary']]['columns'] = $primary['#data']['columns'];
        $reference[$theme][$item['primary']]['width'] = $primary['#data']['width'];
        $reference[$theme][$item['primary']]['push'] = $primary['#data']['push'];
        
        foreach ($alpha[$item['section']][$zone] as $region => $info) {
          if (!$info['#data']['primary'] && $primary['#data']['weight'] > $info['#data']['weight']) {
            $alpha[$item['section']][$zone][$region]['#data']['pull'] = $primary['#data']['width'];
            $reference[$theme][$region]['pull'] = $primary['#data']['width'];
          }
        }
      }
      
      $item['type'] = $item['primary'] && isset($alpha[$item['section']][$zone][$item['primary']]) ? 'dynamic' : 'static';
      $alpha[$item['section']][$zone]['#theme_wrappers'] = array('zone');
      $alpha[$item['section']][$zone]['#data'] = $item;
      $alpha[$item['section']][$zone]['#zone'] = $zone;
      $alpha[$item['section']][$zone]['#weight'] = (int) $item['weight'];
      $alpha[$item['section']][$zone]['#sorted'] = FALSE;
      $alpha[$item['section']][$zone]['#page'] = &$vars;
    }
  }

  foreach ($sections as $section => $item) {
    if (isset($alpha[$section])) {   
      $alpha[$section]['#theme_wrappers'] = array('section');
      $alpha[$section]['#section'] = $section;
      $alpha[$section]['#page'] = &$vars;
    }
  }
  
  $vars['page'] += $alpha;  
  $vars['attributes_array']['id'] = 'page';
  $vars['attributes_array']['class'] = array('clearfix');
}