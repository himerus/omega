<?php
// $Id$

/**
 * @file
 * Contents of omega_preprocess_page().
 */

// Set a variable for the site name title and logo alt attributes text.
$slogan_text = $vars['site_slogan'];
$site_name_text = $vars['site_name'];
$vars['site_name_and_slogan'] = $site_name_text . ' ' . $slogan_text;
if (isset($vars['logo'])) {
  //$vars['logo_img'] = theme('image', substr($vars['logo'], strlen(base_path())), t($vars['site_name']), t($vars['site_name']));
  // @todo - Why no theme_image()?
  $vars['logo_img'] = '<img src="' . $vars['logo'] . '" alt="' . $vars['site_name_and_slogan'] . '" id="logo" />';
}

// NINESIXTY - For easy printing of variables.
if (isset($vars['logo_img'])) {
  $vars['linked_logo_img'] = l($vars['logo_img'], '<front>', array('rel' => 'home', 'title' => t($vars['site_name']), 'html' => TRUE));
}
if (isset($vars['site_name'])) {
  $vars['linked_site_name'] = l($vars['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home')));
}

  if (isset($vars['main_menu'])) {
    $vars['primary_nav'] = theme('links__system_main_menu', array(
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
    $vars['primary_nav'] = FALSE;
  }
  if (isset($vars['secondary_menu'])) {
    $vars['secondary_nav'] = theme('links__system_secondary_menu', array(
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
    $vars['secondary_nav'] = FALSE;
  }

if (user_access('search content') && theme_get_setting('omega_render_search_box')) {
  $vars['search_region'] = drupal_get_form('search_block_form');
  $vars['search_region_classes'] = 'grid-' . theme_get_setting('omega_search_width');
}