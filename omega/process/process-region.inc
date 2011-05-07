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
  switch ($vars['elements']['#region']) {
    case 'content':
      $vars['title_prefix'] = $vars['elements']['#page']['title_prefix'];
      $vars['title'] = $vars['elements']['#page']['title'];
      $vars['title_suffix'] = $vars['elements']['#page']['title_suffix'];
      $vars['tabs'] = $vars['elements']['#page']['tabs'];
      $vars['action_links'] = $vars['elements']['#page']['action_links'];
      
      $vars['title_hidden'] = $vars['elements']['#page']['title_hidden'];
      break;
    
    case 'menu':
      $vars['main_menu'] = $vars['elements']['#page']['main_menu'];
      $vars['secondary_menu'] = $vars['elements']['#page']['secondary_menu'];
      break;
    
    case 'branding':
    
      $vars['site_name'] = $vars['elements']['#page']['site_name'];
      $vars['linked_site_name'] = l($vars['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home')));
      $vars['site_slogan'] = $vars['elements']['#page']['site_slogan'];
      
      $vars['site_name_hidden'] = $vars['elements']['#page']['site_name_hidden'];
      $vars['site_slogan_hidden'] = $vars['elements']['#page']['site_slogan_hidden'];

      $vars['logo'] = $vars['elements']['#page']['logo'];
      $vars['logo_img'] = $vars['logo'] ? '<img src="' . $vars['logo'] . '" alt="' . $vars['site_name'] . '" id="logo" />' : '';
      $vars['linked_logo_img'] = $vars['logo'] ? l($vars['logo_img'], '<front>', array('rel' => 'home', 'title' => t($vars['site_name']), 'html' => TRUE)) : '';    
      break;
      
  }
}