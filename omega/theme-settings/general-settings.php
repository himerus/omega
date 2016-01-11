<?php
// Vertical tab sections
$form['options'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('debug')),
  '#title' => t('Default Options'),
  '#weight' => -999,
  '#group' => 'omega',
  '#open' => FALSE,
);

/*
$enable_advanced_layout_controls = theme_get_setting('enable_advanced_layout_controls', $theme);
$form['enable_advanced_layout_controls'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable Advanced Layout Controls (BETA)'),
  '#description' => t('This feature will enable various advanced features in the theme settings form like jQuery UI Sliders for adjusting width, prefix, suffix on regions, etc. The advanced features can be problematic on mobile or smaller screens. Turning this feature off will revert to the simplest form.'),
  '#default_value' => isset($enable_advanced_layout_controls) ? $enable_advanced_layout_controls : TRUE,
  '#group' => 'options',
);
*/

$enable_backups = theme_get_setting('enable_backups', $theme);
$form['enable_backups'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable Backups (NON-Functional)'),
  '#description' => t('Since this form has the ability to regenerate SCSS and CSS files on the fly, turning on this backup feature will create a copy of layout.scss, layout.css, and THEME.settings.yml file before overwriting any data. These backups will be stored in the default files directory under <em>public://omega/layout/backups</em>.'),
  '#default_value' => isset($enable_backups) ? $enable_backups : TRUE,
  '#group' => 'options',
);


/*
$enable_omega_badge = theme_get_setting('enable_omega_badge', $theme);
$form['enable_omega_badge'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable the "I Heart Omega 5" link'),
  '#description' => t('This feature will add an awesome little link that proudly shows your support for <a href="http://drupal.org/project/omega">Omega</a> and links to the project page. It will look for common locations like "Footer Links" or the "Powered by Drupal" block to place a link/graphic.'),
  '#default_value' => isset($enable_omega_badge) ? $enable_omega_badge : TRUE,
  '#group' => 'options',
);
*/