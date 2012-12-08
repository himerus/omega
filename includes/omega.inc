<?php

/**
 * @file
 * Helper functions for the Omega base theme.
 */

/**
 * Builds the full theme trail (deepest base theme first, subtheme last) for a
 * theme.
 *
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 *
 * @return array
 *   An array of all themes in the trail, keyed by theme key.
 */
function omega_theme_trail($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $cache = &drupal_static(__FUNCTION__);

  if (!isset($cache[$theme])) {
    $cache[$theme] = array();

    if ($theme == $GLOBALS['theme'] && isset($GLOBALS['theme_info']->base_themes)) {
      $cache[$theme] = $GLOBALS['theme_info']->base_themes;
    }

    $themes = list_themes();
    if (empty($cache[$theme]) && isset($themes[$theme]->info['base theme'])) {
      $cache[$theme] = system_find_base_themes($themes, $theme);
    }

    // Add our current subtheme ($key) to that array.
    $cache[$theme][$theme] = $themes[$theme]->info['name'];
  }

  return $cache[$theme];
}

/**
 * Pre-processes CSS files so that CSS files that have 'preprocess_media' set to
 * TRUE are set to media="all" while having their former media query added to
 * the file content.
 *
 * @param $elements
 *   An array of CSS files as in drupal_pre_render_styles().
 *
 * @return array
 *   An array of preprocessed CSS files.
 *
 * @see drupal_pre_render_styles()
 */
function omega_css_preprocessor($elements) {
  foreach ($elements['#items'] as &$item) {
    if ($item['type'] == 'file' && $item['preprocess'] && $item['media'] != 'all') {
      $item['data'] = omega_css_cache_media_queries($item);
      $item['media'] = 'all';
    }
  }

  return $elements;
}

/**
 * Optimizes CSS aggregation by creating a cached version of each CSS file that,
 * instead of using the 'media' attribute on the styles tag, writes the media
 * query into the file itself using the '@media { ... }' syntax.
 *
 * This prevents unnecessary sprouting of new CSS aggregation.
 *
 * @see drupal_build_css_cache().
 */
function omega_css_cache_media_queries($item) {
  $map = variable_get('drupal_css_cache_files', array());
  $key = hash('sha256', serialize($item));
  $uri = isset($map[$key]) ? $map[$key] : NULL;

  if (empty($uri) || !file_exists($uri)) {
    // Build the base URL of this CSS file: start with the full URL.
    $base = file_create_url($item['data']);
    $base = substr($base, 0, strrpos($base, '/'));

    if (substr($base, 0, strlen($GLOBALS['base_theme'])) == $GLOBALS['base_theme']) {
      $base = substr($base, strlen($GLOBALS['base_theme']));
    }

    _drupal_build_css_path(NULL, $base . '/');

    $data = drupal_load_stylesheet($item['data'], TRUE);

    // Anchor all paths in the CSS with its base URL, ignoring external and absolute paths.
    $data = preg_replace_callback('/url\(\s*[\'"]?(?![a-z]+:|\/+)([^\'")]+)[\'"]?\s*\)/i', '_drupal_build_css_path', $data);
    $data = '@media ' . $item['media'] . '{' . $data . '}';

    // Create the css/ within the files folder.
    $directory = 'public://css';
    $uri = $directory . '/css_' . drupal_hash_base64($data) . '.css';

    // Create the CSS file.
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
    if (!file_exists($uri) && !file_unmanaged_save_data($data, $uri, FILE_EXISTS_REPLACE)) {
      return FALSE;
    }

    // If CSS gzip compression is enabled, clean URLs are enabled (which means
    // that rewrite rules are working) and the zlib extension is available then
    // create a gzipped version of this file. This file is served conditionally
    // to browsers that accept gzip using .htaccess rules.
    if (variable_get('css_gzip_compression', TRUE) && variable_get('clean_url', 0) && extension_loaded('zlib')) {
      if (!file_exists($uri . '.gz') && !file_unmanaged_save_data(gzencode($data, 9, FORCE_GZIP), $uri . '.gz', FILE_EXISTS_REPLACE)) {
        return FALSE;
      }
    }

    // Save the updated map.
    $map[$key] = $uri;

    // Write the updated map into the variable.
    variable_set('drupal_css_cache_files', $map);
  }

  return $uri;
}

/**
 * Helper function for eliminating elements from an array using a simplified
 * regex pattern.
 *
 * @param $elements
 *   The array of elements that should have some elements nuked.
 * @param $exclude
 *   An array of strings that should be matched against the keys of the array
 *   of elements.
 *
 * @return array
 *   The purged array.
 */
