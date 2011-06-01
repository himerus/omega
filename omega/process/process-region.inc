<?php

/**
 * @file
 * Container for hook_process_region().
 */

/**
 * Implements hook_process_region().
 * 
 * @see
 *   alpha_process().
 */
function omega_alpha_process_region(&$vars) {
  if (in_array($vars['elements']['#region'], array('content', 'menu', 'branding'))) {
    switch ($vars['elements']['#region']) {
      case 'content':
        $vars['title_prefix'] = $GLOBALS['page']['title_prefix'];
        $vars['title'] = $GLOBALS['page']['title'];
        $vars['title_suffix'] = $GLOBALS['page']['title_suffix'];
        $vars['tabs'] = $GLOBALS['page']['tabs'];
        $vars['action_links'] = $GLOBALS['page']['action_links'];      
        $vars['title_hidden'] = $GLOBALS['page']['title_hidden'];
        break;
      
      case 'menu':
        $vars['main_menu'] = $GLOBALS['page']['main_menu'];
        $vars['secondary_menu'] = $GLOBALS['page']['secondary_menu'];
        break;
      
      case 'branding':    
        $vars['site_name'] = $GLOBALS['page']['site_name'];
        $vars['linked_site_name'] = l($vars['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home'), 'html' => TRUE));
        $vars['site_slogan'] = $GLOBALS['page']['site_slogan'];      
        $vars['site_name_hidden'] = $GLOBALS['page']['site_name_hidden'];
        $vars['site_slogan_hidden'] = $GLOBALS['page']['site_slogan_hidden'];
        $vars['logo'] = $GLOBALS['page']['logo'];
        $vars['logo_img'] = $vars['logo'] ? '<img src="' . $vars['logo'] . '" alt="' . $vars['site_name'] . '" id="logo" />' : '';
        $vars['linked_logo_img'] = $vars['logo'] ? l($vars['logo_img'], '<front>', array('rel' => 'home', 'title' => t($vars['site_name']), 'html' => TRUE)) : '';    
        break;      
    }
  }
}