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
  '#markup' => '<div class="messages messages--status omega-styles-info">Please note that while the ability to enable/disable these libraries is functional that until a stable Drupal 8.x version of Omega is released, the CSS/JS provided by these libraries will be greatly altered. This is an effort to provide some absolutely solid, clean default styles for a large variety of default site elements.</div>',
  '#weight' => -9999,
);
$form['styles']['styles_info']['#markup'] .= '<div class="messages messages--warning omega-styles-info">By enabling libraries in this section, you can greatly alter the visual appearance of your site. Many libraries contain simple CSS enhancements, while others include both CSS and JavaScript to alter/enhance your theme. If you are building a highly customized subtheme of Omega, you will likely turn most of these off. However, if you are creating a theme with minimal customization, leaving them enabled will provide a decent set of core styles and behaviors.</div>';

$toggleCSS = _omega_optional_libraries($theme);

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