function omega_exclude_assets(&$elements, $exclude) {
  // For optimization reasons we load the theme trail to check whether a
  // namespace matches the machine-readable name of one of the themes in the
  // trail.
  $trail = omega_theme_trail();

  foreach ($exclude as $item) {
    $path = '';
    // The first segment (everything before the first slash) is the namespace.
    list($namespace) = explode('/', $item);

    // Check if the namespace refers to a file residing in the 'misc' folder.
    if ($namespace == 'misc') {
      $path = DRUPAL_ROOT . '/misc';
    }
    // Check if the namespace refers to a theme.
    elseif (array_key_exists($namespace, $trail)) {
      $path = drupal_get_path('theme', $namespace);
    }
    else {
      // Otherwise, check if it refers to a module, profile or theme engine.
      foreach (array('module', 'profile', 'theme_engine') as $type) {
        if ($path = drupal_get_path($type, $namespace)) {
          break;
        }
      }
    }

    // If a namespace could be identified, use its path as a prefix, otherwise
    // use the plain file path as provided.
    $item = $path ? $path . '/' . substr($item, strlen($namespace) + 1) : $item;
    $item = preg_quote($item, '/');
    // Turn the * wildcards into actual regex wildcards and make sure that, if
    // a .css file is targeted directly we are also removing the RTL version of
    // that file.
    $item = str_replace(array('\*', '\.css'), array('(.*)', '(\.css|-rtl\.css)'), $item);

    // Look up all elements that match this exclusion pattern.
    $filtered = preg_grep("/^$item$/", array_keys($elements));
    $elements = array_diff_key($elements, array_flip($filtered));
  }
}

/**
 * Retrieves the array of enabled extensions for a theme. Extensions can be
 * registered through the .info file. Each extension can define a theme settings
 * form altering function named
 * 'THEMENAME_extension_EXTENSION_theme_settings_form_alter()' through a file
 * named 'THEME_ROOT/includes/EXTENSION/EXTENSION.settings.inc' to have it
 * automatically included whenever the theme settings form is displayed. Each
 * extension can also define a
 * 'THEMENAME_extension_EXTENSION_theme_registry_alter()' function through a
 * file named 'THEME_ROOT/includes/EXTENSION/EXTENSION.inc' to register custom
 * hooks with the theme registry.
 *
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 * @param $all
 *   (Optional) If TRUE, all extensions are included. If FALSE, only enabled
 *   extensions are included.
 *
 * @return array
 *   The theme info array of the passed or current theme.
 *
 * @see _system_default_theme_features()
 * @see omega_extension_development_theme_settings_form_alter()
 * @see omega_extension_development_theme_registry_alter()
 */
function omega_extensions($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $extensions = drupal_static('__FUNCTION__');

  if (!isset($extensions[$theme])) {
    $extensions[$theme] = _omega_discovery('extension', $theme);
    $context = $theme;

    // Give modules and themes a chance to alter the extensions info array.
    drupal_alter('omega_extensions', $extensions[$theme], $context);
  }

  return $extensions[$theme];
}

/**
 * Looks up the info array of all themes in the theme trail and retrieves a
 * particular info array element.
 */
function omega_theme_trail_info($element, $merge = TRUE, $theme = NULL) {
  $output = array();

  // Loop over all themes in the theme trail and look up $element in the .info
  // array.
  foreach (array_reverse(omega_theme_trail($theme)) as $key => $name) {
    $info = omega_theme_info($key);

    // If $merge is TRUE we combine all the results of all themes in the theme
    // trail. Otherwise we just return the first occurrence.
    if (isset($info[$element]) && is_array($info[$element])) {
      $output = array_merge($info[$element], $output);

      if (!$merge) {
        return array('theme' => $key, 'info' => $output);
      }
    }
  }

  return $output;
}

/**
 * Retrieves the full info array of a theme.
 *
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 *
 * @return array
 *   The theme info array of the passed or current theme.
 */
function omega_theme_info($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];

  // If this is the current theme, just load the theme info from the globals.
  // Note: The global 'theme_key' property is not reliable in this case because
  // it gets overridden on theme settings pages.
  if ($theme == $GLOBALS['theme']) {
    return $GLOBALS['theme_info']->info;
  }

  $themes = list_themes();
  return $themes[$theme]->info;
}

