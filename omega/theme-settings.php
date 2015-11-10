<?php
require_once('omega-functions.php');

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;

// Include Breakpoint Functionality
use Drupal\breakpoint;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\system\Form\ThemeSettingsForm;
use Drupal\omega\savelayout\SaveLayout;

//use Drupal\responsive_image\Entity\ResponsiveImageMapping;

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
  global $base_path;
  
  $build_info = $form_state->getBuildInfo();
  
  // Get the theme name we are editing
  $theme = $build_info['args'][0];
  // get a list of themes
  $themes = \Drupal::service('theme_handler')->listInfo();
  //kint($themes);
  // get the default settings for the current theme
  $themeSettings = $themes[$theme];
  //kint($themeSettings);
  // Get this themes config settings
  //$config = \Drupal::config($theme . '.settings')->get('settings');
  //kint($config);
  
  //kint(\Drupal::service('breakpoint.manager')->getGroups());
  
  
  $defaultLayout = theme_get_setting('default_layout', $theme);
  
  $breakpoints = _omega_getActiveBreakpoints($theme);
  //kint($breakpoints);
  
  
  
  
  
  
  
  
  
  $layouts = theme_get_setting('layouts', $theme);
  //kint($layouts);

  // pull an array of "region groups" based on the "all" media query that should always be present
  $region_groups = $layouts[$defaultLayout]['region_groups']['all'];
  //dsm($region_groups);
  $theme_regions = $themeSettings->info['regions'];
  
  // add in custom JS for Omega administration
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
  
  // include the ability to enable/disable custom Omega stylesheets
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/style-settings.php');
  
  // include the ability to customize various scss variables to provide basic style adjustments
  // include_once(drupal_get_path('theme', 'omega') . '/theme-settings/scss-settings.php');
  
  // include the ability to debug various theme development elements
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/debug-settings.php');
  
  
  // include the layout manager interface
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/layout-settings.php');
  
  
  
  //dsm($breakpointGroupId);
  //dsm($breakpointGroup);
  //dsm($breakpoints);
  //dsm($form);
  
  
  
  
  
  
  
  // Change the text for default submit button
  $form['actions']['submit']['#value'] = t('Save Settings');
  // Hide the default submit button if 'export new subtheme' option is enabled
  $form['actions']['submit']['#states'] = array(
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => TRUE),
    ),
  );
  
  // gather the default submit callback so we can add it to our custom one
  $defaultSubmit = $build_info['callback_object'];
  // copy the default submit button/handler
  $form['actions']['submit_layout'] = $form['actions']['submit'];
  // update the text for the new button
  $form['actions']['submit_layout']['#value'] = t('Save Settings & Layout');
  // update the submit handlers
  $form['actions']['submit_layout']['#submit'][] = array($defaultSubmit, 'submitForm');
  // add in custom submit handler
  $form['actions']['submit_layout']['#submit'][] = 'omega_theme_settings_submit';
  
  // define the visibility of the custom submit button
  // only when enable Omega.gs layout is enabled 
  // AND
  // only when export new subtheme is disabled
  $form['actions']['submit_layout']['#states'] = array(
    'visible' => array(
     ':input[name="enable_omegags_layout"]' => array('checked' => TRUE),
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
function omega_theme_exists($machine) {
  return true;
}

function omega_theme_settings_validate(&$form, &$form_state) {
  //dsm($form);
  //dsm($form_state);
  dsm('omega_form_system_theme_settings_validate');

  //return false;
}

function omega_theme_settings_submit(&$form, &$form_state) {
  // Get the theme name.
  $theme = $form_state->build_info['args'][0];
  
  $values = $form_state->values;
  $layout = $values['layouts'];
  //dsm($values);
  // Options for phpsass compiler. Defaults in SassParser.php
  $options = array(
    'style' => 'nested',
    'cache' => FALSE,
    'syntax' => 'scss',
    'debug' => TRUE,
    //'debug_info' => $debug,
    //'load_paths' => array(dirname($file['data'])),
    //'filename' => $file['data'],
    //'load_path_functions' => array('sassy_load_callback'),
    //'functions' => sassy_get_functions(),
    'callbacks' => array(
      //'warn' => $watchdog ? 'sassy_watchdog_warn' : NULL,
      //'debug' => $watchdog ? 'sassy_watchdog_debug' : NULL,
    ),
  );
 
  // Execute the compiler.
  $parser = new SassParser($options);
  // create CSS from SCSS
  $scss = _omega_compile_layout_sass($layout, $theme, $options);
  //dsm($scss);

  $css = _omega_render_layout_css($scss, $options);
  //dsm($css);
  
  _omega_save_layout_files($scss, $css, $theme);
  //dsm($form_state['values']);
}


function omega_theme_generate_validate(&$form, &$form_state) {
  dsm('function omega_theme_generate_validate() {}');
  //dsm($form);
  //dsm($form_state['values']);
}
function omega_theme_generate_submit(&$form, &$form_state) {
  dsm('function omega_theme_generate_submit() {}');
  //dsm($form);
  //dsm($form_state['values']);
}
