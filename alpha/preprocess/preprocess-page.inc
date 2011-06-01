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
  global $page;

  $page = &$vars;
  $settings = alpha_settings();
  
  $vars['feed_icons'] = $settings['toggle']['feed_icons'] ? $vars['feed_icons'] : NULL;
  $vars['tabs'] = $settings['toggle']['tabs'] ? $vars['tabs'] : NULL;
  $vars['action_links'] = $settings['toggle']['action_links'] ? $vars['action_links'] : NULL;
  $vars['show_messages'] = $settings['toggle']['messages'] ? $vars['show_messages'] : FALSE;  
  $vars['site_name_hidden'] = $settings['hidden']['site_name'];
  $vars['site_slogan_hidden'] = $settings['hidden']['site_slogan'];
  $vars['title_hidden'] = $settings['hidden']['title'];   
  $vars['attributes_array']['id'] = 'page';
  $vars['attributes_array']['class'] = array('clearfix');
}