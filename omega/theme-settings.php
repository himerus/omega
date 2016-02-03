<?php

use Drupal\omega\Theme\OmegaInfo;
use Drupal\omega\Theme\OmegaSettingsInfo;
  
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;

use Drupal\system\Controller\ThemeController;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\HttpFoundation\Request;
  
require_once(drupal_get_path('theme', 'omega') . '/omega-functions.php');
require_once(drupal_get_path('theme', 'omega') . '/omega-functions--admin.php');
require_once(drupal_get_path('theme', 'omega') . '/theme-settings/theme-settings--export-handlers.php');

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
  
  // add in custom CSS/JS for Omega administration
  $form['#attached']['library'][] = 'omega/omega_admin';  
  // Get the build info for the form
  $build_info = $form_state->getBuildInfo();
  // Get the theme name we are editing
  $theme = \Drupal::theme()->getActiveTheme()->getName();
  // Create Omega Settings Object
  $omegaSettings = new OmegaSettingsInfo($theme);
  // get a list of themes
  $themes = $omegaSettings->themes;
  // get the default settings for the current theme
  $themeSettings = $omegaSettings->getThemeInfo();
  // get the value of 'force_export' from THEME.info.yml
  $force_theme_export = $themeSettings->info['force_export'];
  // get the value of 'inherit_layout' from THEME.info.yml
  $inherit_layout = $themeSettings->info['inherit_layout'];
  // get the value of 'scss_support' from THEME.info.yml
  $scss_support = $themeSettings->info['scss_support'];
  // get all the values of the submitted form
  $values = $form_state->getValues();
  // include the introduction message(s)
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/omega-intro.php');
  // include the adjustments to core system theme settings
  include_once(drupal_get_path('theme', 'omega') . '/theme-settings/core-settings.php');
  
  
  //$test = \Drupal::theme()->getActiveTheme()->getLibrariesOverride();
  //dpm($test);
  //$variables = theme_get_setting('variables', $theme);
  //dpm($variables);
/*
  $library_discovery = \Drupal::service('library.discovery');  
  $testLibrary = $library_discovery->getLibraryByName('omega', 'omega_html_elements');
  // only has original data
  dpm($testLibrary);
  
  $library_discovery_parser = \Drupal::service('library.discovery.parser');  
  $themeLibraries = $library_discovery_parser->buildByExtension('omega');
  dpm($themeLibraries);
*/
  
  if (!$force_theme_export) {
    
    // if we are inheriting the layout, throw a message
    if ($inherit_layout) {
      $form['inherited_layout'] = array(
        '#prefix' => '<div class="messages messages--warning omega-variables-info">',
        '#markup' => '',
        '#suffix' => '</div>',
        '#weight' => -999,
      );
      $layoutProvider = omega_find_layout_provider($theme);
      // update the message value
      $form['inherited_layout']['#markup'] = '<p>This theme is currently inheriting the layout(s) from <strong>' . $layoutProvider . '</strong>, so layout configuration options are not available here. <em>Any changes made to the applicable layouts in the parent theme will be used by this theme.</em> You can edit the layout settings for <strong>' . $layoutProvider . '</strong> <a href="/admin/appearance/settings/' . $layoutProvider . '">here</a>.</p>';
    }
    
    // Custom settings in Vertical Tabs container
    $form['omega'] = array(
      '#type' => 'vertical_tabs',
      '#attributes' => array('class' => array('entity-meta')),
      '#weight' => -999,
      '#default_tab' => 'edit-variables',
      '#states' => array(
        'invisible' => array(
         ':input[name="force_subtheme_creation"]' => array('checked' => TRUE),
        ),
      ),
    );
    
    // include the default omega settings
    include_once(drupal_get_path('theme', 'omega') . '/theme-settings/general-settings.php');
    
    // include the ability to enable/disable custom Omega stylesheets/javascripts
    include_once(drupal_get_path('theme', 'omega') . '/theme-settings/style-settings.php');
    
    if ($scss_support) {
      // include the ability to customize various scss variables to provide basic style adjustments
      include_once(drupal_get_path('theme', 'omega') . '/theme-settings/scss-settings.php');
    }
    // include the ability to debug various theme development elements
    include_once(drupal_get_path('theme', 'omega') . '/theme-settings/debug-settings.php');
    
    // if we aren't inheriting the layout, then add the layout config and builder to the form
    if (!$inherit_layout) {
      
      // check for ajax update of default layout, or use default theme setting
      $defaultLayout = isset($form_state->values['default_layout']) ? $form_state->values['default_layout'] : theme_get_setting('default_layout', $theme);
      $edit_this_layout = isset($form_state->values['edit_this_layout']) ? $form_state->values['edit_this_layout'] : theme_get_setting('default_layout', $theme);
      
      // pull the configuration object for this theme that defines the region groups represented in page.html.twig
      $region_groups = \Drupal::config($theme . '.region_groups')->get();
      $theme_regions = $themeSettings->info['regions'];
      
      // get the layouts available to edit in this theme
      $layouts = omega_return_layouts($theme);
      // include the layout configuration options
      include_once(drupal_get_path('theme', 'omega') . '/theme-settings/layout-config.php');
      
      // include the layout builder interface
      include_once(drupal_get_path('theme', 'omega') . '/theme-settings/layout-settings.php');
    }
    // Change the text for default submit button
    $form['actions']['submit']['#value'] = t('Save');
    // Hide the default submit button if 'export new subtheme' option is enabled
    $form['actions']['submit']['#states'] = array(
      'disabled' => array(
        ':input[name="export[export_new_subtheme]"]' => array('checked' => TRUE),
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
    $form['actions']['submit_layout']['#value'] = t('Save & Update Styles');
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
      ),
      'disabled' => array(
        ':input[name="export[export_new_subtheme]"]' => array('checked' => TRUE),
      ),
    );
  } // END !$force_theme_export
  else {
    // We are forced to export/create a new subtheme. DO IT
    // include the ability to export changes made in the Omega settings form to a new
    // subtheme, rather than saving the current settings
    
    // Make sure Machine Name functionality is attached 
    $form['#attached']['library'][] = 'core/drupal.machine-name';
    // Include the form data for export/subtheme creation
    include_once(drupal_get_path('theme', 'omega') . '/theme-settings/export-settings.php');
    
  
    $form['actions']['generate_subtheme'] = $form['actions']['submit'];
    $form['actions']['generate_subtheme']['#value'] = t('Export Subtheme');
    $form['actions']['generate_subtheme']['#validate'] = array('omega_theme_generate_validate');
    $form['actions']['generate_subtheme']['#submit'] = array('omega_theme_generate_submit');
    
    
    // Change the text for default submit button
    $form['actions']['submit']['#value'] = t('Save');
    // disable the default submit button
    $form['actions']['submit']['#disabled'] = TRUE;
    // hide it
    $form['actions']['submit']['#access'] = FALSE;
    
  }
}

