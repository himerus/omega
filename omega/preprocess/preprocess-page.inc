<?php
// $Id$

/**
 * @file
 * Contents of omega_preprocess_page().
 */



//krumo($zones);

/**
 * STATIC REGION BUILDER EXAMPLE
 */
/**

$vars['postscript_container_width'] = theme_get_setting('omega_postscript_container_width');
$postscript_regions = array(
  'postscript_first' => array(
    'data' => $vars['page']['postscript_first'],
    'width' => theme_get_setting('omega_postscript_first_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_first_prefix'),
      'suffix' => theme_get_setting('omega_postscript_first_suffix')),
    ),
  'postscript_second' => array(
    'data' => $vars['page']['postscript_second'],
    'width' => theme_get_setting('omega_postscript_second_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_second_prefix'),
      'suffix' => theme_get_setting('omega_postscript_second_suffix')),
    ),
  'postscript_third' => array(
    'data' => $vars['page']['postscript_third'],
    'width' => theme_get_setting('omega_postscript_third_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_third_prefix'),
      'suffix' => theme_get_setting('omega_postscript_third_suffix'))
  ),
  'postscript_fourth' => array(
    'data' => $vars['page']['postscript_fourth'],
    'width' => theme_get_setting('omega_postscript_fourth_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_fourth_prefix'),
      'suffix' => theme_get_setting('omega_postscript_fourth_suffix'))
  ),
);
$vars = static_region_builder($postscript_regions, theme_get_setting('postscript_container_width'), $vars); 
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


//krumo($vars);