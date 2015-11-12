<?php
require_once('omega-functions.php');

//use Drupal\Core\Config\Config;

// Include Breakpoint Functionality
//use Drupal\breakpoint;

use Drupal\omega\phpsass\SassParser;
use Drupal\omega\phpsass\SassFile;

/**
 * Implementation of hook_form_system_theme_settings_alter()
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 *
 * @param $form_state
 *   A keyed array containing the current state of the form.
 */
function omega_form_system_theme_settings_alter(&$form, &$form_state) {
  
  //dsm($form);
  //dsm($form_state);
  
  // currently edited in root .htaccess :( very bad. need a solution for this
  //krumo(ini_get('max_input_vars'));
  $build_info = $form_state->getBuildInfo();
  
  // Get the theme name we are editing
  $theme = $build_info['args'][0];
  // get a list of themes
  $themes = \Drupal::service('theme_handler')->listInfo();
  // get the default settings for the current theme
  $themeSettings = $themes[$theme];
  
  
  // check for ajax update of default layout, or use default theme setting
  $defaultLayout = isset($form_state->values['default_layout']) ? $form_state->values['default_layout'] : theme_get_setting('default_layout', $theme);
  $edit_this_layout = isset($form_state->values['edit_this_layout']) ? $form_state->values['edit_this_layout'] : theme_get_setting('default_layout', $theme);
  //$defaultLayout = theme_get_setting('default_layout', $theme);
  
  $breakpoints = _omega_getActiveBreakpoints($theme);

  $layouts = omega_return_layouts($theme);
  //krumo($layouts);
  // pull an array of "region groups" based on the "all" media query that should always be present
  $region_groups = $layouts[$defaultLayout]['region_groups']['all'];
  //dsm($region_groups);
  $theme_regions = $themeSettings->info['regions'];
  
  // add in custom CSS/JS for Omega administration
  $form['#attached']['library'][] = 'omega/omega_admin';  
  
  // include the introduction message(s)
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/omega-intro.php');
  
  // include the adjustments to core system theme settings
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/core-settings.php');
  
  // Custom settings in Vertical Tabs container
  $form['omega'] = array(
    '#type' => 'vertical_tabs',
    '#attributes' => array('class' => array('entity-meta')),
    '#weight' => -999,
    '#default_tab' => 'edit-layouts',
  );
  
  // include the adjustments to core system theme settings
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/general-settings.php');
  
  // include the ability to enable/disable custom Omega stylesheets/javascripts
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/style-settings.php');
  
  // include the ability to customize various scss variables to provide basic style adjustments
  // include_once(drupal_get_path('theme', 'omega') . '/theme-settings/scss-settings.php');
  
  // include the ability to debug various theme development elements
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/debug-settings.php');
  
  // include the layout configuration options
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/layout-config.php');
  
  // include the layout builder interface
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/layout-settings.php');
  
  // Change the text for default submit button
  $form['actions']['submit']['#value'] = t('Save Settings');
  // Hide the default submit button if 'export new subtheme' option is enabled
  $form['actions']['submit']['#states'] = array(
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => TRUE),
    ),
  );
  
  // add appropriate validate & submit hooks
  $form['#validate'][] = 'omega_theme_settings_validate';
  $form['#submit'][] = 'omega_theme_settings_submit';
  
  //dsm($form['#submit']);
  
  // gather the default submit callback so we can add it to our custom one
  $defaultSubmit = $build_info['callback_object'];
  
  //dsm($build_info);
  // copy the default submit button/handler
  $form['actions']['submit_layout'] = $form['actions']['submit'];
  // update the text for the new button
  $form['actions']['submit_layout']['#value'] = t('Save Settings & Layout');
  // update the submit handlers
  
  
  
  
  
  // add in the default Omega submit handler that handles the layout data
  //$form['actions']['submit_layout']['#submit'][] = 'omega_theme_settings_submit';
  // add in the layout generation handler that actually creates/updates the SCSS/CSS files
  $form['actions']['submit_layout']['#submit'][] = 'omega_theme_layout_build_submit';
  // add in default submit handler
  $form['actions']['submit_layout']['#submit'][] = '::submitForm';
  // define the visibility of the custom submit button
  // only when enable Omega.gs layout is enabled 
  // AND
  // only when export new subtheme is disabled
  $form['actions']['submit_layout']['#states'] = array(
    'visible' => array(
     ':input[name="enable_omegags_layout"]' => array('checked' => TRUE),
     // once export ability is included, will need to enable/test this again
     //':input[name="export_new_subtheme"]' => array('checked' => FALSE),     
    ),
  );
  
  
  // include the ability to export changes made in the Omega settings form to a new
  // subtheme, rather than saving the current settings
  // include_once(drupal_get_path('theme', 'omega') . '/theme-settings/export-settings.php');
  
