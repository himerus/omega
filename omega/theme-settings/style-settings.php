<?php

/**
 * @file
 * Implements custom theme settings for Omega Five related to JS/CSS Libraries.
 */

use Drupal\omega\Style\OmegaStyle;

// Get the theme name we are editing.
$theme = \Drupal::theme()->getActiveTheme()->getName();

$form['styles'] = [
  '#type' => 'details',
  '#attributes' => ['class' => ['styles']],
  '#title' => t('CSS/JS Libraries'),
  '#weight' => -899,
  '#group' => 'omega',
  '#open' => FALSE,
  '#tree' => TRUE,
];
$form['styles']['styles_info'] = [
  '#markup' => '<div class="messages messages--status omega-styles-info">Please note that while the ability to enable/disable these libraries is functional that until a stable Drupal 8.x version of Omega is released, the CSS/JS provided by these libraries will be greatly altered. This is an effort to provide some absolutely solid, clean default styles for a large variety of default site elements.</div>',
  '#weight' => -9999,
];
$form['styles']['styles_info']['#markup'] .= '<div class="messages messages--warning omega-styles-info">By enabling libraries in this section, you can greatly alter the visual appearance of your site. Many libraries contain simple CSS enhancements, while others include both CSS and JavaScript to alter/enhance your theme. If you are building a highly customized subtheme of Omega, you will likely turn most of these off. However, if you are creating a theme with minimal customization, leaving them enabled will provide a decent set of core styles and behaviors.</div>';

$toggleLibraries = OmegaStyle::getOptionalLibraries($theme);

$form['styles']['styles_toggle'] = [
  '#markup' => '<p><a href="#" class="toggle-styles-on">Select All</a> | <a href="#" class="toggle-styles-off">Select None</a></p>',
  '#weight' => -999,
];

foreach ($toggleLibraries as $id => $data) {
  // Let's organize all the libraries a bit.
  $libraryParts = explode('/', $id);
  $themeProvidingLibrary = $libraryParts[0];

  // Let's create a wrapper for this theme's libraries if it hasn't been
  // made in a previous loop this creates a collapsible element for each
  // theme/parent theme to make the form more usable for themes with sub-sub,
  // sub-sub-sub or further subtheming needs.
  if (!isset($form['styles'][$themeProvidingLibrary])) {
    $form['styles'][$themeProvidingLibrary] = [
      '#type' => 'details',
      '#attributes' => ['class' => [$themeProvidingLibrary]],
      '#title' => $themeProvidingLibrary . ' Libraries',
      '#weight' => -999,
      '#open' => TRUE,
    ];
  }

  // Let's create the checkbox that will enable/disable the library.
  $form['styles'][$themeProvidingLibrary][$id] = [
    '#type' => 'checkbox',
    '#title' => t('@title <small>(@library)</small>', ['@title' => $data['title'], '@library' => $data['library']]),
    '#description' => t('@description', ['@description' => $data['description']]),
    '#default_value' => $data['status'],
    '#tree' => TRUE,
    '#parents' => ['styles', $id],
  ];

  if ($data['allow_disable'] === FALSE) {
    // This library has been set to NOT be allowed to be disabled
    // by any subthemes. Set some things accordingly.
    $form['styles'][$themeProvidingLibrary][$id]['#default_value'] = 1;
    $form['styles'][$themeProvidingLibrary][$id]['#disabled'] = TRUE;
    $form['styles'][$themeProvidingLibrary][$id]['#required'] = TRUE;
  }
}
