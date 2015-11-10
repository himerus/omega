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

if ($theme == 'omega') {
  // on Omega core we completely disable access to the settings
  $form['core']['#access'] = FALSE;
  $form['theme_settings']['#access'] = FALSE;
  $form['logo']['#access'] = FALSE;
  $form['favicon']['#access'] = FALSE;
}
