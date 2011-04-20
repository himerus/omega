<?php

/*
 * @todo
 */
function omega_alpha_process_zone(&$vars) {
  $zone = $vars['elements']['#zone'];
  switch($zone) {
    case 'content':
      $vars['messages'] = $vars['elements']['#page']['messages'];
      $vars['breadcrumb'] = $vars['elements']['#page']['breadcrumb'];
      break;
  }
}