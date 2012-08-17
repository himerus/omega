<?php

/**
 * @file
 * Contains the theme settings form elements for the development extension.
 */

/**
 * Implements hook_extension_EXTENSION_theme_settings_form_alter().
 */
function omega_extension_development_settings_form($element, &$form, $form_state) {
  $element['omega_rebuild_theme_registry'] = array(
    '#type' => 'checkbox',
    '#title' => t('Rebuild the theme registry on every page load'),
    '#description' => t('While creating new templates and theme overrides the theme registry needs to be rebuilt. Note: This has a high (negative) performance impact and has to be deactivated once your website goes into production.'),
    '#default_value' => theme_get_setting('omega_rebuild_theme_registry'),
  );

  $element['omega_rebuild_aggregates'] = array(
    '#type' => 'checkbox',
    '#title' => t('Rebuild CSS and JS aggregates on every page load'),
    '#description' => t('This can be useful for debugging your CSS or JS with activated aggregation. Note: This has a high (negative) performance impact and has to be deactivated once your website goes into production.'),
    '#default_value' => theme_get_setting('omega_rebuild_aggregates'),
  );

  $element['omega_browser_width_indicator'] = array(
    '#type' => 'checkbox',
    '#title' => t('Browser width indicator'),
    '#description' => t('Adds a small box at the bottom of the browser window that displays the current width of the browser window. This can be very useful when writing media queries for a responsive website.'),
    '#default_value' => theme_get_setting('omega_browser_width_indicator'),
  );

  $element['omega_dummy_blocks'] = array(
    '#type' => 'checkbox',
    '#title' => t('Dummy blocks'),
    '#description' => t('Places dummy blocks in all (otherwise empty) regions.'),
    '#default_value' => theme_get_setting('omega_dummy_blocks'),
  );

  return $element;
}
