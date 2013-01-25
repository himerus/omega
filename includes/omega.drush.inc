<?php

/**
 * @file
 * Please supply a file description.
 */

require_once dirname(__FILE__) . '/omega.inc';

/**
 * Implements hook_drush_command().
 */
function omega_drush_command() {
  $items['omega-subtheme'] = array(
    'description' => dt('Creates a Omega subtheme.'),
    'arguments' => array(
      'name' => dt('The name of your subtheme.'),
    ),
    'options' => array(
      'destination' => dt('The destination of your subtheme. Defaults to "site:all" (sites/all/themes). May be one of "site:foo", "profile:bar" or "theme:baz" ("foo", "bar" and "baz" being the name of your site, profile or parent theme).'),
      'machine-name' => dt('The machine-readable name of your subtheme. This will be auto-generated from the human-readable name if omitted.'),
      'starterkit' => dt('The starterkit that your subtheme should use. Defaults to "default".'),
      'basetheme' => dt('Specifies a custom base theme. Defaults to "omega".'),
      'enable' => dt('Automatically enable the subtheme after creation.'),
      'set-default' => dt('Automatically enable the subtheme after creation and make it the default theme.'),
      'no-readme' => dt('Skips readme files when generating the subtheme.'),
    ),
    'examples' => array(
      'drush omega-subtheme "My Theme"' => dt('Creates an Omega subtheme called "My Theme".'),
      'drush omega-subtheme "My Theme" --destination=site:example.com' => dt('Creates an Omega subtheme called "My Theme" in sites/example.com/themes.'),
      'drush omega-subtheme "My Theme" --basetheme=my_custom_basetheme' => dt('Uses the default starterkit from a custom basetheme to create an Omega subtheme called "My Theme" in sites/all/themes.'),
      'drush omega-subtheme "My Theme" --basetheme=my_custom_basetheme --starterkit=my_custom_starterkit' => dt('Uses the my_custom_starterkit from a custom basetheme to create an Omega subtheme called "My Theme" in sites/all/themes.'),
    ),
    'aliases' => array('osub'),
  );

  $items['omega-wizard'] = array(
    'description' => dt('Guides you through a wizard for generating a subtheme.'),
    'aliases' => array('owiz'),
  );

  $items['omega-export'] = array(
    'description' => dt('Exports the theme settings of a given theme from the database to the .info file.'),
    'arguments' => array(
      'theme' => dt('The machine-readable name of the theme to export the theme settings for.'),
    ),
    'options' => array(
      'revert' => dt('Purges the theme settings from the database after exporting them to the .info file.'),
    ),
    'examples' => array(
      'drush omega-export foo' => dt('Exports the theme settings of the "foo" theme to the "foo.info" file in that theme.'),
      'drush omega-export foo --revert' => dt('Purges the theme settings of the "foo" theme from the database after exporting them to the .info file.'),
    ),
    'aliases' => array('oexp'),
  );

  $items['omega-revert'] = array(
    'description' => dt('Reverts the theme settings of a given theme by deleting them from the database.'),
    'arguments' => array(
      'theme' => dt('The machine-readable name of the theme to revert the theme settings for.'),
    ),
    'examples' => array(
      'drush omega-revert foo' => dt('Reverts the theme settings of the "foo" theme.'),
    ),
    'aliases' => array('orev'),
  );

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function omega_drush_help($section) {
  switch ($section) {
    case 'drush:omega-subtheme':
      return dt('Generates a subtheme.');
    case 'drush:omega-wizard':
      return dt('Guides you through a wizard for generating a subtheme.');
    case 'drush:omega-export':
      return dt('Exports the theme settings of a given theme.');
    case 'drush:omega-revert':
      return dt('Reverts the theme settings of a given theme.');
  }
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_omega_subtheme_validate($name = NULL) {
  // Rebuild the theme data so that we can safely check for the existence of
  // themes by using the information provided by list_themes().
  system_rebuild_theme_data();

  if ($machine_name = drush_get_option('machine-name')) {
    // Validate the machine-readable name of the theme.
    if (!is_string($machine_name)) {
      return drush_set_error('OMEGA_THEME_NAME_INVALID', dt('The --machine-name option expects a string value.'));
    }

    if (!preg_match('/^[a-z][a-z0-9_]*$/', $machine_name)) {
      return drush_set_error('OMEGA_THEME_NAME_INVALID', dt('The machine name (@name) is invalid. It may only contain lowercase numbers, letters and underscores and must start with a letter.', array(
        '@name' => $machine_name,
      )));
    }

    $themes = list_themes();
    // Validate that the machine-readable name of the theme is unique.
    if (isset($themes[$machine_name])) {
      return drush_set_error('OMEGA_THEME_ALREADY_EXISTS', dt('A theme with the name @name already exists. The machine-readable name must be unique.', array(
        '@name' => $machine_name,
      )));
    }
  }

  if ($destination = drush_get_option('destination')) {
    // Check if the syntax of the destination is valid.
    if (!is_string($destination) || !preg_match('/^(site|theme|profile):[a-z][a-z0-9_]*/', $destination)) {
      return drush_set_error('OMEGA_DESTINATION_INVALID', dt('The destination syntax (@destination) is invalid. Please use one of the following destination patterns (site, profile or theme): --destination="site:foo", --destination="profile:bar" or --destination="theme:baz".', array(
        '@destination' => $destination,
      )));
    }

    // Check if the provided destination exists.
    if (!drush_omega_resolve_destination($destination)) {
      list($type, $destination) = explode(':', $destination);

      return drush_set_error('OMEGA_DESTINATION_DOEST_NOT_EXIST', dt('The given destination @destination of type @type does not exist. Did you misspell it?', array(
        '@destination' => $destination,
        '@type' => $type,
      )));
    }
  }

  if ($basetheme = drush_get_option('basetheme')) {
    if (!is_string($basetheme)) {
      return drush_set_error('OMEGA_BASETHEME_INVALID', dt('The --basetheme option expects a string value.'));
    }

    // Check if the base theme exists.
    if (!array_key_exists($basetheme, list_themes())) {
      return drush_set_error('OMEGA_BASETHEME_DOES_NOT_EXIST', dt('The base theme @basetheme does not exist or is invalid.', array(
        '@basetheme' => $basetheme,
      )));
    }

    // Check if the base theme is an Omega theme.
    if (!array_key_exists('omega', omega_theme_trail($basetheme))) {
      return drush_set_error('OMEGA_BASETHEME_INVALID', dt('The base theme @basetheme does not exist or is invalid.', array(
        '@basetheme' => $basetheme,
      )));
    }
  }

  if ($starterkit = drush_get_option('starterkit')) {
    if (!is_string($starterkit)) {
      return drush_set_error('OMEGA_STARTERKIT_INVALID', dt('The --starterkit option expects a string value.'));
    }

    $basetheme = drush_get_option('basetheme', 'omega');

    // Check if the starterkit exists.
    if (!array_key_exists($starterkit, omega_discovery('starterkit', $basetheme))) {
      $themes = list_themes();

      return drush_set_error('OMEGA_STARTERKIT_DOES_NOT_EXIST', dt('There is no valid @basetheme theme starterkit with the name @starterkit. Did you forget to specify the correct basetheme?', array(
        '@basetheme' => $themes[$basetheme]->info['name'],
        '@starterkit' => $starterkit,
      )));
    }
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_omega_subtheme($name = NULL) {
  if (!isset($name)) {
    $name = drush_prompt(dt('Please enter the name of the new sub-theme'));
  }

  // Try to generate a machine-readable name. If that fails, prompt for one.
  if (!$machine_name = drush_get_option('machine-name', drush_omega_generate_theme_name($name))) {
    drush_print(dt("Sorry, I couldn't generate a machine-readable name for @name.", array(
      '@name' => $name,
    )));

    $machine_name = drush_omega_require_valid_theme_name(dt('Please enter a machine-readable name for your new theme'));
  }

  $basetheme = drush_get_option('basetheme', 'omega');
  $starterkits = omega_discovery('starterkit', $basetheme);
  $starterkit = drush_get_option('starterkit', 'default');
  $destination = drush_omega_resolve_destination(drush_get_option('destination', 'site:all'));

  $subtheme = new stdClass();
  $subtheme->name = $name;
  $subtheme->machine_name = $machine_name;
  $subtheme->path = $destination . '/' . $subtheme->machine_name;
  $subtheme->starterkit = $starterkits[$starterkit];
  $subtheme->default = (bool) drush_get_option('set-default');
  $subtheme->enable = (bool) ($subtheme->default || drush_get_option('enable'));
  $subtheme->basetheme = $basetheme;
  $subtheme->readme = (bool) !drush_get_option('no-readme');

  if (drush_op('drush_omega_generate_subtheme', $subtheme)) {
    drush_log(dt('You have successfully created the theme @theme (@name) in @path.', array(
      '@theme' => $subtheme->name,
      '@name' => $subtheme->machine_name,
      '@path' => dirname($subtheme->path),
    )), 'success');
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_omega_wizard() {
  // Rebuild the theme data so that we can safely check for the existence of
  // themes by using the information provided by list_themes().
  system_rebuild_theme_data();

  // Prompt for a theme name.
  $name = drush_prompt(dt('Please enter the name of the new sub-theme'));

  // Try to generate a machine-readable name. If that fails, prompt for one.
  if (!$machine_name = drush_omega_generate_theme_name($name)) {
    drush_print(dt("Sorry, I couldn't generate a machine-readable name for @name", array(
      '@name' => $name,
    )));
  }
  // Prompt for a theme name using the automatically generated default if any.
  $machine_name = drush_omega_require_valid_theme_name(dt('Please enter a machine-readable name for your new theme'), ($machine_name ? $machine_name : NULL));

  // Prompt for a base theme.
  if (!$basetheme = drush_omega_theme_choice(dt('Please choose a base theme for your new theme'))) {
    return;
  }

  // Let the user choose a starterkit.
  if (!$starterkit = drush_omega_starterkit_choice($basetheme, dt('Please choose a starterkit for your new theme'))) {
    return;
  }

  // Let the user choose a destination. == 'Yes'
  if (!$destination = drush_omega_destination_choice(dt('Please choose a destination. This is where your sub-theme will be placed'))) {
    return;
  }
  $destination = drush_omega_resolve_destination($destination);

  // Optionally skip readme files when generating the subtheme.
  $readme = preg_match('/^[yY]([eE][sS])?$/', drush_prompt(dt('Do you want to include keep the readme files that are included in the starterkit?'), 'yes'));

  // Finally, let the user choose to directly enable the subtheme.
  if ($enable = preg_match('/^[yY]([eE][sS])?$/', drush_prompt(dt('Do you want to enable your new theme?'), 'yes'))) {
    $default =  preg_match('/^[yY]([eE][sS])?$/', drush_prompt(dt('Do you want to make your new theme the default theme?'), 'yes'));
  }

  $starterkits = omega_discovery('starterkit', $basetheme);

  $subtheme = new stdClass();
  $subtheme->name = $name;
  $subtheme->machine_name = $machine_name;
  $subtheme->path = $destination . '/' . $subtheme->machine_name;
  $subtheme->starterkit = $starterkits[$starterkit];
  $subtheme->default = $default;
  $subtheme->enable = $enable;
  $subtheme->basetheme = $basetheme;
  $subtheme->readme = $readme;

  if (drush_op('drush_omega_generate_subtheme', $subtheme)) {
    drush_log(dt('You have successfully created the theme @theme (@name) in @path.', array(
      '@theme' => $subtheme->name,
      '@name' => $subtheme->machine_name,
      '@path' => dirname($subtheme->path),
    )), 'success');
  }
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_omega_export_validate($theme = NULL) {
  // Rebuild the theme data so that we can safely check for the existence of
  // themes by using the information provided by list_themes().
  system_rebuild_theme_data();

  $themes = list_themes();
  // Check if the given theme exists.
  if (isset($theme) && !isset($themes[$theme])) {
    return drush_set_error('OMEGA_THEME_DOES_NOT_EXIST', dt('There is no theme with the name @theme.', array(
      '@theme' => $theme,
    )));
  }
}

/**
 * Implements drush_hook_COMMAND().
 *
 * Exports the theme settings for the given theme from the database and writes
 * them into the .info file of that theme.
 *
 * @param $theme
 *   (optional) The machine-readable name of a theme.
 * @return bool
 *   TRUE on success, FALSE on failure.
 */
function drush_omega_export($theme = NULL) {
  if (!isset($theme) && !$theme = drush_omega_theme_choice(dt('Which theme do you want to export the theme settings for?'))) {
    return;
  }

  $themes = list_themes();

  // Insert the theme settings from the database.
  if ($settings = variable_get('theme_' . $theme . '_settings')) {
    foreach (_system_default_theme_features() as $feature) {
      // Remove the 'toggle_foobar' elements from the theme settings array.
      unset($settings['toggle_' . $feature]);
    }
  }
  elseif (!drush_confirm(dt('There are no theme settings for @theme stored in the database. Do you want to purge the theme settings from the .info file too?', array('@theme' => $themes[$theme]->info['name'])))) {
    return;
  }

  // Parse the current content of the .info file so we can append the settings
  // from the database.
  $path = drupal_get_path('theme', $theme) . '/' . $theme . '.info';
  $data = file_get_contents($path);

  // Remove the old theme settings from the .info file.
  $data = trim(preg_replace('/^settings\[.*\].*\n?/mi', '', $data));

  // Append the exported theme settings to the .info file if there are any.
  $data = $settings ? $data . "\n\n" . drush_omega_compose_info_file($settings, 'settings') : $data;

  // Write the data to the .info file of the theme.
  if (file_put_contents($path, $data)) {
    drush_log(dt('The theme settings for the @theme theme have been exported to the .info file of the theme.', array('@theme' => $themes[$theme]->info['name'])), 'success');

    if (drush_get_option('revert')) {
      // Revert the theme settings if the 'revert' option is set and they have
      // been exported successfully. In this case, we invoke the API function
      // through the drush command to display the messages.
      drush_op('drush_omega_revert', $theme);
    }

    return TRUE;
  }
  else {
    // There was an error while exporting the theme settings.
    return drush_set_error('OMEGA_EXPORT_ERROR', dt('An error occurred while trying to export the theme settings for the @theme theme.', array('@theme' => $themes[$theme]->info['name'])));
  }
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_omega_revert_validate($theme) {
  // Rebuild the theme data so that we can safely check for the existence of
  // themes by using the information provided by list_themes().
  system_rebuild_theme_data();

  $themes = list_themes();
  // Check if the given theme exists.
  if (isset($theme) && !isset($themes[$theme])) {
    return drush_set_error('OMEGA_THEME_NOT_EXISTS', dt('There is no theme with the name @theme.', array(
      '@theme' => $theme,
    )));
  }
}

/**
 * Implements drush_hook_COMMAND().
 *
 * Delete the theme settings that have been stored in the database and thereby
 * reverts them to the default settings from the .info file.
 *
 * @param $theme
 *   The machine-readable name of a theme.
 */
function drush_omega_revert($theme = NULL) {
  if (!isset($theme) && !$theme = drush_omega_theme_choice(dt('Which theme do you want to revert the theme settings for?'))) {
    return;
  }

  $themes = list_themes();
  // Delete the theme settings variable for the given theme.
  variable_del('theme_' . $theme . '_settings');

  // Clear the theme settings and extensions cache.
  cache_clear_all('theme_settings:' . $theme, 'cache');
  cache_clear_all('omega_extensions:' . $theme, 'cache');

  // Rebuild the theme data for good measure.
  drupal_theme_rebuild();
  system_rebuild_theme_data();

  drush_log(dt('You have successfully reverted the theme settings for the @theme theme.', array('@theme' => $themes[$theme]->info['name'])), 'success');
}

/**
 * Resolves the destination path for a subtheme. This can either be a profile
 * theme folder, a sites theme folder or a theme.
 *
 * @param $destination
 *   A destination string, this can either be a site path ('site:foo'), a
 *   profile path ('profile:foo') or a theme path ('theme:foo').
 *
 * @return string|bool
 *   The full path to the given destination, FALSE if the destination could not
 *   be resolved.
 */
function drush_omega_resolve_destination($destination) {
  list($type, $destination) = explode(':', $destination);

  switch($type) {
    case 'site':
      if (array_key_exists($destination, drush_omega_sites())) {
        return 'sites/' . $destination . '/themes';
      }
      break;

    case 'profile':
      require_once DRUPAL_ROOT . '/includes/install.core.inc';
      if (array_key_exists($destination, install_find_profiles())) {
        return 'profiles/' . $destination . '/themes';
      }
      break;

    case 'theme':
      if (array_key_exists($destination, list_themes())) {
        return drupal_get_path('theme', $destination);
      }
      break;
  }
}

/**
 * Helper function for printing a list of available Omega themes.
 *
 * @param $message
 *   The message that should be displayed.
 *
 * @return bool|string
 *   The machine-readable name of the chosen theme or FALSE if the operation was
 *   cancelled.
 */
function drush_omega_theme_choice($message) {
  $options = array();
  foreach (list_themes() as $key => $info) {
    $trail = omega_theme_trail($key);
    if (array_key_exists('omega', $trail)) {
      $options[$key] = dt('@name (Trail: !trail)', array(
        '@name' => $info->info['name'],
        '!trail' => implode(' > ', $trail),
      ));
    }
  }
  return drush_choice($options, $message);
}

/**
 * Helper function for printing a list of available starterkits.
 *
 * @param $basetheme
 *   The machine-readable name of a basetheme.
 * @param $message
 *   The message that should be displayed.
 *
 * @return bool|string
 *   The machine-readable name of the chosen starterkit or FALSE if the
 *   operation was cancelled.
 */
function drush_omega_starterkit_choice($basetheme, $message) {
  $themes = list_themes();
  $options = array();
  foreach (omega_discovery('starterkit', $basetheme) as $key => $info) {
    $options[$key] = dt('@name (Provided by @provider)', array(
      '@name' => $info['info']['name'],
      '@provider' => $themes[$info['theme']]->info['name'],
    ));
  }
  return drush_choice($options, $message);
}

/**
 * Helper function that asks for the desired destination of a subtheme.
 *
 * @param $message
 *   The message that should be displayed.
 *
 * @return bool|string
 *   The given destination using the pattern "type:destination"
 *   (e.g. "site:all") or FALSE if the operation was cancelled.
 */
function drush_omega_destination_choice($message) {
  drush_print($message);

  // Let the user choose a destination.
  $options = array(
    'site' => dt("Site (e.g. 'all' or 'example.com')"),
    'profile' => dt('Installation profile'),
    'theme' => dt('Parent theme'),
  );

  if (!$type = drush_choice($options, dt('Please choose a destination type.'))) {
    return FALSE;
  }

  switch ($type) {
    case 'site':
      if (!$destination = drush_choice(drush_omega_sites(), dt('Please choose a site.'))) {
        return FALSE;
      }
      return 'site:' .  $destination;

    case 'profile':
      require_once DRUPAL_ROOT . '/includes/install.core.inc';

      $options = array();
      foreach (install_find_profiles() as $profile) {
        $info = drupal_parse_info_file(dirname($profile->uri) . '/' . $profile->name . '.info');
        $options[$profile->name] = $info['name'];
      }

      if (!$destination = drush_choice($options, dt('Please choose an installation profile.'))) {
        return FALSE;
      }
      return 'profile:' . $destination;

    case 'theme':
      if (!$destination = drush_omega_theme_choice(dt('Please choose a theme.'))) {
        return FALSE;
      }
      return 'theme:' . $destination;

    default:
      return 'site:all';
  }
}


/**
 * Helper function that continuously prompts for a valid machine-readable name.
 *
 * @param $message
 *   The message that should be displayed.
 *
 * @return string
 *   A valid, unique machine-readable name.
 */
function drush_omega_require_valid_theme_name($message, $default = NULL) {
  while (TRUE) {
    // Keep prompting for a machine-name until we get an acceptable value.
    $prompt = drush_prompt($message, $default);

    if (!preg_match('/^[a-z][a-z0-9_]*$/', $prompt)) {
      drush_print('The machine-readable name is invalid. It may only contain lowercase numbers, letters and underscores and must start with a letter.');
    }
    else {
      $themes = list_themes();
      // Validate that the machine-readable name of the theme is unique.
      if (isset($themes[$prompt])) {
        drush_print(dt('A theme with the name @name already exists. The machine-readable name must be unique.', array(
          '@name' => $prompt,
        )));
      }
      else {
        // The given machine-readable name is valid. Let's proceed.
        return $prompt;
      }
    }
  }
}

/**
 * Recursively rewrites (and renames) all files in a given path.
 *
 * @param $path
 *   The path to rewrite all files in.
 * @param $search
 *   The string(s) to look for when replacing the file names and contents. Can
 *   be an array or a string.
 * @param $replace
 *   The string(s) to replace $search with. Can be an array or a string.
 *
 * @return bool
 *   TRUE if the operation succeeded, FALSE otherwise.
 *
 * @see omega_drush_replace_contents()
 * @see str_replace()
 */
function drush_omega_rewrite_recursive($path, $search, $replace) {
  if ($path !== ($new = str_replace($search, $replace, $path))) {
    // First, try to rename (move) the file if the name was changed.
    if (!drush_op('drush_move_dir', $path, $new, TRUE)) {
      return FALSE;
    };
  }

  if (is_dir($new)) {
    // If the file actually is a directory, proceed with the recursion.
    $directory = dir($new);

    while (FALSE !== ($read = $directory->read())) {
      if ($read != '.' && $read != '..' ) {
        if (!drush_op('drush_omega_rewrite_recursive', $new . '/' . $read, $search, $replace)) {
          return FALSE;
        }
      }
    }

    $directory->close();
  }
  elseif (is_file($new)) {
    // If it is a file, try to replace its contents.
    $before = file_get_contents($new);
    if ($before !== ($after = str_replace($search, $replace, $before))) {
      if (file_put_contents($new, $after) === FALSE) {
        return FALSE;
      }
    }
  }

  return TRUE;
}

/**
 * Recursively builds an .info file structure from an array.
 *
 * @param $array
 *   The array to build the .info file from.
 * @param $prefix
 *   (Optional) Used internally to forward the current prefix (level of nesting)
 *   for the keys.
 *
 * @return string
 *   A .info file string.
 */
function drush_omega_compose_info_file($array, $prefix = FALSE) {
  $info = '';

  foreach ($array as $key => $value) {
    if (is_array($value)) {
      // This is an array, let's proceed with the next level.
      $info .= drush_omega_compose_info_file($value, (!$prefix ? $key : "{$prefix}[{$key}]"));
    }
    else {
      // Escape all single quotes.
      $value = str_replace("'", "\'", $value);
      // Wrap the value in single quotes if it has any trailing or leading
      // whitespace or it is an empty string from the start.
      $value = $value === '' || trim($value) != $value ? "'" . $value . "'" : $value;
      // If the key is numeric remove it entirely (simple brackets are enough in
      // this case).
      $key = is_numeric($key) ? '' : $key;

      $info .= $prefix ? ("{$prefix}[" . $key .']') : $key;
      $info .= ' = ' . $value . "\n";
    }
  }

  return $info;
}

/**
 * Creates a subtheme from any given starterkit.
 *
 * @param $subtheme
 *   An object containing all the required definitions for creating a subtheme.
 *   The available properties are:
 *
 *   - 'name': The human-readable name of the subtheme.
 *   - 'machine_name': The machine-readable name of the subtheme.
 *   - 'path': The destination that the subtheme should be placed in relative to
 *     the Drupal omega.
 *   - 'starterkit': An array describing the starterkit.
 *   - 'default': (optional) Whether the subtheme should be enabled and set as
 *     the default theme after it has been created.
 *   - 'enable': (optional) Whether the subtheme should be enabled after it has
 *     been created.
 *
 * @return bool
 *   TRUE if the subtheme could be successfully created, FALSE otherwise.
 */
function drush_omega_generate_subtheme($subtheme) {
  // Check whether the destination path does not exist and bail out if it does
  // so we don't delete any important data by accident.
  if (is_dir($subtheme->path)) {
    return drush_set_error('OMEGA_SUBTHEME_PATH', dt('The path @path already exists.', array('@path' => $subtheme->path)));
  }

  // Create a temporary directory so we don't leave any stale files if an
  // operation fails.
  $temporary = drush_op('drush_tempdir'). '/' . $subtheme->name;

  // Try to copy the starterkit to the destination path of the new subtheme.
  if (!drush_op('drush_copy_dir', $subtheme->starterkit['path'], $temporary)) {
    return drush_set_error('OMEGA_GENERATE_SUBTHEME', dt('Failed to generate subtheme.'));
  }

  // Delete the .starterkit.inc file.
  drush_op('drush_delete_dir', $temporary . '/' . basename($subtheme->starterkit['file']));

  // Parse the contents of the current .info file (provided by the starterkit).
  $info = $subtheme->starterkit['info'];

  // Put the name and description, as well as a 'dummy' version for the new
  // subtheme in place.
  $info['name'] = $subtheme->name;
  $info['base theme'] = $subtheme->basetheme;

  // Write to the new .info file.
  $file = $temporary . '/' . $subtheme->machine_name . '.info';
  if (!file_put_contents($file, drush_omega_compose_info_file($info))) {
    return drush_set_error('OMEGA_GENERATE_SUBTHEME', dt('Failed to generate subtheme.'));
  }

  // Optionally remove README.txt files.
  if (!$subtheme->readme) {
    foreach (file_scan_directory($temporary, '/^README\.txt$/') as $file) {
      drush_op('drush_delete_dir', $file->uri);
    }
  }

  // Recursively rewrite the file names and contents of all the files that are
  // now in the subtheme's directory to represent the human- and
  // machine-readable names of the subtheme.
  $search = array('{{ THEME }}', '{{ THEMENAME }}');
  $replace = array($subtheme->machine_name, $subtheme->name);
  if (!drush_op('drush_omega_rewrite_recursive', $temporary, $search, $replace)) {
    return drush_set_error('OMEGA_GENERATE_SUBTHEME', dt('Failed to generate subtheme.'));
  }

  // Move the new subtheme to its destination.
  if (!drush_op('drush_mkdir', dirname($subtheme->path)) || !drush_op('drush_move_dir', $temporary, $subtheme->path)) {
    return drush_set_error('OMEGA_GENERATE_SUBTHEME', dt('Failed to generate subtheme.'));
  }

  // Rebuild the theme caches so that  we can do some final tasks.
  drupal_theme_rebuild();
  system_rebuild_theme_data();

  if (!empty($subtheme->enable) || !empty($subtheme->default)) {
    // Enable the subtheme.
    theme_enable(array($subtheme->machine_name));

    if (!empty($subtheme->default)) {
      // Make the newly created subtheme the default theme.
      variable_set('theme_default', $subtheme->machine_name);
    }
  }

  return TRUE;
}

/**
 * Retrieve an array of available sites from the sites.php.
 *
 * @return array
 *   An array that contains all the available sites in a multisite environment.
 */
function drush_omega_sites() {
  $return = array('all' => 'all', 'default' => 'default');

  // Load the available sites (multisite installation) from the sites.php file
  // if it exists.
  if (file_exists(DRUPAL_ROOT . '/sites/sites.php')) {
    include DRUPAL_ROOT . '/sites/sites.php';

    if (isset($sites)) {
      $return = array_merge($return, $sites);
    }
  }

  // The 'all' and 'default' destinations are always available.
  return $return;
}

/**
 * Helper function for generating a valid machine-readable name for a theme from
 * any string.
 *
 * @param $string
 *   The string to generate the machine-readable name from.
 *
 * @return string
 *   The generated machine-readable name.
 */
function drush_omega_generate_theme_name($string) {
  // Machine-readable names have to start with a lowercase letter.
  $string = preg_replace('/^[^a-z]+/', '', strtolower($string));
  // Machine-readable names may only contain alphanumeric characters and
  // underscores.
  $string = preg_replace('/[^a-z0-9_]+/', '_', $string);
  // Trim all trailing and leading underscores.
  $string = trim($string, '_');

  $themes = list_themes();
  if (isset($themes[$string])) {
    $plain = $string;
    $counter = 0;

    while (isset($themes[$string])) {
      // Make sure that the machine-readable name of the theme is unique.
      $string = $plain . '_' . $counter++;
    }
  }

  return $string;
}
