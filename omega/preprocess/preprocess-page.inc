<?php
// $Id$

/**
 * @file
 * Contents of omega_preprocess_page().
 */

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