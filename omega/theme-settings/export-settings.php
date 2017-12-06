<?php

// Custom settings in Vertical Tabs container
$form['subtheme-generator'] = array(
  '#type' => 'vertical_tabs',
  '#attributes' => array('class' => array('entity-meta')),
  '#weight' => 10,
  '#default_tab' => 'edit-export',
);

$form['export'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('debug')),
  '#title' => t('Omega Subtheme Generator'),
  '#weight' => -9999,
  '#group' => 'subtheme-generator',
  '#tree' => TRUE,
  //'#open' => TRUE,
);

$form['export']['export_details'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('export-details')),
  '#title' => t('Sub-Theme Information'),
  '#weight' => 0,
  '#group' => 'export',
  '#open' => TRUE,
);

// Friendly name of new theme.
$form['export']['export_details']['theme_friendly_name'] = array(
  '#type' => 'textfield',
  '#title' => t('Theme name'),
  '#maxlength' => 50, // the maximum allowable length of a module or theme name.
  '#size' => 30,
  '#required' => TRUE,
  '#default_value' => '',
  '#description' => t('A unique "friendly" name. Letters, spaces and underscores only - numbers and all other characters are stripped or converted.'),
);


// Machine name of new theme.
$form['export']['export_details']['theme_machine_name'] = array(
  '#type' => 'machine_name',
  '#maxlength' => 50,
  '#size' => 30,
  '#title' => t('Machine name'),
  '#required' => TRUE,
  '#field_prefix' => '',
  '#default_value' => '',
  '#machine_name' => array(
    'exists' => array($omegaSettings, 'omegaThemeExists'), // callback
    'source' => array('export', 'export_details', 'theme_friendly_name'),
    'label' => t('Machine name'),
    'replace_pattern' => '[^a-z_]+',
    'replace' => '_',
  ),
);

$form['export']['export_details']['export_description'] = array(
  '#type' => 'textfield',
  '#title' => t('Description'),
  '#description' => t('Enter a short description to describe the newly exported subtheme. This appears only in the administrative interface.'),
  '#default_value' => '',
);

$form['export']['export_details']['export_version'] = array(
  '#type' => 'textfield',
  '#title' => t('Version'),
  '#description' => t(''),
  '#default_value' => '8.x-5.x',
);

$form['export']['export_options'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('export-options')),
  '#title' => t('Sub-Theme Options'),
  '#weight' => 1,
  '#group' => 'export',
  '#open' => TRUE,
);

/**
 * We will only show the "clone" option if there has been a subtheme of Omega
 * already created or referenced in the system. This will prevent the confusion
 * that you might be able to clone Omega.
 */
$clone_options = [
  'clone' => 'Clone',
  'subtheme' => 'Sub-Theme',
];

// Get the array of options for the subtheme dropdown.
$omega_subthemes = $omegaSettings->omegaSubthemesOptionsList();

// If there's not any options, remove the clone option.
if (sizeof($omega_subthemes) == 0) {
  unset($clone_options['clone']);
}

$form['export']['export_options']['export_type'] = array(
  '#type' => 'radios',
  '#title' => t('Create a:'),
  '#options' => $clone_options,
  '#default_value' => 'subtheme',
  '#prefix' => '<div id="export-type-select" class="export-type-select">',
  '#suffix' => '<span class="separator">of</span>',
);

$omegaSubThemes = array('omega' => 'Omega') + $omega_subthemes;

$form['export']['export_options']['export_theme_base'] = array(
  '#type' => 'select',
  '#options' => $omegaSubThemes,
  '#title' => t('Omega Sub-Theme'),
  // set $theme as default so if we are using the generator inside a subtheme, it will
  // auto select the current theme as the new base of a clone/kit.
  '#default_value' => $theme,
  '#suffix' => '<span class="separator">in</span>',
);

// @todo: This should potentially be factored a bit better.
$exportPaths = array(
  'Recommended Locations' => array(
    'themes/custom' => 'themes/custom',
  ),
  'Other Locations' => array(
    'themes' => 'themes',
    'themes/contrib' => 'themes/contrib',
    'core/themes' => 'core/themes',
  ),
);

$form['export']['export_options']['export_destination_path'] = array(
  '#type' => 'select',
  '#options' => $exportPaths,
  '#title' => t('Export Location'),
  // set $theme as default so if we are using the generator inside a subtheme, it will
  // auto select the current theme as the new base of a clone/kit.
  '#default_value' => 'themes/custom',
  '#suffix' => '</div>',
);

