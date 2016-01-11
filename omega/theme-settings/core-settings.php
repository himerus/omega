<?php

// move the default theme settings to our custom vertical tabs for core theme settings
$form['core'] = array(
  '#type' => 'vertical_tabs',
  '#attributes' => array('class' => array('entity-meta')),
  '#weight' => -899,
);

$form['theme_settings']['#group'] = 'core';
$form['logo']['#group'] = 'core';
$form['favicon']['#group'] = 'core';

$form['theme_settings']['#open'] = FALSE;
$form['logo']['#open'] = FALSE;
$form['favicon']['#open'] = FALSE;

// disable the default theme settings for both Omega
// and a theme where "force export" is turned on.
if ($theme == 'omega' || $force_theme_export) {
  $form['core']['#access'] = FALSE;
  $form['theme_settings']['#access'] = FALSE;
  $form['logo']['#access'] = FALSE;
  $form['favicon']['#access'] = FALSE;
}
