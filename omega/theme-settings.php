<?php

/**
 * @file
 * Implements custom theme settings for Omega Five.
 */

use Drupal\omega\Theme\OmegaSettingsInfo;
use Drupal\omega\Layout\OmegaLayout;
use Drupal\omega\Style\OmegaStyle;
use Drupal\Core\Form\FormStateInterface;

require_once 'omega-functions.php';
require_once 'omega-functions--admin.php';
require_once 'theme-settings/theme-settings--export-handlers.php';

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function omega_form_system_theme_settings_alter(array &$form, FormStateInterface $form_state) {

  // Add in custom CSS/JS for Omega administration.
  $form['#attached']['library'][] = 'omega/omega_admin';
  // Get the build info for the form.
  $build_info = $form_state->getBuildInfo();
  // Get the theme name we are editing.
  $theme = \Drupal::theme()->getActiveTheme()->getName();
  // Create Omega Settings Object.
  $omegaSettings = new OmegaSettingsInfo($theme);
  // Get a list of themes.
  $themes = $omegaSettings->themes;
  // Get the default settings for the current theme.
  $themeSettings = $omegaSettings->getThemeInfo();
  // Get the value of 'force_export' from THEME.info.yml.
  $force_theme_export = isset($themeSettings->info['force_export']) ? $themeSettings->info['force_export'] : FALSE;
  // Get the value of 'inherit_layout' from THEME.info.yml.
  $inherit_layout = isset($themeSettings->info['inherit_layout']) ? $themeSettings->info['inherit_layout'] : FALSE;
  // Get the value of 'scss_support' from THEME.info.yml.
  $scss_support = isset($themeSettings->info['scss_support']) ? $themeSettings->info['scss_support'] : FALSE;
  // Get all the values of the submitted form.
  $values = $form_state->getValues();
  // Include the introduction message(s).
  include_once 'theme-settings/omega-intro.php';
  // Include the adjustments to core system theme settings.
  include_once 'theme-settings/core-settings.php';

  if (!$force_theme_export) {

    // If we are inheriting the layout, throw a message.
    if ($inherit_layout) {
      $form['inherited_layout'] = [
        '#prefix' => '<div class="messages messages--warning omega-variables-info">',
        '#markup' => '',
        '#suffix' => '</div>',
        '#weight' => -999,
      ];
      $layoutProvider = OmegaLayout::getLayoutProvider($theme);
      // Update the message value.
      $form['inherited_layout']['#markup'] = '<p>This theme is currently inheriting the layout(s) from <strong>' . $layoutProvider . '</strong>, so layout configuration options are not available here. <em>Any changes made to the applicable layouts in the parent theme will be used by this theme.</em> You can edit the layout settings for <strong>' . $layoutProvider . '</strong> <a href="/admin/appearance/settings/' . $layoutProvider . '">here</a>.</p>';
    }

    // Custom settings in Vertical Tabs container.
    $form['omega'] = [
      '#type' => 'vertical_tabs',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => -999,
      '#default_tab' => 'edit-variables',
      '#states' => [
        'invisible' => [
          ':input[name="force_subtheme_creation"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Include the default omega settings.
    include_once 'theme-settings/general-settings.php';

    // Include the ability to enable/disable custom Omega
    // stylesheets/javascripts.
    include_once 'theme-settings/style-settings.php';

    if ($scss_support) {
      // Include the ability to customize various scss variables to provide
      // basic style adjustments.
      include_once 'theme-settings/scss-settings.php';
    }
    // Include the ability to debug various theme development elements.
    include_once 'theme-settings/debug-settings.php';

    // If we aren't inheriting the layout, then add the layout config and
    // builder to the form.
    if (!$inherit_layout) {

      // Check for ajax update of default layout, or use default theme setting.
      $defaultLayout = isset($form_state->values['default_layout']) ? $form_state->values['default_layout'] : theme_get_setting('default_layout', $theme);
      $edit_this_layout = isset($form_state->values['edit_this_layout']) ? $form_state->values['edit_this_layout'] : theme_get_setting('default_layout', $theme);

      // Pull the configuration object for this theme that defines the region
      // groups represented in page.html.twig.
      $region_groups = \Drupal::config($theme . '.region_groups')->get();
      $theme_regions = $themeSettings->info['regions'];

      // Get the layouts available to edit in this theme.
      $layouts = OmegaLayout::getAvaliableLayoutPluginLayouts([$theme]);
      // Include the layout configuration options.
      include_once 'theme-settings/layout-config.php';

      // Include the layout builder interface.
      include_once 'theme-settings/layout-settings.php';
    }
    // Change the text for default submit button.
    $form['actions']['submit']['#value'] = t('Save');

    // TableSelect: Enable the built-in form validation for #tableselect for
    // this form button, so as to ensure that the bulk operations form cannot
    // be submitted without any selected items.
    // $form['actions']['submit']['#tableselect'] = TRUE;
    // Hide the default submit button if 'export new subtheme'
    // option is enabled.
    $form['actions']['submit']['#states'] = [
      'disabled' => [
        ':input[name="export[export_new_subtheme]"]' => ['checked' => TRUE],
      ],
    ];

    // Add appropriate validate & submit hooks.
    $form['#validate'][] = 'omega_theme_settings_validate';
    $form['#submit'][] = 'omega_theme_settings_submit';
    // Copy the default submit button/handler.
    $form['actions']['submit_layout'] = $form['actions']['submit'];
    // Update the text for the new button.
    $form['actions']['submit_layout']['#value'] = t('Save & Update Styles');
    // Update the submit handlers.
    // Add in the default Omega submit handler that handles the layout data.
    $form['actions']['submit_layout']['#submit'][] = 'omega_theme_layout_build_submit';
    // Add in default submit handler.
    $form['actions']['submit_layout']['#submit'][] = '::submitForm';
    // Define the visibility of the custom submit button.
    // Only when enable Omega.gs layout is enabled.
    // AND only when export new subtheme is disabled.
    $form['actions']['submit_layout']['#states'] = [
      'visible' => [
        ':input[name="enable_omegags_layout"]' => ['checked' => TRUE],
        // Once export ability is included, will need to enable/test this again.
      ],
      'disabled' => [
        ':input[name="export[export_new_subtheme]"]' => ['checked' => TRUE],
      ],
    ];
  }
  else {
    // We are forced to export/create a new subtheme. DO IT.
    // Include the ability to export changes made in the Omega settings
    // form to a new subtheme, rather than saving the current settings.
    // Make sure Machine Name functionality is attached.
    $form['#attached']['library'][] = 'core/drupal.machine-name';

    // Include the form data for export/subtheme creation.
    include_once 'theme-settings/export-settings.php';

    $form['actions']['generate_subtheme'] = $form['actions']['submit'];
    $form['actions']['generate_subtheme']['#value'] = t('Export Subtheme');
    $form['actions']['generate_subtheme']['#validate'] = ['omega_theme_generate_validate'];
    $form['actions']['generate_subtheme']['#submit'] = ['omega_theme_generate_submit'];

    // Change the text for default submit button.
    $form['actions']['submit']['#value'] = t('Save');
    // Disable the default submit button.
    $form['actions']['submit']['#disabled'] = TRUE;
    // Hide it.
    $form['actions']['submit']['#access'] = FALSE;
  }
}

/**
 * Function to check machine name for generated theme to ensure it is available.
 *
 * @todo -- Currently unused, but needed function. Move to OmegaInfo class.
 *
 * @param string $machine_name
 *   Desired machine name to be used for theme.
 *
 * @return bool
 *   True if $machine_name selected can be created, false otherwise.
 */
function omega_theme_exists($machine_name) {
  drupal_set_message(t('function <strong>omega_theme_exists</strong> called...'));
  $themes = \Drupal::service('theme_handler')->rebuildThemeData();
  $result = FALSE;
  if (array_key_exists($machine_name, $themes)) {
    $result = TRUE;
  }
  return $result;
}

/**
 * Validation handler for theme settings form.
 */
function omega_theme_settings_validate(array &$form, FormStateInterface $form_state) {

}

/**
 * Default Omega theme settings submit handler.
 *
 * Currently performs the following operations:
 *  - Saves default theme configurations
 *  - Saves updates to the layouts in the database:
 *    - This is handled by calling _omega_save_database_layout for each layout
 *  - Removes $layouts from the default theme settings config to avoid storing
 *    it all in one massive variable at $theme.settings, and instead relies on
 *    $theme.layout.$layout_id to contain individual layouts.
 */
function omega_theme_settings_submit(array &$form, FormStateInterface $form_state) {
  // Get the build info for the form.
  $build_info = $form_state->getBuildInfo();

  // Get the theme name.
  $theme = $build_info['args'][0];

  // Get all the values of the submitted form.
  $values = $form_state->getValues();

  // Grab the value of layouts so we can update the $theme.layout.$layout_name.
  $layouts = $values['layouts'];

  // Unset the layout variable so it's not stored in $theme.settings.
  // Instead an empty array will replace the value in $theme.settings.
  $form_state->setValue('layouts', []);

  // FOREACH $layouts we need to accomplish the following:
  // Check to see if there are differences in the layout
  // stored and layout submitted
  // Write the changes to $theme.layout.$layout in the config table
  // Ensure this will work with multiple layouts passed (current) OR
  // when individual layouts are passed (hopefully) via ajax.
  foreach ($layouts as $layout_id => $layout) {
    // Save $layout to the database.
    OmegaLayout::saveLayoutData($layout, $layout_id, $theme, FALSE);
  }
}

/**
 * Function to handle validation of the build.
 */
function omega_theme_layout_build_validate(array &$form, FormStateInterface $form_state) {
  $build_info = $form_state->getBuildInfo();
  // Get the theme name.
  $theme = $build_info['args'][0];
  // Get all the values of the submitted form.
  $values = $form_state->getValues();
}

/**
 * Theme settings submit handler for full layout generation (SCSS/CSS files).
 *
 * Currently performs the following operations:
 *  - Saves default theme configurations.
 *  - Saves updates to the layouts in the database:
 *    - This is handled by calling _omega_save_database_layout for each layout.
 *  - Removes $layouts from the default theme settings config to avoid storing
 *    it all in one massive variable at $theme.settings, and instead relies on
 *    $theme.layout.$layout_id to contain individual layouts.
 *  - Passes $layout to _omega_compile_layout_sass to generate the
 *    appropriate SCSS based on settings provided.
 *  - Passes returned SCSS to _omega_compile_css to generate the CSS.
 *  - Passes returned SCSS and CSS to _omega_save_layout_files to
 *    generate new SCSS and CSS files.
 */
function omega_theme_layout_build_submit(array &$form, FormStateInterface $form_state) {
  // Get the build info for the form.
  $build_info = $form_state->getBuildInfo();
  // Get the theme name.
  $theme = $build_info['args'][0];
  // Get all the values of the submitted form.
  $values = $form_state->getValues();

  // Grab the value of layouts so we can update the $theme.layout.$layout_name.
  $layouts = $values['layouts'];

  // Unset the layout variable so it's not stored in $theme.settings.
  // Instead an empty array will replace the value in $theme.settings.
  $form_state->setValue('layouts', []);

  // FOREACH $layouts we need to run some operations that will hopefully
  // accomplish the following:
  // Check to see if there are differences in the layout stored
  // and layout submitted.
  // Write the changes to $theme.layout.$layout in the config table.
  // @todo See if $theme.layout.$layout.generated exists and matches submitted values
  // This will allow us to skip file generation as well for unchanged values.
  // Write the changes to SCSS and generate CSS.
  // Ensure this will work with multiple layouts passed (current) OR
  // when individual layouts are passed (hopefully) via ajax.
  foreach ($layouts as $layout_id => $layout) {
    // Save $layout to the database and see if we need to regenerate the files.
    $generated = OmegaLayout::saveLayoutData($layout, $layout_id, $theme, TRUE);
    // Determine if SCSS should be forced to compile or not.
    $compile_now = isset($values['force_scss_compile']) ? TRUE : FALSE;
    // We return either true or false from the _omega_save_database_layout
    // function that tells us that the last value in
    // $theme.layout.$layout_id.generated didn't match, so we need to rewrite
    // the SCSS/CSS files.
    // Also check to see if we've actually forced the SCSS/CSS to recompile.
    if ($generated || $compile_now) {
      // Generate the SCSS from the layout data.
      OmegaLayout::compileLayout($layout, $layout_id, $theme);
    }
  }

  if (isset($values['variables'])) {
    // If this is true, SCSS color/variable support was turned on.
    // Grab the value of variables so we can update _omega-style-vars.scss.
    $styles = $values['variables'];
    // Run function to compile the _omega-style-vars.scss file with any updates.
    OmegaStyle::scssVariablesUpdate($styles, $theme);
  }
}
