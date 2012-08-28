<?php

/**
 * @file
 * Theme settings file for the Omega base theme.
 */

require_once dirname(__FILE__) . '/template.php';

/**
 * Implements hook_form_FORM_alter().
 */
function omega_form_system_theme_settings_alter(&$form, $form_state, $form_id = NULL) {
  // General "alters" use a form id. Settings should not be set here. The only
  // thing useful about this is if you need to alter the form for the running
  // theme and *not* the the me setting. @see http://drupal.org/node/943212
  if (isset($form_id)) {
    return;
  }

  // Include the template.php and theme-settings.php files for all the themes in
  // the theme trail.
  foreach (omega_theme_trail() as $theme => $name) {
    $path = drupal_get_path('theme', $theme);

    $filename = DRUPAL_ROOT . '/' . $path . '/template.php';
    if (file_exists($filename)) {
      require_once $filename;
    }

    $filename = DRUPAL_ROOT . '/' . $path . '/theme-settings.php';
    if (file_exists($filename)) {
      require_once $filename;
    }
  }

  // Get the admin theme so we can set a class for styling this form.
  $admin = drupal_html_class(variable_get('admin_theme', $GLOBALS['theme']));
  $form['#prefix'] = '<div class="admin-theme-' . $admin . '">';
  $form['#suffix'] = '</div>';

  // Add some custom styling and functionality to our theme settings form.
  $form['#attached']['css'][] = drupal_get_path('theme', 'omega') . '/css/omega.admin.css';
  $form['#attached']['js'][] = drupal_get_path('theme', 'omega') . '/js/omega.admin.js';

  // Collapse all the core theme settings tabs in order to have the form actions
  // visible all the time without having to scroll.
  foreach (element_children($form) as $key) {
    if ($form[$key]['#type'] == 'fieldset')  {
      $form[$key]['#collapsible'] = TRUE;
      $form[$key]['#collapsed'] = TRUE;
    }
  }

  if ($extensions = omega_extensions()) {
    $form['omega'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => -10,
    );

    $form['omega_extensions'] = array(
      '#type' => 'fieldset',
      '#title' => t('Extensions'),
      '#description' => t('Enable or disable certain theme extensions.'),
      '#collapsible' => TRUE,
      '#weight' => -9,
    );

    // Load the theme settings for all enabled extensions.
    foreach ($extensions as $extension => $info) {
      $form['omega_extensions']['omega_toggle_extension_' . $extension] = array(
        '#type' => 'checkbox',
        '#title' => $info['info']['name'],
        '#default_value' => theme_get_setting('omega_toggle_extension_' . $extension),
      );

      $element = array();

      // Load the implementation for this extensions and invoke the according
      // hook.
      $file = drupal_get_path('theme', $info['theme']) . '/includes/' . $extension . '/' . $extension . '.settings.inc';
      if (is_file($file)) {
        require_once $file;
      }

      $function = $info['theme'] . '_extension_' . $extension . '_settings_form';
      if (function_exists($function)) {
        // By default, each extension resides in a vertical tab
        $enabled = theme_get_setting('omega_toggle_extension_' . $extension);
        $element = $function($element, $form, $form_state) + array(
          '#type' => 'fieldset',
          '#title' => $info['info']['name'] . (!$enabled ? ' (' . t('disabled') . ')' : ''),
          '#disabled' => !$enabled,
        );
      }

      drupal_alter('extension_' . $extension . '_settings_form', $element, $form, $form_state);

      if (element_children($element)) {
        // Append the extension form to the theme settings form if it has any
        // children.
        $form['omega']['omega_' . $extension] = $element;
      }
    }
  }

  // Custom option for toggling the main content blog on the front page.
  $form['theme_settings']['omega_toggle_front_page_content'] = array(
    '#type' => 'checkbox',
    '#title' => t('Front page content'),
    '#description' => t('Uncheck this checkbox in order to hide the main content block on the front page.'),
    '#default_value' => theme_get_setting('omega_toggle_front_page_content'),
  );

  // We need a custom form submit handler for processing some of the values.
  $form['#submit'][] = 'omega_theme_settings_form_submit';
}

/**
 * Form submit handler for the theme settings form.
 */
function omega_theme_settings_form_submit($form, &$form_state) {
  // Clear the theme settings cache.
  cache_clear_all('theme_settings:' . $form_state['build_info']['args'][0], 'cache');

  // Rebuild the theme data. This is required for the system info altering code
  // to run once again after the theme settings have been changed.
  system_rebuild_theme_data();

  // Rebuild the theme registry. This has quite a performance impact but since
  // this only happens once after we (re-)saved the theme settings this is fine.
  // Also, this is actually required because we are caching certain things in
  // the theme registry.
  drupal_theme_rebuild();

  // This is a relict from the vertical tabs and should be removed so it doesn't
  // end up in the theme settings array.
  unset($form_state['values']['omega__active_tab']);
}