// STATES VARIABLES
$omega_state__type_clone = array(':input[name="export[export_options][export_type]"]' => array('value' => 'clone'));
$omega_state__type_subtheme = array(':input[name="export[export_options][export_type]"]' => array('value' => 'subtheme'));
$omega_state__base_omega = array(':input[name="export[export_options][export_theme_base]"]' => array('value' => 'omega'));

// variable to represent state to apply to clone themes only
$clone_state = array(
  'visible' => array(
    ':input[name="export[export_options][export_type]"]' => array('value' => 'clone'),
  ),
);

// variable to represent state to apply to sub-themes only
$subtheme_state = array(
  'visible' => array(
    ':input[name="export[export_options][export_type]"]' => array('value' => 'subtheme'),
  ),
);

$form['export']['export_options']['export_options_clone'] = array(
  '#type' => 'item',
  '#prefix' => '<div class="description omega-export-options">',
  '#markup' => '<p>Creating a clone of a sub-theme will create a direct clone with minimal options for customization. The process will clone the entire directory, and search and replace machine names where appropriate to create a newly named theme identical in every way to the previous theme. This provides great potential for quick testing of a theme patch on your installation without risking any adverse effects on the primary theme.</p>',
  '#suffix' => '</div>',
  '#states' => $clone_state,
);

$form['export']['export_options']['export_options_kit'] = array(
  '#type' => 'item',
  '#prefix' => '<div class="description omega-export-options">',
  '#markup' => '<p>Creating a sub-theme will allow you to create a highly customized new theme based on another theme. The options available here will allow you to customize items like layout inheritance, template overrides, SCSS support and more. Each option includes a detailed description that should clarify exactly what will happen when selecting/deselecting an option.</p>',
  '#suffix' => '</div>',
  '#states' => $subtheme_state,
);

$form['export']['export_options']['export_install_auto'] = array(
  '#access' => FALSE, // currently not implemented/working
  '#type' => 'checkbox',
  '#title' => t('Install'),
  '#description' => t('Setting this theme to install will make it available to the system, but will not set it as the default theme for the site.'),
  '#default_value' => 1,
);

$form['export']['export_options']['export_install_default'] = array(
  '#access' => FALSE, // currently not implemented/working
  '#type' => 'checkbox',
  '#title' => t('AND set as default theme'),
  '#description' => t('Selecting to set the newly created theme as the default theme will make it the primary theme for the site. Proceed with caution! You would never want to do this in a production environment.'),
  '#default_value' => 0,
  '#disabled' => TRUE,
  '#states' => array(
    'enabled' => array(
      ':input[name="export[export_options][export_install_auto]"]' => array('checked' => TRUE),
    ),
  ),
);

$form['export']['export_options']['export_include_block_positions'] = array(
  '#access' => FALSE, // currently not implemented/working
  '#type' => 'checkbox',
  '#title' => t('Export block placements (still @todo/@tofix/@toinvesigate)'),
  '#description' => t('<p>This feature will copy all block placements from the base theme to ensure core blocks are placed properly by default.</p><p>This should not normally be needed if the theme you are using as your base theme has been installed and the new subtheme will inherit the same regions. This export feature, however will copy the latest block location placements to help ensure blocks appear in the correct regions.</p>'),
  '#default_value' => 1,
  '#states' => $subtheme_state,
);


// This is set to be invisible when Omega is selected as the theme to create a
// subtheme of. Even if we are starting a 'bare' Omega subtheme, we would NEVER
// want to use layout provided by Omega as it can't be edited and could be
// overwritten by updates.
// @todo: Ensure functionality matches this behavior to NOT inherit layout from Omega.
$form['export']['export_options']['export_inherit_layout'] = array(
  '#type' => 'checkbox',
  '#title' => t('Inherit Layout from parent theme'),
  '#description' => t('<p>When this option is unchecked, a copy of any layouts in the parent theme will be copied to the new subtheme, allowing each theme to have different layouts. When this option IS checked, all layout settings/css will be inherited from the parent theme. You would likely want this option checked if you are creating a very slim subtheme that will only have a few color changes made to it from the defaults copied/inherited from the parent theme and the layouts between the two themes will always remain consistent.</p><p><em>This also assumes that the regions between the subtheme and parent theme MUST match. If they do not match, unintended consequences are likely</em>.</p>'),
  '#default_value' => 0,
  '#states' => array(
    'invisible' => array(
      $omega_state__base_omega,
    ),
  ),
);