/*
  $form['actions']['generate_subtheme'] = $form['actions']['submit'];
  $form['actions']['generate_subtheme']['#value'] = t('Export as Subtheme');
  
  $form['actions']['generate_subtheme']['#submit'] = array('omega_theme_generate_submit');
  $form['actions']['generate_subtheme']['#validate'] = array('omega_theme_generate_validate');
  
  // show export only when appropriate
  $form['actions']['generate_subtheme']['#states'] = array(
    // Hide the submit buttons appropriately
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => FALSE),
    ),
  );
*/
  
  //dsm($form);
  //dsm($form_state);
  
  
  
  
  
  
  
}

/**
 * @todo
 * Function to check machine name for generated theme to ensure it is available
 */
function omega_theme_exists($machine) {
  return true;
}

function omega_theme_settings_validate(&$form, &$form_state) {
  //drupal_set_message(t('Running <strong>omega_theme_settings_validate()</strong>'));
}

function omega_theme_settings_submit(&$form, &$form_state) {
  //drupal_set_message(t('Running <strong>omega_theme_settings_submit()</strong>'));
  //dsm($form['#submit']);
  //dsm($form_state->getSubmitHandlers());
  $build_info = $form_state->getBuildInfo();
  // Get the theme name.
  $theme = $build_info['args'][0];
  // get all the values of the submitted form
  $values = $form_state->getValues();
  //dsm($values);
  
  // grab the value of layouts so we can update the $theme.layout.$layout_name
  $layouts = $values['layouts'];
  
  // unset the layout variable so it's not stored in $theme.settings
  // instead an empty array will replace the value in $theme.settings
  $form_state->setValue('layouts', array());

  // FOREACH $layouts we need to run some operations that will hopefully accomplish the following:
  // 1.) Check to see if there are differences in the layout stored and layout submitted
  // 2.) Write the changes to $theme.layout.$layout in the config table
  // 3.) Write the changes to SCSS and generate CSS.
  // 4.) Ensure this will work with multiple layouts passed (current) OR
  //     when individual layouts are passed (hopefully) via ajax
 
  foreach ($layouts AS $layout_id => $layout) {
    
    // Save $layout to the database
    _omega_save_database_layout($layout, $layout_id, $theme);
  }
}

function omega_theme_layout_build_validate(&$form, &$form_state) {
  //drupal_set_message(t('Running <strong>omega_theme_layout_build_validate()</strong>'));
  $build_info = $form_state->getBuildInfo();
  // Get the theme name.
  $theme = $build_info['args'][0];
  // get all the values of the submitted form
  $values = $form_state->getValues();
}
function omega_theme_layout_build_submit(&$form, &$form_state) {
  //drupal_set_message(t('Running <strong>omega_theme_layout_build_submit()</strong>'));
  //dsm($form_state->getSubmitHandlers());
  $build_info = $form_state->getBuildInfo();
  // Get the theme name.
  $theme = $build_info['args'][0];
  // get all the values of the submitted form
  $values = $form_state->getValues();
  
  // grab the value of layouts so we can update the $theme.layout.$layout_name
  $layouts = $values['layouts'];
  
  // unset the layout variable so it's not stored in $theme.settings
  // instead an empty array will replace the value in $theme.settings
  $form_state->setValue('layouts', array());
  
  // Options for phpsass compiler. Defaults in SassParser.php
  $options = array(
    'style' => 'nested',
    'cache' => FALSE,
    'syntax' => 'scss',
    'debug' => TRUE,
  );

  // FOREACH $layouts we need to run some operations that will hopefully accomplish the following:
  // 1.) @todo Check to see if there are differences in the layout stored and layout submitted
  // 2.) @todone Write the changes to $theme.layout.$layout in the config table
  // 3.) @todone Write the changes to SCSS and generate CSS.
  // 4.) @todone Ensure this will work with multiple layouts passed (current) OR
  //     when individual layouts are passed (hopefully) via ajax
 
  foreach ($layouts AS $layout_id => $layout) {
    // Save $layout to the database
    _omega_save_database_layout($layout, $layout_id, $theme);
    // generate the SCSS from the layout data
    $scss = _omega_compile_layout_sass($layout, $layout_id, $theme, $options);
    // generate the CSS from the SCSS created above
    $css = _omega_compile_layout_css($scss, $options);
    // save the SCSS and CSS files to the theme's filesystem
    _omega_save_layout_files($scss, $css, $theme, $layout_id);
  }
}

function omega_theme_generate_validate(&$form, &$form_state) {
  drupal_set_message(t('Running <strong>omega_theme_generate_validate()</strong>'));
  $build_info = $form_state->getBuildInfo();
  // Get the theme name.
  $theme = $build_info['args'][0];
  // get all the values of the submitted form
  $values = $form_state->getValues();
}
function omega_theme_generate_submit(&$form, &$form_state) {
  drupal_set_message(t('Running <strong>omega_theme_generate_submit()</strong>'));
  $build_info = $form_state->getBuildInfo();
  // Get the theme name.
  $theme = $build_info['args'][0];
  // get all the values of the submitted form
  $values = $form_state->getValues();
}