/**
 * Includes all occurrences of a file in all themes in the theme trail. This is
 * very similar to module_load_include().
 *
 * @param $type
 *   The include file's type (file extension).
 * @param $name
 *   The base file name (without the $type extension).
 * @param $all
 *   (Optional) Whether all occurrences of this file should be loaded or just
 *   the first. Defaults to TRUE (all).
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 *
 * @see module_load_include()
 */
function omega_theme_trail_load_include($type, $name, $all = TRUE, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $trail = array_reverse(omega_theme_trail($theme));

  foreach ($trail as $key => $title) {
    // Check if the requested file exists in any of the themes in the theme
    // trail and include all occurrences.
    $file = DRUPAL_ROOT . '/' . drupal_get_path('theme', $key) . "/$name.$type";
    if (is_file($file)) {
      include_once $file;

      // Exit right here if we only want the first occurrence of this file to
      // be loaded.
      if (!$all) {
        return;
      }
    }
  }
}

/**
 * Invoke a hook in all themes in the theme trail that implement it.
 *
 * @param $hook
 *   The name of the hook to invoke.
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 * @param ...
 *   Arguments to pass to the hook.
 *
 * @return array
 *   An array of return values of the hook implementations. If themes return
 *   arrays from their implementations, those are merged into one array.
 *
 * @see module_invoke_all()
 */
function omega_invoke_all($hook, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];

  $args = func_get_args();
  // Remove $hook from the arguments.
  unset($args[0], $args[1]);

  $return = array();
  foreach (omega_theme_trail($theme) as $key => $name) {
    $function = $key . '_' . $hook;

    if (function_exists($function)) {
      $result = call_user_func_array($function, array_merge(array($theme), array_values($args)));
      if (isset($result) && is_array($result)) {
        // Append the 'theme' property to each array element.
        foreach ($result as &$item) {
          $item['theme'] = $key;
        }
        $return = array_merge_recursive($return, $result);
      }
      elseif (isset($result)) {
        $return[] = $result;
      }
    }
  }
  return $return;
}

/**
 * Custom implementation of drupal_array_get_nested_value() that also supports
 * objects instead of just arrays.
 *
 * @param $object
 *   The array or object from which to get the value.
 * @param $parents
 *   An array of parent keys of the value, starting with the outermost key.
 * @param $key_exists
 *   (optional) If given, an already defined variable that is altered by
 *   reference.
 *
 * @return mixed
 *   The requested nested value. Possibly NULL if the value is NULL or not all
 *   nested parent keys exist. $key_exists is altered by reference and is a
 *   Boolean that indicates whether all nested parent keys exist (TRUE) or not
 *   (FALSE). This allows to distinguish between the two possibilities when NULL
 *   is returned.
 *
 * @see drupal_array_get_nested_value()
 */
function omega_get_nested_value(&$object, array $parents, &$key_exists = NULL) {
  $ref = &$object;
  foreach ($parents as $parent) {
    if (is_array($ref) && array_key_exists($parent, $ref)) {
      $ref = &$ref[$parent];
    }
    elseif (is_object($ref) && property_exists($ref, $parent)) {
      $ref = &$ref->$parent;
    }
    else {
      $key_exists = FALSE;
      return NULL;
    }
  }
  $key_exists = TRUE;
  return $ref;
}

/**
 * Retrieves the info array for all available layouts.
 *
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 *
 * @return array
 *   An array of available layouts for the given theme.
 */
function omega_layouts_info($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $layouts = drupal_static('__FUNCTION__');

  if (!isset($layouts[$theme])) {
    $layouts[$theme] = _omega_discovery('layout', $theme);

    // A theme or base theme can explicitly restrict the available layouts to
    // a subset defined through the .info file.
    if ($filter = omega_theme_trail_info('layouts', TRUE, $theme)) {
      $layouts[$theme] = array_intersect_key($layouts[$theme], array_flip($filter));
    }

    foreach ($layouts[$theme] as $layout => &$info) {
      $info['attached'] = array();

      // Look up possible CSS and JS file overrides.
      if (isset($info['info']['stylesheets'])) {
        foreach ($info['info']['stylesheets'] as $media => $files) {
          foreach ($files as $key => $file) {
            $info['attached']['css'][$key] = array(
              'media' => $media,
              'data' => $info['path'] . '/' . $file,
              'group' => CSS_THEME,
              'every_page' => TRUE,
              'weight' => -10,
            );
          }
        }
      }

      // Look up possible CSS and JS file overrides.
      if (isset($info['info']['scripts'])) {
        foreach ($info['info']['scripts'] as $key => $file) {
          $info['attached']['js'][$key] = array(
            'data' => $info['path'] . '/' . $file,
            'group' => JS_THEME,
            'every_page' => TRUE,
            'weight' => -10,
          );
        }
      }
    }

    $context = $theme;

    // Give modules and themes a chance to alter the layout info array.
    drupal_alter('omega_layouts_info', $layouts[$theme], $context);
  }

  return $layouts[$theme];
}

