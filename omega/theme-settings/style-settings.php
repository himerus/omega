<?php

$form['styles'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('styles')),
  '#title' => t('Optional Libraries'),
  '#weight' => -899,
  '#group' => 'omega',
  '#open' => FALSE,
  '#tree' => TRUE,
);
$form['styles']['styles_info'] = array(
  '#prefix' => '<div class="messages messages--warning omega-styles-info">',
  '#markup' => '',
  '#suffix' => '</div>',
  '#weight' => -9999,
);

$form['styles']['styles_info']['#markup'] .= '<p>By selecting or unselecting styles in this section, you can greatly alter the visual appearance of your site.</p>';
$toggleCSS = _omega_optional_css($theme);

$form['styles']['styles_toggle'] = array(
  //'#prefix' => '<div class="messages messages--warning omega-styles-info">',
  '#markup' => '<p><a href="#" class="toggle-styles-on">Select All</a> | <a href="#" class="toggle-styles-off">Select None</a></p>',
  //'#suffix' => '</div>',
  '#weight' => -999,
);

foreach($toggleCSS as $id => $data) {
  $form['styles'][$id] = array(
    '#type' => 'checkbox',
    '#title' => t($data['title'] . ' <small>(' . $data['library'] . ')</small>'),
    '#description' => t($data['description']),
    '#default_value' => $data['status'],
    '#group' => 'styles',
    
  );
}