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
    '#default_value' => omega_theme_get_setting('omega_rebuild_theme_registry', FALSE),
  );

  $element['omega_rebuild_aggregates'] = array(
    '#type' => 'checkbox',
    '#title' => t('Rebuild CSS and JS aggregates on every page load'),
    '#description' => t('This can be useful for debugging your CSS or JS with activated aggregation. Note: This has a high (negative) performance impact and has to be deactivated once your website goes into production.'),
    '#default_value' => omega_theme_get_setting('omega_rebuild_aggregates', FALSE),
  );

  $element['omega_browser_width_indicator'] = array(
    '#type' => 'checkbox',
    '#title' => t('Browser width indicator'),
    '#description' => t('Adds a small box at the bottom of the browser window that displays the current width of the browser window. This can be very useful when writing media queries for a responsive website.'),
    '#default_value' => omega_theme_get_setting('omega_browser_width_indicator', FALSE),
  );

  $regions = system_region_list($GLOBALS['theme_key'], REGIONS_VISIBLE);
  $regions = module_exists('dashboard') ? array_diff_key($regions, array_flip(dashboard_regions())) : $regions;
  if ($regions) {
    $element['omega_demo_regions'] = array(
      '#type' => 'checkbox',
      '#title' => t('Region demo mode'),
      '#description' => t('Forces all regions to be rendered, regardless of whether they are empty or not places a label with their name on them.'),
      '#default_value' => omega_theme_get_setting('omega_demo_regions', TRUE),
    );

    $element['omega_demo_regions_list'] = array(
      '#type' => 'fieldset',
      '#title' => t('Select demo regions'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#states' => array(
        'visible' => array(
          'input[name="omega_demo_regions"]' => array('checked' => TRUE),
        ),
      ),
    );

    $element['omega_demo_regions_list']['omega_demo_regions_list'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Demo regions'),
      '#options' => $regions,
      '#default_value' => omega_theme_get_setting('omega_demo_regions_list', array_keys($regions)),
    );
  }

  $form['#submit'][] = 'omega_extension_development_settings_form_submit';

  return $element;
}

/**
 * Form submit handler for the theme settings form to clean up stale values.
 */
function omega_extension_development_settings_form_submit($form, &$form_state) {
  if (empty($form_state['values']['omega_demo_regions'])) {
    unset($form_state['values']['omega_demo_regions_list']);
  }
}