/**
 * Retrieves the active layout for the current page.
 *
 * @return array|bool
 *   The info array for the active layout or FALSE if the current page does not
 *   use an alternative page layout.
 */
function omega_layout() {
  $cache = &drupal_static(__FUNCTION__);

  if (!isset($cache)) {
    $cache = FALSE;
    // Load the default layout from the theme settings.
    $layout = theme_get_setting('omega_layout');
    drupal_alter('omega_layout', $layout);

    $registry = theme_get_registry();
    if (isset($registry['page__layout__' . $layout]['layout'])) {
      $cache = $registry['page__layout__' . $layout]['layout'];
    }
  }

  return $cache;
}

/**
 * Allow themes to easily define libraries.
 *
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 *
 * @return array
 *   An array of libraries defined by themes in the theme trail of the given
 *   theme.
 */
function omega_theme_libraries_info($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $libraries = drupal_static('__FUNCTION__');

  if (!isset($libraries[$theme])) {
    $libraries[$theme] = array();

    foreach (omega_invoke_all('omega_theme_libraries_info') as $library => $info) {
      $libraries[$theme][$library] = $info;
    }

    $context = $theme;

    // Give modules and themes a chance to alter the libraries info array.
    drupal_alter('omega_theme_libraries_info', $libraries[$theme], $context);
  }

  return $libraries[$theme];
}

/**
 * Iterates over all themes in the theme trail in order to find an
 * implementation of a specific file and returns the first occurance of that
 * file. Sub-theme implementations override base-theme implementations.
 *
 * @param $file
 *   The name of the file.
 * @param $path
 *   (Optional) The path to the file (relative to the theme root).
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 *
 * @return bool|string
 *   The path to the file or FALSE if no implementation exists.
 */
function omega_theme_trail_find_file($file, $path = NULL, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];

  foreach (array_reverse(omega_theme_trail($theme)) as $key => $name) {
    $current = drupal_get_path('theme', $key) . (isset($path) ? '/' . $path : '') . '/' . $file;

    if (is_file($current)) {
      return $current;
    }
  }

  return FALSE;
}


/**
 * Iterates over all themes in the theme trail in order to find all
 * implementations of a specific file.
 *
 * @param $file
 *   The name of the file.
 * @param $path
 *   (Optional) The path to the file (relative to the theme root).
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 *
 * @return array
 *   An array containing the pathes to all implementations of the given file.
 */
function omega_theme_trail_find_files($file, $path = NULL, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];

  $files = array();
  foreach (omega_theme_trail($theme) as $key => $name) {
    $current = drupal_get_path('theme', $key) . (isset($path) ? '/' . $path : '') . '/' . $file;

    if (is_file($current)) {
      $files[$key] = $current;
    }
  }

  return $files;
}

/**
 * Helper function for discovering layouts, extensions or other pluggins of any
 * sort in the theme trail.
 *
 * @param $type
 *   A theme extension type (e.g. layout or extension).
 * @param $theme
 *   (Optional) The key (machine-readable name) of a theme. Defaults to the key
 *   of the current theme.
 *
 * @return array
 *   An array containing the discovered definitions.
 */
function _omega_discovery($type, $theme = NULL) {
  $discovery = array();
  $strlen = strlen($type) + 1;

  foreach (array_reverse(omega_theme_trail($theme)) as $key => $label) {
    $path = drupal_get_path('theme', $key);

    // Support files without '.inc' extension for backwards compatibilty.
    foreach (file_scan_directory($path, '/\.' . $type . '(\.inc)?$/', array('key' => 'name')) as $name => $file) {
      if (substr($name, -$strlen) === '.' . $type) {
        $name = substr($name, 0, strlen($name) - $strlen);
      }

      // The name 'starterkit' is reserved for actual starterkits.
      if ($name == 'starterkit') {
        continue;
      }

      $discovery[$name] = array(
        'name' => $name,
        'path' => dirname($file->uri),
        'info' => drupal_parse_info_file($file->uri),
        'theme' => $key,
      );
    }
  }

  return $discovery;
}