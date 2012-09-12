<?php

/**
 * @file
 * Please supply a file description.
 */

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
      'destination' => dt('The destination of your subtheme. Defaults to "all" (sites/all/themes).'),
      'machine-name' => dt('The machine-readable name of your subtheme. This will be auto-generated from the human-readable name if omitted.'),
      'starterkit' => dt('The starterkit that your subtheme should use. Defaults to "omega_starterkit".'),
      'basetheme' => dt('Overrides the base theme defined in the starterkit.'),
      'enable' => dt('Automatically enable the subtheme after creation.'),
      'set-default' => dt('Automatically enable the subtheme after creation and make it the default theme.'),
    ),
    'examples' => array(
      'drush omega-subtheme "My Theme"' => dt('Creates a Omega subtheme called "My Theme".'),
      'drush omega-subtheme "My Theme" --destination=example.com' => dt('Creates a Omega subtheme called "My Theme" in sites/example.com/themes.'),
      'drush omega-subtheme "My Theme" --starterkit=my_custom_starterkit' => dt('Uses a custom starterkit to create a Omega subtheme called "My Theme" in sites/all/themes (default).'),
    ),
    'aliases' => array('osub'),
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

  $items['omega-layout'] = array(
    'description' => dt('Generates a layout from the starterkit.'),
    'arguments' => array(
      'theme' => dt('The machine-readable name of the theme to create the layout for.'),
      'layout' => dt('The name of your layout.'),
    ),
    'options' => array(
      'machine-name' => dt('The machine-readable name of the layout. This will be auto-generated from the human-readable name if omitted.'),
    ),
    'examples' => array(
      'drush omega-layout foo bar' => dt('Creates a layout "bar" for the "foo" theme.'),
    ),
    'aliases' => array('olay'),
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
    case 'drush:omega-export':
      return dt('Exports the theme settings of a given theme.');
    case 'drush:omega-revert':
      return dt('Reverts the theme settings of a given theme.');
    case 'drush:omega-layout':
      return dt('Generates a layout.');
  }
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_omega_subtheme_validate($name = NULL) {
  // Rebuild the theme data so that we can safely check for the existance of
  // themes by using the information provided by list_themes().
  system_rebuild_theme_data();

  // Retrieve the option values from the drush command.
  $destination = drush_get_option('destination', 'all');
  $starterkit = drush_get_option('starterkit', 'omega_starterkit');

  if ($machine_name = drush_get_option('machine-name')) {
    // Validate the machine-readable name of the theme.
    if (!preg_match('/^[a-z][a-z0-9_]*$/', $machine_name)) {
      return drush_set_error('OMEGA_THEME_NAME_INVALID', dt('The machine name is invalid. It may only contain lowercase numbers, letters and underscores and must start with a letter.'));
    }

    $themes = list_themes();
    // Validate that the machine-readable name of the theme is unique.
    if (isset($themes[$machine_name])) {
      return drush_set_error('OMEGA_THEME_ALREADY_EXISTS', dt('A theme with that name already exists. The machine-readable name must be unique.'));
    }
  }

  // Check if the destination is valid.
  if (!omega_drush_resolve_destination($destination)) {
    return drush_set_error('OMEGA_DESITINATION_INVALID', dt('The destination is invalid.'));
  }

  // Check if the chosen base theme or starterkit exists.
  if (!array_key_exists($starterkit, omega_drush_starterkits())) {
    return drush_set_error('OMEGA_STARTERKIT_NOT_EXISTS', dt('There is no valid Omega theme starterkit with the name @starterkit.', array('@starterkit' => $starterkit)));
  }

  if ($basetheme = drush_get_option('basetheme')) {
    if (!array_key_exists($basetheme, list_themes()) || !array_key_exists('omega', omega_theme_trail($basetheme))) {
      return drush_set_error('OMEGA_BASETHEME_INVALID', dt('The base theme @basetheme does not exist or is invalid.', array('@basetheme' => $basetheme)));
    }
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_omega_subtheme($name = NULL) {
  if (!isset($name)) {
    drush_prompt(dt('Please enter the name of the new sub-theme'));
  }

  $destination = drush_get_option('destination', 'sites/all');

  $subtheme = new stdClass();
  $subtheme->name = $name;
  $subtheme->machine_name = drush_get_option('machine-name', _omega_drush_generate_theme_name($name));
  $subtheme->path = omega_drush_resolve_destination($destination) . '/' . $subtheme->machine_name;
  $subtheme->starterkit = drush_get_option('starterkit', 'omega_starterkit');
  $subtheme->default = drush_get_option('set-default') !== NULL;
  $subtheme->enable = $subtheme->default || drush_get_option('enable') !== NULL;
  $subtheme->basetheme = drush_get_option('basetheme');

  if (omega_drush_subtheme_create($subtheme)) {
    drush_log(dt('You have successfully created the theme @theme.', array('@theme' => $subtheme->name)), 'success');
  }
}

/**
 * Resolves the destination path for a subtheme. This can either be a profile
 * theme folder, a sites theme folder or a theme.
 *
 * @param string $destination
 *   A destination string, this can either be a site path ('sites/foo'), a
 *   profile path ('profiles/foo') or the name of a theme.
 *
 * @return string|bool
 *   The full path to the given destination, FALSE if the destination could not
 *   be resolved.
 */
function omega_drush_resolve_destination($destination) {
  if (strpos($destination, 'sites/') === 0) {
    if (in_array(substr($destination, 6), omega_drush_sites())) {
      return $destination . '/themes';
    }
  }
  elseif (strpos($destination, 'profiles/') === 0) {
    require_once DRUPAL_ROOT . '/includes/install.core.inc';

    $profile = substr($destination, 9);
    $profiles = install_find_profiles();
    if (array_key_exists($profile, $profiles)) {
      return dirname($profiles[$profile]->uri) . '/themes';
    }
  }
  elseif (strpos($destination, 'themes/') === 0) {
    $theme = substr($destination, 7);

    if (array_key_exists($theme, list_themes())) {
      return drupal_get_path('theme', $theme);
    }
  }

  return FALSE;
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_omega_export_validate($theme = NULL) {
  $themes = list_themes();
  // Check if the given theme exists.
  if (isset($theme) && !isset($themes[$theme])) {
    return drush_set_error('OMEGA_THEME_NOT_EXISTS', dt('There is no theme with that name.'));
  }
}

/**
 * Implements drush_hook_COMMAND().
 *
 * Exports the theme settings for the given theme from the database and writes
 * them into the .info file of that theme.
 *
 * @param $name
 *   The machine-readable name of a theme.
 */
function drush_omega_export($theme = NULL) {
  if (!isset($theme) && !$theme = _omega_drush_theme_choice()) {
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
  $data = $settings ? $data . "\n\n" . omega_drush_build_info_file($settings, 'settings') : $data;

  // Write the data to the .info file of the theme.
  if (file_unmanaged_save_data($data, $path, FILE_EXISTS_REPLACE)) {
    drush_log(dt('The theme settings for the @theme theme have been exported to the .info file of the theme.', array('@theme' => $themes[$theme]->info['name'])), 'success');

    if (drush_get_option('revert')) {
      // Revert the theme settings if the 'revert' option is set and they have
      // been exported successfully. In this case, we invoke the API function
      // through the drush command to get the  print messages displazed.
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
  $themes = list_themes();
  // Check if the given theme exists.
  if (isset($theme) && !isset($themes[$theme])) {
    return drush_set_error('OMEGA_THEME_NOT_EXISTS', dt('There is no theme with that name.'));
  }
}

/**
 * Implements drush_hook_COMMAND().
 *
 * Delete the theme settings that have been stored in the database and thereby
 * reverts them to the default settings from the .info file.
 *
 * @param $name
 *   The machine-readable name of a theme.
 */
function drush_omega_revert($theme = NULL) {
  if (!isset($theme) && !$theme = _omega_drush_theme_choice()) {
    return;
  }

  $themes = list_themes();
  // Delete the theme settings variable for the given theme.
  variable_del('theme_' . $theme . '_settings');

  // Rebuild the theme data for good measure.
  drupal_theme_rebuild();
  system_rebuild_theme_data();

  drush_log(dt('You have successfully reverted the theme settings for the @theme theme.', array('@theme' => $themes[$theme]->info['name'])), 'success');
}

/**
 * Helper function for printing a list of available themes that the user can
 * choose from in a drush prompt.
 *
 * @return bool|string
 *   The machine-readable name of the chosen theme or FALSE if the operation was
 *   cancelled.
 */
function _omega_drush_theme_choice() {
  $themes = list_themes();
  $values = array_values($themes);
  $options = array();
  foreach ($values as $key => $info) {
    $options[$key] = $info->info['name'];
  }

  if (!$theme = drush_choice($options, dt('Which theme do you want to export the theme settings for?'))) {
    return FALSE;
  }

  return $values[$theme]->name;
}

/**
 * Retrieves all themes that can be used as starterkits for a given base theme.
 *
 * @param $base
 *   (Optional) The machine-readable name of a base theme to filter all the
 *   available starterkits for.
 *
 * @return array
 *   An array of theme names keyed with the machine-readable name of each theme.
 */
function omega_drush_starterkits($base = NULL) {
  $options = array();

  // Retrieve all the possible starterkits.
  foreach (list_themes() as $name => $theme) {
    $info = $theme->info;

    // A starterkit is always flagged as such in the .info file.
    if (!empty($info['starterkit']) && !empty($info['base theme']) && (!isset($base) || $info['base theme'] == $base)) {
      $options[$name] = $info['name'] . (!empty($info['description']) ? ': ' . $info['description'] : '');
    }
  }

  return $options;
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
function omega_drush_rewrite_recursive($path, $search, $replace) {
  if ($path !== ($new = str_replace($search, $replace, $path))) {
    // First, try to rename (move) the file if the name was changed.
    if (!drush_move_dir($path, $new, TRUE)) {
      return FALSE;
    };
  }

  if (is_dir($new)) {
    // If the file actually is a directory, proceed with the recursion.
    $directory = dir($new);

    while (FALSE !== ($read = $directory->read())) {
      if ($read != '.' && $read != '..' ) {
        if (!omega_drush_rewrite_recursive($new . '/' . $read, $search, $replace)) {
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
      if (file_unmanaged_save_data($after, $new, FILE_EXISTS_REPLACE) === FALSE) {
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
function omega_drush_build_info_file($array, $prefix = FALSE) {
  $info = '';

  foreach ($array as $key => $value) {
    if (is_array($value)) {
      // This is an array, let's proceed with the next level.
      $info .= omega_drush_build_info_file($value, (!$prefix ? $key : "{$prefix}[{$key}]"));
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
 * Helper function for creating and saving a .info file.
 *
 * @param $name
 *   The name of the .info file (equivalent to the machine-readable name of the
 *   theme that the .info file belongs to).
 * @param $data
 *   The content of the .info file.
 * @param $destination
 *   (Optional) The path in which the .info file should be saved. Defaults to
 *   the path of the theme as defined by $name.
 *
 * @return bool
 *   TRUE if the .info file was successfully saved, FALSE otherwise.
 */
function omega_drush_write_info_file($name, $data, $destination = NULL) {
  $destination = isset($destination) ? $destination : drupal_get_path('theme', $name);

  if (!empty($destination)) {
    $data = is_array($data) ? omega_drush_build_info_file($data) : $data;
    return file_unmanaged_save_data($data, $destination . '/' . $name . '.info', FILE_EXISTS_REPLACE);
  }

  return FALSE;
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
 *   - 'starterkit': The machine-readable name of the starterkit that should be
 *     used for creating the subtheme.
 *   - 'default': (optional) Whether the subtheme should be enabled and set as
 *     the default theme after it has been created.
 *   - 'enable': (ptional) Whether the subtheme should be enabled after it has
 *     been created.
 *
 * @return bool
 *   TRUE if the subtheme could be successfully created, FALSE otherwise.
 */
function omega_drush_subtheme_create($subtheme) {
  // Check whether the destination path does not exist and bail out if it does
  // so we don't delete any important data by accident.
  if (is_dir($subtheme->path)) {
    return drush_set_error('OMEGA_SUBTHEME_PATH', dt('The path @path already exists.', array('@path' => $subtheme->path)));
  }

  $destination = dirname($subtheme->path);
  if (!is_writable($destination) && !file_prepare_directory($destination, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
    return drush_set_error('OMEGA_SUBTHEME_PATH', dt('The path @path is not writable.', array('@path' => $subtheme->path)));
  }

  $themes = list_themes();
  // Try to copy the starterkit to the destination path of the new subtheme.
  $source = drupal_get_path('theme', $subtheme->starterkit);
  if (!drush_copy_dir($source, $subtheme->path)) {
    return drush_set_error('OMEGA_STARTERKIT', t('The starterkit @starterkit could not be copied.', array('@starterkit' => $themes[$subtheme->starterkit]->info['name'])));
  }

  // Recursively rewrite the file names and contents of all the files that are
  // now in the subtheme's directory to represent the human- and
  // machine-readable names of the subtheme.
  $relative = _omega_relative_path($subtheme->path, drupal_get_path('theme', $themes[$subtheme->starterkit]->info['base theme']));
  $search = array($subtheme->starterkit, $themes[$subtheme->starterkit]->info['name'], 'BASETHEME_PATH_RELATIVE');
  $replace = array($subtheme->machine_name, $subtheme->name, $relative);
  omega_drush_rewrite_recursive($subtheme->path, $search, $replace);

  // Parse the contents of the current .info file (provided by the starterkit).
  $subtheme->info = drupal_parse_info_file($subtheme->path . '/' . $subtheme->machine_name . '.info');
  // Unset all unneeded (and potentially hindering) properties in the .info file
  // of the subtheme.
  unset($subtheme->info['starterkit'], $subtheme->info['hidden'], $subtheme->info['locked'], $subtheme->info['project'], $subtheme->info['datestamp']);

  // Put the name and description, as well as a 'dummy' version for the new
  // subtheme in place.
  $subtheme->info['name'] = $subtheme->name;
  $subtheme->info['description'] = $subtheme->description;
  $subtheme->info['version'] = '1.x';

  if (!empty($subtheme->basetheme)) {
    $subtheme->info['base theme'] = $subtheme->basetheme;
  }

  // Overwrite the existing .info file with the changed values.
  omega_drush_write_info_file($subtheme->machine_name, $subtheme->info, $subtheme->path);

  // Rebuild the theme caches so that we can do some final tasks.
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
 * @return
 *   An array that contains all the available sites in a multisite environment.
 */
function omega_drush_sites() {
  $sites = array('all' => 'all');

  // Load the available sites (multisite installation) from the sites.php file
  // if it exists.
  if (file_exists(DRUPAL_ROOT . '/sites/sites.php')) {
    include DRUPAL_ROOT . '/sites/sites.php';
  }

  // The 'all' destination is always available.
  return array_keys(array_merge($sites));
}

/**
 * Helper function for generating a valid machine-readable name for a theme from
 * any string.
 *
 * @param $name
 *   The string to generate the machine-readable name from.
 *
 * @return string
 *   The generated machine-readable name.
 */
function _omega_drush_generate_theme_name($string) {
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

/**
 * Helper function for retrieving the relative path between two endpoints.
 */
function _omega_relative_path($from, $to) {
  $from = explode('/', $from);
  $to = explode('/', $to);
  $relative = $to;

  foreach ($from as $depth => $dir) {
    // Find the first non-matching directory.
    if ($dir === $to[$depth]) {
      // Ignore this directory.
      array_shift($relative);
    }
    else {
      // Get the number of remaining directories to $from.
      $remaining = count($from) - $depth;

      if ($remaining > 0) {
        // Add traversals up to the first matching directory.
        $length = (count($relative) + $remaining) * -1;
        $relative = array_pad($relative, $length, '..');
        break;
      }
      else {
        $relative[0] = './' . $relative[0];
      }
    }
  }

  return implode('/', $relative);
}
