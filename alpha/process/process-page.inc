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
  $vars['title'] = $vars['#alpha']['toggle']['page_title'] ? $vars['title'] : NULL;
  $vars['breadcrumb'] = $vars['#alpha']['toggle']['breadcrumb'] ? $vars['breadcrumb'] : NULL;
}