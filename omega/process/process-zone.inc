<?php

/*
 * @todo
 */
function omega_alpha_process_zone(&$vars) {
  if ($vars['elements']['#zone'] == 'content') {
    $vars['messages'] = $vars['elements']['#page']['messages'];
    $vars['breadcrumb'] = $vars['elements']['#page']['breadcrumb'];
    
    $vars['main_menu'] = $vars['elements']['#page']['main_menu'];
    $vars['secondary_menu'] = $vars['elements']['#page']['secondary_menu'];
  }
}