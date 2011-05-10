<?php

/**
 * @file
 * Container for hook_process_zone().
 */

/**
 * Implements hook_process_zone().
 * 
 * @see
 *   alpha_process().
 */
function omega_alpha_process_zone(&$vars) {
  switch($vars['elements']['#zone']) {
    case 'content':
      $vars['messages'] = $vars['elements']['#page']['messages'];
      $vars['breadcrumb'] = $vars['elements']['#page']['breadcrumb'];
      break;
  }
}