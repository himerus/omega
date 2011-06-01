<?php

/**
 * @file
 * Container for hook_process_page().
 */

/**
 * Implements hook_process_page().
 * 
 * @see
 *   alpha_process().
 */
function alpha_alpha_process_page(&$vars) {
  $settings = alpha_settings();

  $vars['title'] = $settings['toggle']['page_title'] ? $vars['title'] : NULL;
  print_r($GLOBALS['page']['title']);
  $vars['breadcrumb'] = $settings['toggle']['breadcrumb'] ? $vars['breadcrumb'] : NULL;
  
}