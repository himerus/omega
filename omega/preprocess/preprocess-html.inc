<?php
// $Id$

/**
 * @file
 * Contents of omega_preprocess_html().
 */

/**
 * Allow the default text in the search box to be 100% configurable via backend theme settings
 */
$search_text_val = check_plain(theme_get_setting('omega_search_default_text')) ? check_plain(theme_get_setting('omega_search_default_text')) : 'search...';
$search_text = array(
  'default_search_text' => $search_text_val,
);
drupal_add_js($search_text, 'setting');

// ACQUIA-MARINA
// Set site title, slogan, mission, page title & separator (unless using Page Title module)
if (!module_exists('page_title')) {
  $title = t(variable_get('site_name', ''));
  $slogan = t(variable_get('site_slogan', ''));
  $page_title = t(drupal_get_title());
  $title_separator = theme_get_setting('configurable_separator');
  if ($vars['is_front']) { // Front page title settings
    switch (theme_get_setting('front_page_title_display')) {
      case 'title_slogan':
        $vars['head_title'] = drupal_set_title($title . $title_separator . $slogan);
        break;
      case 'slogan_title':
        $vars['head_title'] = drupal_set_title($slogan . $title_separator . $title);
        break;
      case 'custom':
        if (theme_get_setting('page_title_display_custom') !== '') {
          $vars['head_title'] = drupal_set_title(t(theme_get_setting('page_title_display_custom')));
        }
    }
  }
  else { // Non-front page title settings
    switch (theme_get_setting('other_page_title_display')) {
      case 'ptitle_slogan':
        $vars['head_title'] = drupal_set_title($page_title . $title_separator . $slogan);
        break;
      case 'ptitle_stitle':
        $vars['head_title'] = drupal_set_title($page_title . $title_separator . $title);
        break;
      case 'ptitle_custom':
        if (theme_get_setting('other_page_title_display_custom') !== '') {
          $vars['head_title'] = drupal_set_title($page_title . $title_separator . t(theme_get_setting('other_page_title_display_custom')));
        }
        break;
      case 'custom':
        if (theme_get_setting('other_page_title_display_custom') !== '') {
          // make sure to wrap this in a t() or you might be sacrificing a kitten
          $vars['head_title'] = drupal_set_title(t(theme_get_setting('other_page_title_display_custom')));
        }
    }
  }
  $vars['head_title'] = strip_tags($vars['head_title']); // Remove any potential html tags
}