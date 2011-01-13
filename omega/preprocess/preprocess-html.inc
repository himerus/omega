<?php
// $Id: preprocess-html.inc,v 1.1.2.7 2011/01/13 15:05:04 himerus Exp $

/**
 * @file
 * Contents of omega_preprocess_html().
 */
 
// grid debugging
global $user;
$debug_access = FALSE;
$debug_roles = omega_theme_get_setting('debug_grid_roles');

if (!isset($user->roles[1])) {
  // Check to see if $user has the appropriate role.
  foreach ($debug_roles as $k => $v) {
    if ($v && isset($user->roles[$k])) {
      // user has access to debug tools
      $debug_access = TRUE;
    }
  }
}

// if grid debug is ON, and user either has access by role, or is uid 1
if(omega_theme_get_setting('debug_grid_toggle') && ($debug_access || $user->uid == 1)) {
  $debug_settings = array(
    'grid_overlay_on' => omega_theme_get_setting('debug_grid_toggle'),
    'grid_overlay_state' => omega_theme_get_setting('debug_grid_toggle_state'),
  );
  drupal_add_js($debug_settings, 'setting');
  drupal_add_js(drupal_get_path('theme', 'omega') . '/js/debug.js', array('weight' => 1000, 'type' => 'file'));
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/debug.css');
}

/**
 * Allow the default text in the search box to be 100% configurable via backend theme settings
 */
$search_text_val = check_plain(omega_theme_get_setting('omega_search_default_text')) ? check_plain(omega_theme_get_setting('omega_search_default_text')) : 'search...';
$search_text = array(
  'default_search_text' => $search_text_val,
);
drupal_add_js($search_text, 'setting');

// ACQUIA-MARINA
// Set site title, slogan, mission, page title & separator (unless using Page Title module)
if (!module_exists('page_title')) {
  $title = (omega_theme_get_setting('toggle_name') ? filter_xss_admin(variable_get('site_name', '')) : '');
  $slogan = (omega_theme_get_setting('toggle_slogan') ? filter_xss_admin(variable_get('site_slogan', '')) : '');
  $page_title = drupal_get_title();
  $title_separator = omega_theme_get_setting('configurable_separator');
  if ($vars['is_front']) { // Front page title settings
    switch (omega_theme_get_setting('front_page_title_display')) {
      case 'title_slogan':
        $vars['head_title'] = drupal_set_title($title . $title_separator . $slogan, PASS_THROUGH);
        break;
      case 'slogan_title':
        $vars['head_title'] = drupal_set_title($slogan . $title_separator . $title, PASS_THROUGH);
        break;
      case 'custom':
        if (omega_theme_get_setting('page_title_display_custom') !== '') {
          $vars['head_title'] = drupal_set_title(t(omega_theme_get_setting('page_title_display_custom')), PASS_THROUGH);
        }
    }
  }
  else { // Non-front page title settings
    switch (omega_theme_get_setting('other_page_title_display')) {
      case 'ptitle_slogan':
        $vars['head_title'] = drupal_set_title($page_title . $title_separator . $slogan, PASS_THROUGH);
        break;
      case 'ptitle_stitle':
        $vars['head_title'] = drupal_set_title($page_title . $title_separator . $title, PASS_THROUGH);
        break;
      case 'ptitle_custom':
        if (omega_theme_get_setting('other_page_title_display_custom') !== '') {
          $vars['head_title'] = drupal_set_title($page_title . $title_separator . t(omega_theme_get_setting('other_page_title_display_custom')), PASS_THROUGH);
        }
        break;
      case 'custom':
        if (omega_theme_get_setting('other_page_title_display_custom') !== '') {
          // make sure to wrap this in a t() or you might be sacrificing a kitten
          $vars['head_title'] = drupal_set_title(t(omega_theme_get_setting('other_page_title_display_custom')));
        }
    }
  }
}