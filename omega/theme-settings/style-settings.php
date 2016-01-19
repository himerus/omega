<?php

$form['styles'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('styles')),
  '#title' => t('CSS/JS Libraries'),
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

$toggleLibraries = _omega_optional_libraries($theme);

$form['styles']['styles_toggle'] = array(
  //'#prefix' => '<div class="messages messages--warning omega-styles-info">',
  '#markup' => '<p><a href="#" class="toggle-styles-on">Select All</a> | <a href="#" class="toggle-styles-off">Select None</a></p>',
  //'#suffix' => '</div>',
  '#weight' => -999,
);
//dpm($form['styles']);
foreach($toggleLibraries as $id => $data) {
  // let's organize all the libraries a bit
  $libraryParts = explode('/', $id);
  $themeProvidingLibrary = $libraryParts[0];
  //dpm($themeProvidingLibrary);
  
  // let's create a wrapper for this theme's libraries if it hasn't been made in a previous loop
  // this creates a collapsible element for each theme/parent theme to make the form more usable
  // for themes with sub-sub, sub-sub-sub or further subtheming needs.
  if (!isset($form['styles'][$themeProvidingLibrary])) {
    $form['styles'][$themeProvidingLibrary] = array(
      '#type' => 'details',
      '#attributes' => array('class' => array($themeProvidingLibrary)),
      '#title' => $themeProvidingLibrary . ' Libraries',
      '#weight' => -999,
      '#open' => TRUE,
      //'#tree' => FALSE,
    );
  }
  
  
  
  // Let's create the checkbox that will enable/disable the library
  $form['styles'][$themeProvidingLibrary][$id] = array(
    '#type' => 'checkbox',
    '#title' => t($data['title'] . ' <small>(' . $data['library'] . ')</small>'),
    '#description' => t($data['description']),
    '#default_value' => $data['status'],
    //'#group' => $themeProvidingLibrary,
    '#tree' => TRUE,
    '#parents' => array('styles', $id),
  );
  
  if($data['allow_disable'] === FALSE) {
    // this library has been set to NOT be allowed to be disabled by any subthemes.
    // set some things accordingly.
    $form['styles'][$themeProvidingLibrary][$id]['#default_value'] = 1;
    $form['styles'][$themeProvidingLibrary][$id]['#disabled'] = TRUE;
    $form['styles'][$themeProvidingLibrary][$id]['#required'] = TRUE;
  }
}