/**
 * @todo
 * Function to check machine name for generated theme to ensure it is available
 */
function omega_theme_exists($machine_name) {
  //dsm($machine_name);
  drupal_set_message('function <strong>omega_theme_exists</strong> called...');
  $themes = \Drupal::service('theme_handler')->rebuildThemeData();
  $result = FALSE;
  if (array_key_exists($machine_name, $themes)) {
    $result = TRUE;
  }
  return $result;
}

function omega_theme_settings_validate(&$form, &$form_state) {
  //drupal_set_message(t('Running <strong>omega_theme_settings_validate()</strong>'));
}

/**
 * Default Omega theme settings submit handler.
 * Currently performs the following operations:
 *  - Saves default theme configurations
 *  - Saves updates to the layouts in the database:
 *    - This is handled by calling _omega_save_database_layout for each layout
 *  - Removes $layouts from the default theme settings config to avoid storing
 *    it all in one massive variable at $theme.settings, and instead relies on 
 *    $theme.layout.$layout_id to contain individual layouts.
 */
function omega_theme_settings_submit(&$form, &$form_state) {
  // get the build info for the form
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

  // FOREACH $layouts we need to run some operations that will hopefully accomplish the following:
  // 1.) @todone Check to see if there are differences in the layout stored and layout submitted
  // 2.) @todone Write the changes to $theme.layout.$layout in the config table
  // 3.) @todone Ensure this will work with multiple layouts passed (current) OR
  //     when individual layouts are passed (hopefully) via ajax
 
  foreach ($layouts AS $layout_id => $layout) {
    // Save $layout to the database
    _omega_save_database_layout($layout, $layout_id, $theme, FALSE);
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

/**
 * Custom Omega theme settings submit handler for full layout generation (SCSS/CSS files).
 * Currently performs the following operations:
 *  - Saves default theme configurations
 *  - Saves updates to the layouts in the database:
 *    - This is handled by calling _omega_save_database_layout for each layout
 *  - Removes $layouts from the default theme settings config to avoid storing
 *    it all in one massive variable at $theme.settings, and instead relies on 
 *    $theme.layout.$layout_id to contain individual layouts.
 *  - Passes $layout to _omega_compile_layout_sass to generate the appropriate SCSS
 *    based on settings provided
 *  - Passes returned SCSS to _omega_compile_css to generate the appropriate CSS
 *  - Passes returned SCSS and CSS to _omega_save_layout_files to generate new SCSS and CSS files
 */
function omega_theme_layout_build_submit(&$form, &$form_state) {
  // get the build info for the form
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

  // FOREACH $layouts we need to run some operations that will hopefully accomplish the following:
  // 1.) @todone Check to see if there are differences in the layout stored and layout submitted
  // 2.) @todone Write the changes to $theme.layout.$layout in the config table
  // 3.) @todo See if $theme.layout.$layout.generated exists and matches submitted values
  //     This will allow us to skip file generation as well for unchanged values
  // 4.) @todone Write the changes to SCSS and generate CSS.
  // 5.) @todone Ensure this will work with multiple layouts passed (current) OR
  //     when individual layouts are passed (hopefully) via ajax
 
  foreach ($layouts AS $layout_id => $layout) {
    // Save $layout to the database and see if we need to regenerate the files
    $generated = _omega_save_database_layout($layout, $layout_id, $theme, TRUE);
    
    // we return either true or false from the _omega_save_database_layout function
    // that tells us that the last value in $theme.layout.$layout_id.generated didn't 
    // match, so we need to rewrite the SCSS/CSS files
    if ($generated) {
      // generate the SCSS from the layout data
      _omega_compile_layout($layout, $layout_id, $theme);
    }  
  }
  
  // grab the value of layouts so we can update the $theme.layout.$layout_name
  $styles = $values['variables'];
  // run function to compile the style-vars.scss file with any updates.
  _omega_update_style_scss($styles, $theme, FALSE);
}

