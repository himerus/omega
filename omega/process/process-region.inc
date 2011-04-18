<?php

/*
 * @todo
 */
function omega_alpha_process_region(&$vars) {
  if ($vars['elements']['#region'] == 'content') {
    $vars['title_prefix'] = $vars['elements']['#page']['title_prefix'];
    $vars['title'] = $vars['elements']['#page']['title'];
    $vars['title_suffix'] = $vars['elements']['#page']['title_suffix'];
  }
}