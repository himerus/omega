<?php

/*
 * @todo
 */
function omega_alpha_process_zone(&$vars) {
  switch($vars['elements']['#zone']) {
    case 'content':
      $vars['messages'] = $vars['elements']['#page']['messages'];
      $vars['breadcrumb'] = $vars['elements']['#page']['breadcrumb'];
      break;
  }
}