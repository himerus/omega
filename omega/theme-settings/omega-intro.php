<?php



$toggle_omega_intro = theme_get_setting('omega_toggle_intro', $theme);
$welcome_status = $toggle_omega_intro ? $toggle_omega_intro : FALSE;


$form['welcome'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('welcome', 'omega-help')),
  '#title' => t('Welcome to Omega Five'),
  '#weight' => -1000,
  '#open' => $welcome_status,
  '#tree' => FALSE,
);

$screenshot = base_path() . drupal_get_path('theme', 'omega') . '/screenshot.png';

$form['welcome']['omega5'] = array(
  '#prefix' => '<div class="omega-welcome clearfix">',
  '#markup' => '<img class="screeny" src="'. $screenshot .'" />',
  '#suffix' => '</div>',
  '#weight' => -9999,
);

$form['welcome']['omega5']['#markup'] .= t('<h3>Omega Five <small>(8.x-5.x)</small></h3>');
$form['welcome']['omega5']['#markup'] .= t('<p><strong>Project Page</strong> - <a href="http://drupal.org/project/omega" target="_blank">drupal.org/project/omega</a>');
$form['welcome']['omega5']['#markup'] .= t('<p><strong>Issue Queue</strong> - <a href="http://drupal.org/project/issues/omega" target="_blank">drupal.org/project/issues/omega</a>');
$form['welcome']['omega5']['#markup'] .= t('<p>Omega Five brings back the simplicity in layout management that was found in Omega 3.x and vastly popular among theme builders of all abilities. </p>');
$form['welcome']['omega5']['#markup'] .= t('<p>Most settings in the <strong>Omega Configuration Interface</strong> are well documented inline. For additional information and links, visit the project page listed above.</p>');

$form['omega_toggle_intro'] = array(
  '#type' => 'checkbox',
  '#title' => t('Show this message/introduction by default'),
  '#description' => t(''),
  '#default_value' => $welcome_status,
  '#group' => 'welcome',
);