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
  if ($vars['elements']['#zone'] == 'content') {
    $vars['messages'] = $GLOBALS['page']['messages'];
    $vars['breadcrumb'] = $GLOBALS['page']['breadcrumb'];
  }
}