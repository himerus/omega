<?php
$form['debug'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('debug')),
  '#title' => t('Debugging & Development'),
  '#weight' => -699,
  '#group' => 'omega',
  //'#open' => TRUE,
);

$form['block_demo_mode'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable region demo mode <small>(global setting)</small>'),
  '#description' => t('Display demonstration blocks in each theme region to aid in theme development and configuration. When this setting is enabled, ALL site visitors will see the demo blocks. <br /><strong>This should never be enabled on a live site.</strong>'),
  '#default_value' => theme_get_setting('block_demo_mode', $theme),
  '#group' => 'debug',
);

$form['screen_demo_indicator'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable screen size indicator <small>(global setting)</small>'),
  '#description' => t('Display data about the screen size, current media query, etc. When this setting is enabled, ALL site visitors will see the overlay data. <br /><strong>This should never be enabled on a live site.</strong>'),
  '#default_value' => theme_get_setting('screen_demo_indicator', $theme),
  '#group' => 'debug',
);