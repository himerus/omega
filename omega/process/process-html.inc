<?php
// $Id$

/**
 * @file
 * Contents of omega_process_html().
 */

// enable/disable optional CSS files
if (theme_get_setting('reset_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/reset.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -100));
}
if (theme_get_setting('text_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/text.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -99));
}
if (theme_get_setting('regions_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/regions.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -98));
}
if (theme_get_setting('defaults_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/defaults.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -97));
}
if (theme_get_setting('custom_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/custom.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -1));
}
if (theme_get_setting('table_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/tables.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -96));
}
if (theme_get_setting('pager_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/pagers.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -95));
}
if (theme_get_setting('form_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/forms.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -94));
}
if (theme_get_setting('menu_css') == '1') {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/menus.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', -93));
}
if (module_exists('skinr')) {
  drupal_add_css(drupal_get_path('theme', 'omega') . '/css/skinr.css', array('group' => CSS_DEFAULT, 'every_page' => TRUE, 'weight', 1));
}

// Add JavaScript for fluid width option
if (theme_get_setting('omega_fixed_fluid') == 'fluid') {
  drupal_add_js(array('fluid_grid' => theme_get_setting('omega_fixed_fluid')), 'setting');
}
$vars['styles']  = drupal_get_css();