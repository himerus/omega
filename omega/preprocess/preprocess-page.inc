<?php
// $Id: preprocess-page.inc,v 1.1.2.17 2011/01/11 21:26:32 himerus Exp $

/**
 * @file
 * Contents of omega_preprocess_page().
 */

if (variable_get('menu_main_links_source', 'main-menu') == variable_get('menu_secondary_links_source', 'user-menu')) {
  $menu_depth = omega_theme_get_setting('omega_menu_depth') ? omega_theme_get_setting('omega_menu_depth') : 1;
  // render the menus as nested ULs to style up some drop downs.
  $vars['menu_data'] = $menu = omega_menu_structure(menu_tree_page_data(variable_get('menu_main_links_source', 'main-menu'), $menu_depth, FALSE));
  $vars['main_menu'] = theme('links', array(
    'links' => $menu,
    'attributes' => array(
      'class' => array('links', 'main-menu'),
    ),
    'heading' => array(
      'text' => t('Main menu'),
      'level' => 'h2',
      'class' => array('element-invisible'),
    )
  ));
  $vars['secondary_menu'] = FALSE;
}
else {
  // render default main & secondary menus (separate menus)
  if (isset($vars['main_menu'])) {
    $vars['main_menu'] = theme('links__system_main_menu', array(
      'links' => $vars['main_menu'],
      'attributes' => array(
        'class' => array('links', 'main-menu'),
      ),
      'heading' => array(
        'text' => t('Main menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      )
    ));
  }
  else {
    $vars['main_menu'] = FALSE;
  }
  if (isset($vars['secondary_menu'])) {
    $vars['secondary_menu'] = theme('links__system_secondary_menu', array(
      'links' => $vars['secondary_menu'],
      'attributes' => array(
        'class' => array('links', 'secondary-menu'),
      ),
      'heading' => array(
        'text' => t('Secondary menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      )
    ));
  }
  else {
    $vars['secondary_menu'] = FALSE;
  }
}