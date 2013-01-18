<?php

/**
 * @file
 * Template overrides and (pre-)process hooks for the Omega base theme.
 */

require_once dirname(__FILE__) . '/includes/omega.inc';
require_once dirname(__FILE__) . '/includes/scripts.inc';

if ($GLOBALS['theme'] === $GLOBALS['theme_key'] && ($GLOBALS['theme'] == 'omega' || (!empty($GLOBALS['base_theme_info']) && $GLOBALS['base_theme_info'][0]->name == 'omega'))) {
  // Slightly hacky performance tweak for theme_get_setting(). This resides
  // outside of any function declaration to make sure that it runs directly
  // after the theme has been initialized.
  //
  // Instead of rebuilding the theme settings array on every page load we are
  // caching the content of the static cache in the database after it has been
  // built initially. This is quite a bit faster than running all the code in
  // theme_get_setting() on every page.
  //
  // By checking whether the global 'theme' and 'theme_key' properties are
  // identical we make sure that we don't interfere with any of the theme
  // settings pages and only use this feature when actually rendering a page
  // with this theme.
  //
  // @see theme_get_setting()
  if (!$static = &drupal_static('theme_get_setting')) {
    if ($cache = cache_get('theme_settings:' . $GLOBALS['theme'])) {
      // If the cache entry exists, populate the static theme settings array
      // with its data. This prevents the theme settings from being rebuilt on
      // every page load.
      $static[$GLOBALS['theme']] = $cache->data;
    }
    else {
      // Invoke theme_get_setting() with a random argument to build the theme
      // settings array and populate the static cache.
      theme_get_setting('foo');
      // Extract the theme settings from the previously populated static cache.
      $static = &drupal_static('theme_get_setting');

      // Cache the theme settings in the database.
      cache_set('theme_settings:' . $GLOBALS['theme'], $static[$GLOBALS['theme']]);
    }
  }

  // Managing debugging (flood) messages and a few development tasks. This also
  // lives outside of any function declaration to make sure that the code is
  // executed before any theme hooks.
  if (omega_extension_enabled('development') && user_access('administer site configuration')) {
    if (variable_get('theme_' . $GLOBALS['theme'] . '_settings') && flood_is_allowed('omega_' . $GLOBALS['theme'] . '_theme_settings_warning', 3)) {
      // Alert the user that the theme settings are served from a variable.
      flood_register_event('omega_' . $GLOBALS['theme'] . '_theme_settings_warning');
      drupal_set_message(t('The settings for this theme are currently served from a variable. You might want to export them to your .info file.'), 'warning');
    }

    // Rebuild the theme registry / aggregates on every page load if the
    // development extension is enabled and configured to do so.
    if (omega_theme_get_setting('omega_rebuild_theme_registry', FALSE)) {
      // Rebuild the theme data.
      system_rebuild_theme_data();
      // Rebuild the theme registry.
      drupal_theme_rebuild();

      if (flood_is_allowed('omega_' . $GLOBALS['theme'] . '_rebuild_registry_warning', 3)) {
        // Alert the user that the theme registry is being rebuilt on every
        // request.
        flood_register_event('omega_' . $GLOBALS['theme'] . '_rebuild_registry_warning');
        drupal_set_message(t('The theme registry is being rebuilt on every request. Remember to <a href="!url">turn off</a> this feature on production websites.', array("!url" => url('admin/appearance/settings/' . $GLOBALS['theme']))), 'warning');
      }
    }

    if (omega_theme_get_setting('omega_rebuild_aggregates', FALSE) && variable_get('preprocess_css', FALSE) && (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update')) {
      foreach (array('css', 'js') as $type) {
        variable_del('drupal_' . $type . '_cache_files');

        foreach (file_scan_directory('public://' . $type . '', '/.*/') as $file) {
          // Delete files that are older than 20 seconds.
          if (REQUEST_TIME - filemtime($file->uri) > 20) {
            file_unmanaged_delete($file->uri);
          }
        };
      }

      if (flood_is_allowed('omega_' . $GLOBALS['theme'] . '_rebuild_aggregates_warning', 3)) {
        // Alert the user that the theme registry is being rebuilt on every
        // request.
        flood_register_event('omega_' . $GLOBALS['theme'] . '_rebuild_aggregates_warning');
        drupal_set_message(t('The CSS and JS aggregates are being rebuilt on every request. Remember to <a href="!url">turn off</a> this feature on production websites.', array("!url" => url('admin/appearance/settings/' . $GLOBALS['theme']))), 'warning');
      }
    }
  }
}

/**
 * Implements hook_system_info_alter().
 */
function omega_system_info_alter(&$info, $file, $type) {
  if ($type == 'theme' && array_key_exists('omega', omega_theme_trail($file->name))) {
    foreach (omega_layouts_info($file->name) as $layout) {
      foreach ($layout['info']['regions'] as $region => $description) {
        if (!isset($info['regions'][$region])) {
          $info['regions'][$region] = $description;
        }
      }
    }
  }
}

/**
 * Implements hook_process().
 *
 * Added through omega_theme_registry_alter() to synchronize the attributes
 * array with the classes array.
 */
function omega_enforce_attributes(&$variables) {
  // Copy over the classes array into the attributes array.
  if (!empty($variables['classes_array'])) {
    $variables['attributes_array']['class'] = !empty($variables['attributes_array']['class']) ? array_merge($variables['attributes_array']['class'], $variables['classes_array']) : $variables['classes_array'];
    $variables['attributes_array']['class'] = array_unique($variables['attributes_array']['class']);
  }

  // Sync that with the classes array. Remember: We don't recommend using it.
  if (!empty($variables['attributes_array']['class'])) {
    $variables['classes_array'] = $variables['attributes_array']['class'];
  }
}

/**
 * Implements hook_element_info_alter().
 */
function omega_element_info_alter(&$elements) {
  if (omega_extension_enabled('css') && omega_theme_get_setting('omega_media_queries_inline', TRUE) && variable_get('preprocess_css', FALSE) && (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update')) {
    array_unshift($elements['styles']['#pre_render'], 'omega_css_preprocessor');
  }

  $elements['scripts'] = array(
    '#items' => array(),
    '#pre_render' => array('omega_pre_render_scripts'),
    '#group_callback' => 'omega_group_js',
    '#aggregate_callback' => 'omega_aggregate_js',
  );
}

/**
 * Implements hook_css_alter().
 *
 * The backported CSS files have been copied from the Aurora theme. Huge props
 * to Sam Richard (Snugug) for chasing Drupal 8 HEAD!
 *
 * Backports the following CSS changes made to Drupal 8:
 * - #1216950: Clean up the CSS for Block module.
 * - #1216948: Clean up the CSS for Aggregator module.
 * - #1216972: Clean up the CSS for Color module.
 */
function omega_css_alter(&$css) {
  $omega = drupal_get_path('theme', 'omega');

  // The CSS_SYSTEM aggregation group doesn't make any sense. Therefore, we are
  // pre-pending it to the CSS_DEFAULT group. This has the same effect as giving
  // it a separate (low-weighted) group but also allows it to be aggregated
  // together with the rest of the CSS.
  foreach ($css as &$item) {
    if ($item['group'] == CSS_SYSTEM) {
      $item['group'] = CSS_DEFAULT;
      $item['weight'] = $item['weight'] - 100;
    }
  }

  // Clean up core and contrib module CSS.
  $overrides = array(
    'aggregator' => array(
      'aggregator.css' => array(
        'theme' => 'aggregator.theme.css',
      ),
      'aggregator-rtl.css' => array(
        'theme' => 'aggregator.theme-rtl.css',
      ),
    ),
    'block' => array(
      'block.css' => array(
        'admin' => 'block.admin.css',
      ),
    ),
    'color' => array(
      'color.css' => array(
        'admin' => 'color.admin.css',
      ),
      'color-rtl.css' => array(
        'admin' => 'color.admin-rtl.css',
      ),
    ),
  );

  // Check if we are on an admin page. Otherwise, we can skip admin CSS.
  $types = path_is_admin(current_path()) ? array('base', 'theme', 'admin') : array('base', 'theme');

  // Override module provided CSS with clean and modern alternatives provided
  // by Omega.
  foreach ($overrides as $module => $files) {

    // We gathered the CSS files with paths relative to the providing module.
    $module = drupal_get_path('module', $module);

    foreach ($files as $file => $items) {
      if (isset($css[$module . '/' . $file])) {
        // Keep a copy of the original file array so we can merge that with our
        // overrides in order to keep the 'weight' and 'group' declarations.
        $original = $css[$module . '/' . $file];
        unset($css[$module . '/' . $file]);

        // Omega 4.x tries to follow the pattern described in
        // http://drupal.org/node/1089868 for declaring CSS files. Therefore, it
        // may take more than a single file to override a .css file added by
        // core. This gives us better granularity when overriding .css files
        // in a sub-theme.
        foreach ($types as $type) {
          if (isset($items[$type])) {
            $css[$omega . '/css/aggregator/' . $items[$type]] = array(
              'data' => $omega . '/css/aggregator/' . $items[$type],
            ) + $original;
          }
        }
      }
    }
  }

  // Exclude CSS files as declared in the theme settings.
  if (omega_extension_enabled('css') && $exclude = omega_theme_get_setting('omega_css_exclude', array())) {
    omega_exclude_assets($css, $exclude);
  }
}

/**
 * Implements hook_js_alter().
 */
function omega_js_alter(&$js) {
  if (!omega_extension_enabled('scripts')) {
    return;
  }

  if ($exclude = omega_theme_get_setting('omega_js_exclude', array())) {
    omega_exclude_assets($js, $exclude);
  }

  // Move all the JavaScript to the footer if the theme is configured that way.
  if (omega_theme_get_setting('omega_js_footer', TRUE)) {
    foreach ($js as &$item) {
      // JavaScript libraries should never be moved to the footer.
      if ($item['group'] == JS_LIBRARY) {
        continue;
      }

      if (empty($item['force header'])) {
        $item['scope'] = 'footer';
      }
    }
  }
}

/**
 * Implements hook_theme().
 */
function omega_theme() {
  $info = array();

  if (omega_extension_enabled('layouts') && $layouts = omega_layouts_info()) {
    foreach ($layouts as $key => $layout) {
      if (!isset($info['page__layout__' . $key])) {
        $info['page__layout__' . $key] = array(
          'template' => $key . '.layout',
          'path' => $layout['path'],
          'base hook' => 'page',
        );
      }

      $info['page__layout__' . $key]['layout'] = $layout;
    }
  }

  return $info;
}

/**
 * Implements hook_theme_registry_alter().
 */
function omega_theme_registry_alter(&$registry) {
  // We prefer the attributes array instead of the plain classes array used by
  // many core and contrib modules. In Drupal 8, we are going to convert all
  // occurances of that into an attributes object. For now, we simply
  // synchronize our attributes array with the classes array to encourage
  // themers to use it.
  foreach ($registry as $hook => $item) {
    if (empty($item['base hook']) && !isset($item['function'])) {
      array_unshift($registry[$hook]['process functions'], 'omega_enforce_attributes');
    }
  }

  // Allow themers to split preprocess / process / theme code across separate
  // files to keep the main template.php file clean. This is really fast because
  // it uses the theme registry to cache the paths to the files that it finds.
  foreach (omega_theme_trail() as $key => $theme) {
    foreach (array('preprocess', 'process', 'theme') as $type) {
      $path = drupal_get_path('theme', $key);
      // Only look for files that match the 'something.preprocess.inc' pattern.
      $mask = '/.' . $type . '.inc$/';
      // This is the length of the suffix (e.g. '.preprocess') of the basename
      // of a file.
      $strlen = -(strlen($type) + 1);

      // Recursively scan the folder for the current step for (pre-)process
      // files and write them to the registry.
      foreach (file_scan_directory($path . '/' . $type, $mask) as $item) {
        $hook = strtr(substr($item->name, 0, $strlen), '-', '_');

        if (array_key_exists($hook, $registry)) {
          // Template files override theme functions.
          if (($type == 'theme' && isset($registry[$hook]['template']))) {
            continue;
          }

          // Name of the function (theme hook or theme function).
          $function = $type == 'theme' ? $key . '_' . $hook : $key . '_' . $type . '_' . $hook;

          // Load the file once so we can check if the function exists.
          require_once $item->uri;

          // Proceed if the callback doesn't exist.
          if (!function_exists($function)) {
            continue;
          }

          // By adding this file to the 'includes' array we make sure that it is
          // available when the hook is executed.
          $registry[$hook]['includes'][] = $item->uri;

          if ($type == 'theme') {
            $registry[$hook]['type'] = $key == $GLOBALS['theme'] ? 'theme_engine' : 'base_theme_engine';
            $registry[$hook]['theme path'] = $path;

            // Replace the theme function.
            $registry[$hook]['function'] = $function;
          }
          else {
            // Append the included preprocess hook to the array of functions.
            $registry[$hook][$type . ' functions'][] = $function;
          }
        }
      }
    }
  }

  // Include the main extension file for every enabled extension. This is
  // required for the next step (allowing extensions to register hooks in the
  // theme registry).
  foreach (omega_extensions() as $extension => $info) {
    // Load all the implementations for this extensions and invoke the according
    // hooks.
    if (omega_extension_enabled($extension)) {
      $file = $info['path'] . '/' . $extension . '.inc';

      if (is_file($file)) {
        require_once $file;
      }

      // Give every enabled extension a chance to alter the theme registry.
      $hook = $info['theme'] . '_extension_' . $extension . '_theme_registry_alter';

      if (function_exists($hook)) {
        $hook($registry);
      }
    }
  }

  // Override template_process_html() in order to add support for conditional
  // comments for JavaScript files.
  if (($index = array_search('template_process_html', $registry['html']['process functions'], TRUE)) !== FALSE) {
    array_splice($registry['html']['process functions'], $index, 1, 'omega_template_process_html_override');
  }

  // Fix for integration with the theme developer module.
  if (module_exists('devel_themer')) {
    foreach ($registry as &$item) {
      if (isset($item['function']) && $item['function'] != 'devel_themer_catch_function') {
        // If the hook is a function, store it so it can be run after it has been intercepted.
        // This does not apply to template calls.
        $item['devel_function_intercept'] = $item['function'];
      }

      // Add our catch function to intercept functions as well as templates.
      $item['function'] = 'devel_themer_catch_function';

      // Remove the process and preprocess functions so they are
      // only called by devel_themer_theme_twin().
      $item['devel_function_preprocess_intercept'] = !empty($item['preprocess functions']) ? array_merge($item['devel_function_preprocess_intercept'], array_diff($item['preprocess functions'], $item['devel_function_preprocess_intercept'])) : $item['devel_function_preprocess_intercept'];
      $item['devel_function_process_intercept'] = !empty($item['process functions']) ? array_merge($item['devel_function_process_intercept'], array_diff($item['process functions'], $item['devel_function_process_intercept'])) : $item['devel_function_process_intercept'];
      $item['preprocess functions'] = array();
      $item['process functions'] = array();
    }
  }
}

/**
 * Overrides template_process_html() in order to provide support for the
 * 'browsers' attribute for JavaScript files.
 */
function omega_template_process_html_override(&$variables) {
  // Render page_top and page_bottom into top level variables.
  $variables['page_top'] = drupal_render($variables['page']['page_top']);
  $variables['page_bottom'] = drupal_render($variables['page']['page_bottom']);
  // Place the rendered HTML for the page body into a top level variable.
  $variables['page'] = $variables['page']['#children'];
  $variables['page_bottom'] .= omega_get_js('footer');

  $variables['head'] = drupal_get_html_head();
  $variables['css'] = drupal_add_css();
  $variables['styles']  = drupal_get_css();
  $variables['scripts'] = omega_get_js();
}

/**
 * Implements hook_block_list_alter().
 *
 * Effectively hides the main content block on the front page if the theme
 * settings are configured that way.
 */
function omega_block_list_alter(&$blocks) {
  if (omega_extension_enabled('layouts') && $layout = omega_layout()) {
    foreach ($blocks as $key => $block) {
      if (!array_key_exists($block->region, $layout['info']['regions'])) {
        unset($blocks[$key]);
      }
    }
  }

  if (!omega_theme_get_setting('omega_toggle_front_page_content', TRUE) && drupal_is_front_page()) {
    foreach ($blocks as $key => $block) {
      if ($block->module == 'system' && $block->delta == 'main') {
        unset($blocks[$key]);
      }
    }

    drupal_set_page_content();
  }
}

/**
 * Implements hook_page_delivery_callback_alter().
 */
function omega_page_delivery_callback_alter(&$callback) {
  if (module_exists('overlay') && overlay_display_empty_page()) {
    $callback = 'omega_override_overlay_deliver_empty_page';
  }
}

/**
 * Delivery callback to display an empty page.
 *
 * This function is used to print out a bare minimum empty page which still has
 * the scripts and styles necessary in order to trigger the overlay to close.
 */
function omega_override_overlay_deliver_empty_page() {
  $empty_page = '<html><head><title></title>' . drupal_get_css() . omega_get_js() . '</head><body class="overlay"></body></html>';
  print $empty_page;
  drupal_exit();
}

/**
 * Implements hook_page_alter().
 *
 * Look for the last block in the region. This is impossible to determine from
 * within a preprocess_block function.
 */
function omega_page_alter(&$page) {
  // Look in each visible region for blocks.
  foreach (system_region_list($GLOBALS['theme'], REGIONS_VISIBLE) as $region => $name) {
    if (!empty($page[$region])) {
      // Find the last block in the region.
      $blocks = array_reverse(element_children($page[$region]));
      while ($blocks && !isset($page[$region][$blocks[0]]['#block'])) {
        array_shift($blocks);
      }

      if ($blocks) {
        $page[$region][$blocks[0]]['#block']->last_in_region = TRUE;
      }
    }
  }

  if (omega_extension_enabled('development') && omega_theme_get_setting('omega_dummy_blocks', TRUE) && user_access('administer site configuration')) {
    $item = menu_get_item();

    // Don't interfere with the 'Demonstrate block regions' page.
    if ($item['path'] != 'admin/structure/block/demo/' . $GLOBALS['theme']) {
      foreach (system_region_list($GLOBALS['theme'], REGIONS_VISIBLE) as $region => $name) {
        if (empty($page[$region])) {
          $page[$region]['#theme_wrappers'] = array('region');
          $page[$region]['#region'] = $region;
        }

        $page[$region]['dummy']['#markup'] = '<div class="omega-dummy-block">' . $name . '</div>';
      }
    }
  }
}

/**
 * Implements hook_html_head_alter().
 */
function omega_html_head_alter(&$head) {
  // Simplify the meta tag for character encoding.
  $head['system_meta_content_type']['#attributes'] = array('charset' => str_replace('text/html; charset=', '', $head['system_meta_content_type']['#attributes']['content']));
}

/**
 * Implements hook_omega_theme_libraries_info().
 */
function omega_omega_theme_libraries_info($theme) {
  $path = drupal_get_path('theme', 'omega');

  $libraries['selectivizr'] = array(
    'name' => t('Selectivizr'),
    'description' => t('Selectivizr is a JavaScript utility that emulates CSS3 pseudo-classes and attribute selectors in Internet Explorer 6-8. Simply include the script in your pages and selectivizr will do the rest.'),
    'vendor' => 'Keith Clark',
    'vendor url' => 'http://selectivizr.com/',
    'package' => t('Polyfills'),
    'files' => array(
      'js' => array(
        $path . '/libraries/selectivizr/selectivizr.min.js' => array(
          'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
          'group' => JS_THEME,
          'weight' => 100,
          'every_page' => TRUE,
        ),
      ),
    ),
    'variants' => array(
      'source' => array(
        'name' => t('Source'),
        'description' => t('During development it might be useful to include the source files instead of the minified version.'),
        'files' => array(
          'js' => array(
            $path . '/libraries/selectivizr/selectivizr.js' => array(
              'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
              'group' => JS_THEME,
              'weight' => 100,
              'every_page' => TRUE,
            ),
          ),
        ),
      ),
    ),
  );

  $libraries['css3mediaqueries'] = array(
    'name' => t('CSS3 Media Queries'),
    'description' => t('CSS3 Media Queries is a JavaScript library to make IE 5+, Firefox 1+ and Safari 2 transparently parse, test and apply CSS3 Media Queries. Firefox 3.5+, Opera 7+, Safari 3+ and Chrome already offer native support. Note: This library requires <a href="!url">CSS aggregation</a> to be enabled for it to work properly.', array('!url' => url('admin/config/development/performance'))),
    'vendor' => 'Wouter van der Graaf',
    'vendor url' => 'http://woutervandergraaf.nl/',
    'package' => t('Polyfills'),
    'callbacks' => array('omega_extension_library_requirements_css_aggregation'),
    'files' => array(
      'js' => array(
        $path . '/libraries/css3mediaqueries/css3mediaqueries.min.js' => array(
          'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
          'group' => JS_THEME,
          'weight' => 100,
          'every_page' => TRUE,
        ),
      ),
    ),
    'variants' => array(
      'source' => array(
        'name' => t('Source'),
        'description' => t('During development it might be useful to include the source files instead of the minified version.'),
        'files' => array(
          'js' => array(
            $path . '/libraries/css3mediaqueries/css3mediaqueries.js' => array(
              'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
              'group' => JS_THEME,
              'weight' => 100,
              'every_page' => TRUE,
            ),
          ),
        ),
      ),
    ),
  );

  $libraries['respond'] = array(
    'name' => t('Respond'),
    'description' => t('Respond is a fast & lightweight polyfill for min/max-width CSS3 Media Queries (for IE 6-8, and more). Note: This library requires <a href="!url">CSS aggregation</a> to be enabled for it to work properly.', array('!url' => url('admin/config/development/performance'))),
    'vendor' => 'Scott Jehl',
    'vendor url' => 'http://scottjehl.com/',
    'package' => t('Polyfills'),
    'callbacks' => array('omega_extension_library_requirements_css_aggregation'),
    'files' => array(
      'js' => array(
        $path . '/libraries/respond/respond.min.js' => array(
          'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
          'group' => JS_THEME,
          'weight' => 100,
          'force header' => TRUE,
          'every_page' => TRUE,
        ),
      ),
    ),
    'variants' => array(
      'source' => array(
        'name' => t('Source'),
        'description' => t('During development it might be useful to include the source files instead of the minified version.'),
        'files' => array(
          'js' => array(
            $path . '/libraries/respond/respond.js' => array(
              'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
              'group' => JS_THEME,
              'weight' => 100,
              'every_page' => TRUE,
            ),
          ),
        ),
      ),
    ),
  );

  $libraries['css3pie'] = array(
    'name' => t('CSS3 PIE'),
    'description' => t('PIE makes Internet Explorer 6-9 capable of rendering several of the most useful CSS3 decoration features.'),
    'vendor' => 'Keith Clark',
    'vendor url' => 'http://css3pie.com/',
    'options form' => 'omega_library_pie_options_form',
    'package' => t('Polyfills'),
    'files' => array(),
    'variants' => array(
      'js' => array(
        'name' => t('JavaScript'),
        'description' => t('While the .htc behavior is still the recommended approach for most users, the JS version has some advantages that may be a better fit for some users.'),
        'files' => array(
          'js' => array(
            $path . '/libraries/css3pie/PIE.js' => array(
              'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
              'group' => JS_THEME,
              'weight' => 100,
              'every_page' => TRUE,
            ),
          ),
        ),
      ),
    ),
  );

  // Add the generated .css file to the corresponding variant.
  $file = file_create_url('public://omega/' . $theme . '/pie-selectors.css');
  $file = substr($file, strlen($GLOBALS['base_url']) + 1);

  if (is_file($file)) {
    $libraries['css3pie']['files']['css'][$file] = array(
      'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
      'group' => CSS_THEME,
      'weight' => 100,
      'every_page' => TRUE,
    );
  }

  // Add the generated .js file to the corresponding variant.
  $file = file_create_url('public://omega/' . $theme . '/pie-selectors.js');
  $file = substr($file, strlen($GLOBALS['base_url']) + 1);

  if (is_file($file)) {
    $libraries['css3pie']['variants']['js']['files']['js'][$file] = array(
      'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
      'group' => JS_THEME,
      'weight' => 100,
      'every_page' => TRUE,
    );
  }

  $libraries['html5shiv'] = array(
    'name' => t('HTML5 Shiv'),
    'description' => t('This script is the defacto way to enable use of HTML5 sectioning elements in legacy Internet Explorer, as well as default HTML5 styling in Internet Explorer 6 - 9, Safari 4.x (and iPhone 3.x), and Firefox 3.x.'),
    'vendor' => 'Alexander Farkas',
    'package' => t('Polyfills'),
    'files' => array(
      'js' => array(
        $path . '/libraries/html5shiv/html5shiv.min.js' => array(
          'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
          'group' => JS_THEME,
          'weight' => 100,
          'force header' => TRUE,
          'every_page' => TRUE,
        ),
      ),
    ),
    'variants' => array(
      'source' => array(
        'name' => t('Source'),
        'description' => t('During development it might be useful to include the source files instead of the minified version.'),
        'files' => array(
          'js' => array(
            $path . '/libraries/html5shiv/html5shiv.js' => array(
              'browsers' => array('IE' => '(gte IE 6)&(lte IE 8)', '!IE' => FALSE),
              'group' => JS_THEME,
              'weight' => 100,
              'force header' => TRUE,
              'every_page' => TRUE,
            ),
          ),
        ),
      ),
    ),
  );

  $libraries['messages'] = array(
    'name' => t('Discardable messages'),
    'description' => t("Adds a 'close' button to each message."),
    'package' => t('Goodies'),
    'files' => array(
      'js' => array(
        $path . '/js/omega.messages.js' => array(
          'group' => JS_THEME,
          'weight' => -100,
          'every_page' => TRUE,
        ),
      ),
      'css' => array(
        $path . '/css/omega.messages.css' => array(
          'group' => CSS_THEME,
          'weight' => -100,
          'every_page' => TRUE,
        ),
      ),
    ),
  );

  return $libraries;
}