$form['export']['export_options']['export_include_templates'] = array(
  '#type' => 'checkbox',
  '#title' => t('Include templates from parent theme.'),
  '#description' => t('This will copy all files from the template folder of the parent theme to the subtheme. This will allow for detailed template customization for a subtheme without having to copy templates one by one for override. However, any template updates that are made to a parent theme would need to be then updated here. Use this with caution. Otherwise, the template folder will be empty by default and ready for selective copying of any needed overrides.'),
  '#default_value' => 0,
  '#states' => $subtheme_state,
);

$form['export']['export_options']['export_enable_scss_support'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable SCSS Customizations'),
  '#description' => t('Enabling SCSS Customizations will enable the <strong>SCSS Variables</strong> tab in your new theme. This will allow you to alter colors, fonts, etc. from the interface. When a new theme is created with this option enabled, copies of all SCSS/CSS files from all parent themes will be copied to the new theme.'),
  '#default_value' => 0,
);

$form['export']['export_options']['export_enable_configrb'] = array(
  '#type' => 'checkbox',
  '#title' => t('Include config.rb'),
  '#description' => t('Creating a config.rb in your subtheme will allow you to use Compass to compile your SCSS rather than Omega. <strong>If you are unsure what config.rb is, leave this option unchecked.</strong> For more information on config.rb options, visit <a href="http://compass-style.org/help/documentation/configuration-reference/" target="_blank">compass-style.org</a>.'),
  '#default_value' => 0,
  '#states' => $subtheme_state,
);

$form['export']['export_options']['export_enable_gemfile'] = array(
  '#type' => 'checkbox',
  '#title' => t('Include Gemfile'),
  '#description' => t('Creating a Gemfile will help install ruby gem dependencies needed using Bundler. <strong>If you are unsure what a Gemfile is, leave this option unchecked.</strong> For more information on Gemfile options, visit <a href="http://bundler.io/gemfile.html" target="_blank">bundler.io</a>.'),
  '#default_value' => 0,
  '#states' => $subtheme_state,
);

$form['export']['export_options']['export_enable_gruntfile'] = array(
  '#type' => 'checkbox',
  '#title' => t('Include Gruntfile.js'),
  '#description' => t('Grunt is a JavaScript Task Runner. It will allow you to streamline repetitive tasks like minification, compilation, unit testing, linting, etc. <strong>If you are unsure what Grunt is, leave this option unchecked.</strong> For more information on Gruntfile.js options, visit <a href="http://gruntjs.com/getting-started" target="_blank">gruntjs.com</a>.'),
  '#default_value' => 0,
  '#states' => $subtheme_state,
);

$form['export']['export_options']['export_enable_gulpfile'] = array(
  '#type' => 'checkbox',
  '#title' => t('Include gulpfile.js/gulpconfig.js'),
  '#description' => t('Gulp is a toolkit for automating painful or time-consuming tasks in your development workflow, so you can stop messing around and build something. <strong>If you are unsure what Gulp is, leave this option unchecked.</strong> For more information on gulpfile.js options, visit <a href="http://gulpjs.com/" target="_blank">gulpjs.com</a>.'),
  '#default_value' => 0,
  '#states' => $subtheme_state,
);

$form['export']['export_options']['export_include_blank_library'] = array(
  '#type' => 'checkbox',
  '#title' => t('Include customizable library with CSS/JS'),
  '#description' => t('This will create a libraries.yml file for your theme, defining a custom CSS and JS include for your theme, with basic usage examples.'),
  '#default_value' => 1,
  '#states' => $subtheme_state,
);

$form['export']['export_options']['export_include_theme_settings_php'] = array(
  '#type' => 'checkbox',
  '#title' => t('Include theme-settings.php'),
  '#description' => t('This will create a <strong>theme-settings.php</strong> file in your new theme, and include basic hook information and usage.'),
  '#default_value' => 0,
  '#states' => array(
    'visible' => array(
      $omega_state__type_subtheme,
      $omega_state__type_clone
    ),
  ),
);
