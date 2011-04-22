<?php

/*
 * @todo
 */
function alpha_alpha_process_page(&$vars) {
  $vars['title'] = $vars['#alpha']['toggle']['feed_icons'] ? $vars['title'] : NULL;
  $vars['breadcrumb'] = $vars['#alpha']['toggle']['breadcrumb'] ? $vars['breadcrumb'] : NULL;
}