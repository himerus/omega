<?php

/**
 * @file
 * Implements custom theme settings for Omega Five related general settings.
 */

// Vertical tab sections.
$form['options'] = [
  '#type' => 'details',
  '#attributes' => ['class' => ['debug']],
  '#title' => t('Default Options'),
  '#weight' => -999,
  '#group' => 'omega',
  '#open' => FALSE,
];

$enable_backups = theme_get_setting('enable_backups', $theme);
$form['enable_backups'] = [
  '#type' => 'checkbox',
  '#title' => t('Enable Backups (NON-Functional)'),
  '#description' => t('Since this form has the ability to regenerate SCSS and CSS files on the fly, turning on this backup feature will create a copy of layout.scss, layout.css, and THEME.settings.yml file before overwriting any data. These backups will be stored in the default files directory under <em>public://omega/layout/backups</em>.'),
  '#default_value' => isset($enable_backups) ? $enable_backups : TRUE,
  '#group' => 'options',
  '#disabled' => TRUE,
];

$compile_scss = theme_get_setting('compile_scss', $theme);
$form['compile_scss'] = [
  '#type' => 'checkbox',
  '#title' => t('Compile SCSS Directly'),
  '#description' => t('Omega Five has the ability to create SCSS on the fly based on the layout configuration, and also the optional SCSS variables that control other visual appearances of your theme. When this option is disabled, Omega will not write the final CSS file, and instead rely on Compass or another similar SCSS compiler to write the final CSS files. The SCSS will still be written by Omega since it is required in order to translate the variable changes for layout/colors/etc. <strong>If you are unsure, leave this option enabled.</strong>'),
  '#default_value' => isset($compile_scss) ? $compile_scss : TRUE,
  '#group' => 'options',
];

$show_compile_warning = theme_get_setting('show_compile_warning', $theme);
$form['show_compile_warning'] = [
  '#type' => 'checkbox',
  '#title' => t('Show SCSS Compile Warning'),
  '#description' => t('If you have selected to disable the above <strong>Compile SCSS Directly</strong> option, a warning will be showed when you <strong>Save & Update Styles</strong>. Unchecking this option will turn off that warning.</strong>'),
  '#default_value' => isset($show_compile_warning) ? $show_compile_warning : TRUE,
  '#group' => 'options',
];

$form['force_scss_compile'] = [
  '#type' => 'checkbox',
  '#title' => t('Force SCSS Compile'),
  '#description' => t('By default, if you have not updated the layout grid variables, Omega does a check between omega.layout.YOUR-LAYOUT.yml and omega.layout.YOUR-LAYOUT.generated.yml. If the values have not been updated since the last time the SCSS was generated, it will skip generation. This setting allows you to force the compile. Most useful when updates to Omega change the default SCSS formatting of the layout files.'),
  '#default_value' => FALSE,
  '#group' => 'options',
];

$enable_omega_badge = theme_get_setting('enable_omega_badge', $theme);
$form['enable_omega_badge'] = [
  '#type' => 'checkbox',
  '#title' => t('Enable the "I Heart Omega 5" link'),
  '#description' => t('This feature will add an awesome little link that proudly shows your support for <a href="http://drupal.org/project/omega">Omega</a> and links to the project page. It will look for common locations like "Footer Links" or the "Powered by Drupal" block to place a link/graphic. Currently only alters the default Powered By block. <em>Caches must be cleared after this setting is changed to alter the block layout.</em>'),
  '#default_value' => isset($enable_omega_badge) ? $enable_omega_badge : TRUE,
  '#group' => 'options',
];
