<?php 

/**
 * @file
 * Helper functions for the Alpha base theme.
 */

/**
 * Invokes a preprocess or process hook in all base themes aswell 
 * as the subtheme (in that order) by including the corresponding 
 * .inc file and calling the associated function.
 * 
 * @param $type
 * 	 The type of the hook. Can be preprocess or process.
 * 
 * @param $hook
 * 	 The name of the hook.
 * 
 * @param &$vars
 *   An array of variables belonging to the (pre)process hook.
 *   Handed by reference.
 */
function alpha_invoke($type, $hook, &$vars) {
  // Fetch the hook registry from cache or static (if already populated).
  $registry = &drupal_static(__FUNCTION__);
  if (!isset($registry)) {
    $registry = variable_get('theme_' . $GLOBALS['theme_key'] . '_alpha_registry', array());
  }

  // If one of the themes in the theme trail implements this hook
  // include the corresponding .inc file and call the associated function.
  if (!empty($registry[$type][$hook])) {    
    foreach ($registry[$type][$hook] as $item) {
      if (is_file($item['path'])) {
        include_once $item['path'];      
       
        if (function_exists($item['function'])) {
          $item['function']($vars);
        }
      }
    }
  }
}

/**
 * Builds the full theme trail (deepest base theme first, subtheme last)
 * for a theme.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 to retrieve the theme trail for.
 * 
 * @param $hook
 * 	 The name of the hook.
 * 
 * @return 
 * 	 An array of all themes in the trail, keyed by theme key.
 */
function alpha_theme_trail($key) {
  $static = &drupal_static(__FUNCTION__);
  
  if (!isset($static)) {
    $themes = list_themes();
    
    if (isset($themes[$key]->info['base theme'])) {
      foreach (system_find_base_themes($themes, $key) as $base => $name) {
        if ($name && isset($themes[$base])) {
          $static[$key][$base] = $themes[$base];
        }
      }
    }

    // Add our current subtheme ($key) to that array.
    $static[$key][$key] = $themes[$key];
  }

  return $static[$key];
}

/**
 * Builds the hook registry tree for a theme and its ancestors
 * and saves it as a variable.
 * 
 * @param $name
 * 	 The key (machin-readable name) of the theme that you want
 *	 to build the registry for.
 * 
 * @param $registry
 * 	 The theme registry as an array.
 * 
 * @see 
 * 	 hook_theme_registry_alter().
 */
function alpha_build_registry($name, $registry) {
  $cache = array();
  
  // Loop through all items in the registry items and check for
  // existing (pre)process hooks in our theme trail.
  foreach ($registry as $item => $info) {
    foreach (array('preprocess', 'process') as $type) {
      $key = $type . ' functions';
     
      // Only process this array if it has hooks of this $type.
      if (!empty($info[$key])) {
        foreach (alpha_theme_trail($name) as $theme => $data) {
          $file = $type . '-' . str_replace('_', '-', $item) . '.inc';    
          $path = DRUPAL_ROOT . '/' . drupal_get_path('theme', $theme) . '/' . $type . '/' . $file;

          // If the corresponding file exists add it to the registry.
          if (is_file($path)) {
            $cache[$type][$item][] = array(
              'type' => $type,
              'hook' => $item,
              'theme' => $theme,
              'path' => $path,
              'function' => $theme . '_alpha_' . $type . '_' . $item,
            );
          }
        }
      }
    }
  }
  
  // Save the registry in the database.
  variable_set('theme_' . $name . '_alpha_registry', $cache);
}

/**
 * A wrapper function for theme_get_settings().
 * 
 * @param $name
 * 	 The name of the setting that you want to retrieve.
 * 
 * @param $theme (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to the global variable $theme_key.
 *   
 * @param $default (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @see 
 * 	 theme_get_setting().
 */
function alpha_theme_get_setting($name, $theme = NULL, $default = NULL) {
  $setting = theme_get_setting($name, $theme);
  
  return isset($setting) ? $setting : $default; 
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $start
 * 	 The number to start with.
 * 
 * @param $end
 * 	 The number to end with.
 *   
 * @param $step
 * 	 The size of a step.
 *   
 * @return 
 * 	 An array of scale options.
 */
function alpha_scale_options($start, $end, $step) {
  $options = array();  
  foreach (range($start, $end, $step) as $number) {
    // Format the value to display with one decimal.
    $options[(string) $number] = number_format($number, 1);
  }
  
  return $options;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme.
 * 
 * @param $responsive
 * 	 A boolean indicating wether the array should cover responsive
 * 	 or non-responsive stylesheets.
 *   
 * @return 
 * 	 An array of optional or responsive stylesheet options.
 */
function alpha_css_options($key, $responsive = FALSE) {
  $items = alpha_css($key);
  
  $output = array();
  if (!empty($items)) { 
    foreach ($items as $key => $info) {
      $output[$key] = '<strong>' . check_plain($info['name']) . '</strong>' . (!$responsive ? ' (' . (isset($info['options']['media']) ? $info['options']['media'] : 'all') . ')' : '') . ' - ' . $info['file'] . '<div class="description">' . $info['description'] . '</div>';
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme.
 * 
 * @see
 *   hook_css_alter().
 *   
 * @return 
 * 	 An array of stylesheets that can be disabled / excluded with
 *   hook_css_alter().
 */
function alpha_exclude_options($key) {
  $items = alpha_excludes($key);
  
  $output = array(); 
  foreach ($items as $key => $info) {
    if ($info['type'] == 'exclude') {
      $output[$key] = '<strong>' . basename($key) . '</strong> - ' . t('Defined by') . ' ' . $info['name'] . '<div class="description">' . $info['description'] . '</div>';
    }
    else {
      $output[$key] = '<strong>' . basename($key) . '</strong> (' . $info['media'] . ') - ' . t('Belongs to') . ' ' . $info['name'] . '<div class="description">' . $info['description'] . '</div>';
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme.
 *   
 * @return 
 * 	 An array of available grids.
 */
function alpha_grid_options($key) {
  $items = alpha_grids($key);
  
  $output = array();
  if (!empty($items)) {
    foreach ($items as $key => $info) {
      $output[$key] = check_plain($info['name']);
    }
  }
    
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme.
 *   
 * @return 
 * 	 An array of available layouts.
 */
function alpha_grid_layouts_options($key, $grid) {
  $grid = alpha_grids($key, $grid);
  
  $output = array();
  if (!empty($grid['layouts'])) {
    foreach ($grid['layouts'] as $key => $info) {
      $output[$key] = check_plain($info['name']);
    }
  }
    
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme.
 *   
 * @return 
 * 	 An array of available libraries.
 */
function alpha_library_options($key) {
  $items = alpha_libraries($key);
  
  $output = array();
  if (!empty($items)) {
    foreach ($items as $key => $info) {
      $output[$key] = check_plain($info['name']) . '<div class="description">' . $info['description'] . '</div>';
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme.
 *   
 * @return 
 * 	 An array of available zones.
 */
function alpha_zone_options($key) {
  $items = alpha_zones($key);
  
  $output = array();
  if (!empty($items)) {
    foreach ($items as $key => $info) {
      $output[$key] = check_plain($info['name']);
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available containers for.
 *
 * @param $grid
 * 	 The grid that you want to fetch the available containers for.
 *   
 * @return 
 * 	 An array of available containers.
 */
function alpha_container_options($key, $grid) {
  $grids = alpha_grids($key, $grid);
  
  $output = array();
  if (!empty($grids['columns'])) {
    foreach ($grids['columns'] as $count => $path) {
      $output[$count] = t('@count columns', array('@count' => $count));
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available containers for.
 *   
 * @return 
 * 	 An array of available regions.
 */
function alpha_region_options($key) {
  $regions = alpha_regions($key);
  
  $output = array();
  if (!empty($regions)) {
    foreach ($regions as $region => $item) {
      $output[$region] = $item['name'];
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $max
 * 	 The maximum number of columns that you want to cover.
 *   
 * @return 
 * 	 An array of available columns counts.
 */
function alpha_column_options($max = NULL) {
  $output = array();
  
  if (isset($max)) {
    foreach (range(0, $max) as $width) {
      $output[$width] = t('@width columns', array('@width' => $width));
    }
  }
  
  return $output;
}

/**
 * Retrieves all registered CSS files for a theme.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available grids for.
 * 
 * @param $responsive (optional)
 * 	 Wether you want to fetch the array of responsive or non-responsive 
 *   CSS files. Defaults to FALSE.
 * 
 * @return
 * 	 An array that holds the information about the available CSS files.
 */
function alpha_css($key) {
  $css = &drupal_static(__FUNCTION__);
  
  if (!isset($css[$key])) {
    $css[$key] = variable_get('theme_' . $key . '_alpha_css');
  }

  if (!isset($css[$key])) {
    $css[$key] = alpha_register_css($key);
  }
  
  return $css[$key];
}

/**
 * Retrieves all available CSS files for a theme and caches them in a 
 * variable.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available CSS files for.
 */
function alpha_register_css($key) {
  $cache = array();  

  foreach (alpha_info($key, 'css') as $theme => $data) {
    foreach ($data as $file => $info) {
      $cache[$file] = array(
        'name' => $info['name'],
        'description' => isset($info['description']) ? $info['description'] : '',
        'file' => $file,
        'path' => drupal_get_path('theme', $theme) . '/' . (isset($info['path']) ? $info['path'] : 'css') . '/' . $file,
        'options' => isset($info['options']) ? $info['options'] : array(),
      );
    }
  }
  
  alpha_alter('alpha_css', $cache, $key);
  variable_set('theme_' . $key . '_alpha_css', $cache);
    
  return $cache;
}

/**
 * Retrieves all available libraries for a theme.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available libraries for.
 * 
 * @param $grid (optional)
 * 	 The name of the library that you want to fetch. Defaults to NULL.
 * 
 * @return
 * 	 An array of libraries or the library defined by $library.
 */
function alpha_libraries($key, $library = NULL) {
  $libraries = &drupal_static(__FUNCTION__);
  
  if (!isset($libraries[$key])) {
    $libraries[$key] = variable_get('theme_' . $key . '_alpha_libraries');
  }

  if (!isset($libraries[$key])) {
    $libraries[$key] = alpha_register_libraries($key, $library);
  }
  
  if (isset($library) && isset($libraries[$key][$library])) {
    return $libraries[$key][$library];
  } 
  else if (!isset($library)) {
    return $libraries[$key];
  }
}

/**
 * Retrieves all available libraries for a theme and caches them in a 
 * variable.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available libraries for.
 */
function alpha_register_libraries($key) {
  $cache = array();  

  foreach (alpha_info($key, 'libraries') as $theme => $data) {
    foreach ($data as $name => $info) {
      $cache[$name] = array(
        'name' => $info['name'],
        'description' => isset($info['description']) ? $info['description'] : '',
      );
      
      foreach (array('css', 'js') as $type) {
        if (!empty($info[$type])) {
          foreach ($info[$type] as $item) {            
            $cache[$name][$type][] = array(
              'path' => drupal_get_path('theme', $theme) . '/' . (isset($item['path']) ? $item['path'] : $type) . '/' . $item['file'],
          	  'options' => isset($item['options']) ? $item['options'] : array(),
            );
          }
        }
      }
    }
  }
  
  alpha_alter('alpha_libraries', $cache, $key);
  variable_set('theme_' . $key . '_alpha_libraries', $cache);
    
  return $cache;
}

/**
 * Adds a column layout for a grid to your site.
 * 
 * @param $name
 * 	 A valid grid name.
 *
 * @param $columns
 * 	 The number of columns.
 */
function alpha_include_grid($name, $columns) {
  $included = &drupal_static(__FUNCTION__);
  
  if (empty($included[$name]['columns'][$columns])) {
    // Set this name/columns combination to TRUE in the static so we don't try to include it over and over.
    $included[$name]['columns'][$columns] = TRUE;
    $grid = alpha_grids($GLOBALS['theme_key'], $name);

    // Only proceed if the grid and the desired column count actually exist.
    if (!empty($grid) && isset($grid['columns'][$columns])) {
      $settings = alpha_settings($GLOBALS['theme_key']);
      $path = $grid['path'] . '/' . $grid['primary'] . '/' . str_replace('_', '-', $name) . '-' . str_replace('_', '-', $grid['primary']);
            
      if (!$settings['responsive']) {        
        drupal_add_css($path . '-' . $columns . '.css', array('group' => 500));
        
        if (empty($included[$name]['primary'])) {
          $included[$name]['primary'] = TRUE;
          
          foreach ($grid['layouts'][$grid['primary']]['attached'] as $attached) {
            drupal_add_css($attached, array('group' => 500));
          }
        }
      }
      else {
        // Add the fallback (primary) grid layout for IE < 9
        $options = array('browsers' => array('IE' => '(lt IE 9)&(!IEMobile)', '!IE' => FALSE), 'group' => 500, 'weight' => -250);
        
        drupal_add_css('ie::' . $path . '-' . $columns . '.css', array('data' => $path . '-' . $columns . '.css', 'basename' => 'ie::' . $path . '-' . $columns . '.css') + $options);

        if (empty($included[$name]['primary'])) {
          $included[$name]['primary'] = TRUE;

          foreach ($grid['layouts'][$grid['primary']]['attached'] as $attached) {
            drupal_add_css('ie::' . $attached, array('data' => $attached, 'basename' => 'ie::' . $attached) + $options);
          }
        }
              
        // Loop through all possible grid layouts and add those that are enabled
        foreach ($grid['layouts'] as $layout => $info) {
          if ($info['enabled']) {
            $path = $grid['path'] . '/' . $layout . '/' . str_replace('_', '-', $name) . '-' . str_replace('_', '-', $layout);
            $options = array('media' => $info['media'], 'weight' => $info['weight'], 'group' => 500);
            
            drupal_add_css($path . '-' . $columns . '.css', $options);

            if (empty($included[$name]['layouts'][$layout])) {
              $included[$name]['layouts'][$layout] = TRUE;
              
              foreach ($info['attached'] as $attached) {
                drupal_add_css($layout . '::' . $attached, array('data' => $attached, 'basename' => $layout . '::' . $attached) + $options);
              }
            }
          }
        }
      }
    }
  }
}

/**
 * A helper function for retrieving zone settings.
 * 
 * @param $name
 * 	 The name of the setting that you want to retrieve.
 * 
 * @param $zone
 * 	 The zone that you want to fetch the setting for.
 * 
 * @param $theme (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to the global variable $theme_key.
 *   
 * @param $default (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @see 
 * 	 alpha_theme_get_setting().
 * 
 * @see
 * 	 theme_get_setting().
 */
function alpha_zone_get_setting($name, $zone, $theme = NULL, $default = NULL) {
  return alpha_theme_get_setting('alpha_zone_' . $zone . '_' . $name, $theme, $default);
}

/**
 * A helper function for retrieving region settings.
 * 
 * @param $name
 * 	 The name of the setting that you want to retrieve.
 * 
 * @param $region
 * 	 The region that you want to fetch the setting for.
 * 
 * @param $theme (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to the global variable $theme_key.
 *   
 * @param $default (optional)
 * 	 The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @see 
 * 	 alpha_theme_get_setting().
 * 
 * @see
 * 	 theme_get_setting().
 */
function alpha_region_get_setting($name, $region, $theme = NULL, $default = NULL) {
  return alpha_theme_get_setting('alpha_region_' . $region . '_' . $name, $theme, $default);
}

/**
 * Retrieves all available grids for a theme.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available grids for.
 * 
 * @param $grid (optional)
 * 	 The name of the grid that you want to fetch. Defaults to NULL.
 * 
 * @return
 * 	 An array of grids or the grid defined by $grid.
 */
function alpha_grids($key, $grid = NULL) {
  $grids = &drupal_static(__FUNCTION__);
  
  if (!isset($grids[$key])) {
    $grids[$key] = variable_get('theme_' . $key . '_alpha_grids'); 

    if (!isset($grids[$key])) {
      $grids[$key] = alpha_register_grids($key);
    }

    foreach ($grids[$key] as $name => $info) {
      $grids[$key][$name]['primary'] = alpha_theme_get_setting('alpha_primary_' . $name, $key);
      
      foreach ($grids[$key][$name]['layouts'] as $layout => $data) {
        $grids[$key][$name]['layouts'][$layout] = $data + array(
          'enabled' => alpha_theme_get_setting('alpha_layouts_' . $name . '_' . $layout . '_responsive', $key, FALSE),
          'media' => alpha_theme_get_setting('alpha_layouts_' . $name . '_' . $layout . '_media', $key, 'all'),
          'weight' => alpha_theme_get_setting('alpha_layouts_' . $name . '_' . $layout . '_weight', $key, 0),
        );
      }
      
      uasort($grids[$key][$name]['layouts'], 'alpha_sort_layouts');
    }
  }

  if (isset($grid) && isset($grids[$key][$grid])) {
    return $grids[$key][$grid];
  } 
  else if (!isset($grid)) {
    return $grids[$key];
  }
}

/**
 * Retrieves all available grids for a theme and caches them in a 
 * variable.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available grids for.
 */
function alpha_register_grids($key) {
  $cache = array();

  $trail = alpha_theme_trail($key);
  
  foreach (alpha_info($key, 'grids') as $theme => $data) {
    foreach ($data as $name => $info) {
      $cache[$name] = array(
        'name' => $info['name'],
        'path' => drupal_get_path('theme', $theme) . '/css/grid/' . $name,
        'layouts' => array(),
        'columns' => $info['columns'],
      );
      
      foreach ($info['layouts'] as $layout => $title) {
        $cache[$name]['layouts'][$layout] = array(
          'name' => $title,
          'attached' => array(),
        );
        
        foreach ($trail as $item) {
          $path = drupal_get_path('theme', $item->name) . '/css/' . implode('-', array(str_replace('_', '-', $item->name), str_replace('_', '-', $name)));
          $files = array($path . '.css', $path . '-' . str_replace('_', '-', $layout) . '.css');

          foreach ($files as $file) {
            if (is_file($file)) {
              $cache[$name]['layouts'][$layout]['attached'][] = $file;
            }
          }
        }
      }
    }
  }
  
  alpha_alter('alpha_grids', $cache, $key);  
  variable_set('theme_' . $key . '_alpha_grids', $cache);
    
  return $cache;
}

/**
 * Retrieves all available zones for a theme.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available grids for.
 *
 * @return
 *   An array of zones and their settings.
 */
function alpha_zones($key) {
  $output = &drupal_static(__FUNCTION__);
  
  if (!isset($output[$key])) {
    $themes = list_themes();
    $sections = alpha_sections();
    
    $output[$key] = array();
    if (isset($themes[$key]->info['zones'])) { 
      foreach ($themes[$key]->info['zones'] as $zone => $name) {
        $section = alpha_zone_get_setting('section', $zone, $key);
        
        $output[$key][$zone] = array(
          'zone' => $zone,
          'name' => $name,
          'enabled' => isset($sections[$section]),
          'force' => alpha_zone_get_setting('force', $zone, $key, FALSE),
          'columns' => alpha_zone_get_setting('columns', $zone, $key, 0),
          'section' => $section,
          'weight' => alpha_zone_get_setting('weight', $zone, $key, 0),
          'wrapper' => alpha_zone_get_setting('wrapper', $zone, $key, FALSE),
          'wrapper_css' => alpha_zone_get_setting('wrapper_css', $zone, $key),
          'primary' => alpha_zone_get_setting('primary', $zone, $key),
          'css' => alpha_zone_get_setting('css', $zone, $key),
        );
      }
    }
    
    uasort($output[$key], 'drupal_sort_weight');
  }
  
  return $output[$key];
}

/**
 * Retrieves all available regions for a theme.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the available grids for.
 *
 * @return
 *   An array of regions and their settings.
 */
function alpha_regions($key) {
  $output = &drupal_static(__FUNCTION__);

  if (!isset($output[$key])) {
    $regions = system_region_list($key);
    $zones = alpha_zones($key);
    $exclude = alpha_regions_exclude();
    
    $output[$key] = array();
    foreach (array_diff_key($regions, $exclude) as $region => $name) {
      $zone = alpha_region_get_setting('zone', $region, $key);
      $prefix = alpha_region_get_setting('prefix', $region, $key, 0);
      $columns = alpha_region_get_setting('columns', $region, $key, 0);
      $suffix = alpha_region_get_setting('suffix', $region, $key, 0);
      
      $output[$key][$region] = array(
        'region' => $region,
        'name' => $name,
        'zone' => $zone,
        'enabled' => isset($zones[$zone]),
        'force' => alpha_region_get_setting('force', $region, $key, FALSE),
        'prefix' => $prefix,
        'columns' => $columns,
        'suffix' => $suffix,
        'width' => $prefix + $columns + $suffix,
        'push' => 0,
        'pull' => 0,
        'css' => alpha_region_get_setting('css', $region, $key),
        'weight' => alpha_region_get_setting('weight', $region, $key, 0),
        'primary' => isset($zone) && isset($zones[$zone]) ? $zones[$zone]['primary'] == $region : FALSE,
      );
    }
    
    uasort($output[$key], 'drupal_sort_weight');
  }
  
  return $output[$key];
}

/**
 * A helper function that holds the available sections.
 * 
 * @return
 *   The array of available sections.
 */
function alpha_sections() {
  return array(
    'header' => t('Header'),
    'content' => t('Content'),
    'footer' => t('Footer'),
  );
}

/**
 * A helper function for retrieving the content of theme .info files
 * in the theme trail of $key.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the theme trail .info content for.
 * 
 * @param $item
 *   The name of the variable that you want to fetch.
 *   
 * @return
 *   The $item content of all themes .info files in the theme trail.
 */
function alpha_info($key, $item) {
  $output = &drupal_static(__FUNCTION__);
  
  if (!isset($output[$key][$item])) {
    $output[$key][$item] = array();
    foreach (alpha_theme_trail($key) as $theme => $info) {
      if (!empty($info->info[$item])) {
        $output[$key][$item][$theme] = $info->info[$item];
      }
    }    
  }
  
  return $output[$key][$item];
}

/**
 * This function is a non-cached short version of the full settings array
 * as in alpha_settings(). It is required because in hook_block_list_alter()
 * we need the information provided by this function without unintentionally
 * populating the alpha_settings() cache as this would interfere with the
 * Delta modules settings injection procedure, because the Delta module is
 * being invoked AFTER hook_block_list_alter().
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the debug settings for.
 */
function alpha_debug_settings($key) {
  $settings = array(
    'block' => alpha_theme_get_setting('alpha_debug_block_toggle', $key, FALSE),
    'grid' => alpha_theme_get_setting('alpha_debug_grid_toggle', $key, FALSE),
    'roles' => array_keys(array_filter(alpha_theme_get_setting('alpha_debug_grid_roles', $key, array()))),
  );
  
  $settings['access'] = alpha_debug_access($GLOBALS['user'], $settings['roles']);

  return $settings;
}

/**
 * A helper function to gather all non-region and non-zone settings for the
 * theme defined by $key.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the settings for.
 *
 * @return
 *   A nested array of theme settings.
 */
function alpha_settings($key) {
  $settings = &drupal_static(__FUNCTION__);

  if (!isset($settings[$key])) {
    $css = alpha_css($key);
    
    $settings[$key] = array(
      'grid' => alpha_theme_get_setting('alpha_grid', $key, 'default'),
      'primary' => alpha_theme_get_setting('alpha_primary', $key),
      'css' => alpha_theme_get_setting('alpha_css', $key, array()),
      'libraries' => alpha_theme_get_setting('alpha_libraries', $key, array()),
      'exclude' => alpha_theme_get_setting('alpha_exclude', $key, array()),      
      'responsive' => alpha_theme_get_setting('alpha_responsive', $key, FALSE),
      'toggle' => array(),
      'hidden' => array(),
      'viewport' => array(
        'enabled' => alpha_theme_get_setting('alpha_viewport', $key, FALSE),
        'initial' => alpha_theme_get_setting('alpha_viewport_initial_scale', $key, 1),
        'min' => alpha_theme_get_setting('alpha_viewport_min_scale', $key, 1),
        'max' => alpha_theme_get_setting('alpha_viewport_max_scale', $key, 1),
        'user' => alpha_theme_get_setting('alpha_viewport_user_scaleable', $key, TRUE),
      ),
      'debug' => array(
        'block' => alpha_theme_get_setting('alpha_debug_block_toggle', $key, FALSE),
        'grid' => alpha_theme_get_setting('alpha_debug_grid_toggle', $key, FALSE),
        'roles' => array_keys(array_filter(alpha_theme_get_setting('alpha_debug_grid_roles', $key, array()))),
      ),
    );

    foreach (alpha_toggle() as $item => $title) {
      $settings[$key]['toggle'][$item] = alpha_theme_get_setting('alpha_toggle_' . $item, $key, TRUE);
    }
    
    foreach (alpha_visibility() as $item => $title) {
      $settings[$key]['hidden'][$item] = alpha_theme_get_setting('alpha_hidden_' . $item, $key, FALSE);
    }

    $settings[$key]['debug']['access'] = alpha_debug_access($GLOBALS['user'], $settings[$key]['debug']['roles']);
    
    alpha_alter('alpha_settings', $settings[$key], $key);
  }
  
  return $settings[$key];
}

/**
 * This function "fixes" drupal_alter so it also works in the theme-settings and anywhere else 
 * where you want to be 100% certain that drupal_alter uses the proper global $theme.
 * 
 * The problem with drupal_alter is, that it always relies on the global $theme while
 * the theme-settings page relies (and "overrides") the global $theme_key variable while
 * building its form.
 * 
 * @param $type
 * 
 * @param $data
 * 
 * @param $context1
 * 
 * @param $context2
 * 
 * @see 
 *   See drupal_alter() for more information about how this works.
 */
function alpha_alter($type, &$data, &$context1 = NULL, &$context2 = NULL) {
  global $theme, $base_theme_info;

  if ($theme != $context1) {
    $themes = list_themes();
    
    if (!empty($themes[$context1])) {
      $theme_original = $theme;
      $base_theme_info_original = $base_theme_info;

      $base_theme_info = alpha_theme_trail($context1);
      
      if (!empty($base_theme_info)) {
        foreach ($base_theme_info as $theme) {
          if (is_file(drupal_get_path('theme', $theme->name) . '/template.php')) {
            include_once drupal_get_path('theme', $theme->name) . '/template.php';
          }
        }
      }
     
      array_pop($base_theme_info);
      
      $theme = $context1;
            
      drupal_alter($type, $data, $context1, $context2);      
      
      $functions = &drupal_static('drupal_alter');
      unset($functions[$type]);
      
      $theme = $theme_original;
      $base_theme_info = $base_theme_info_original;
    }
  }
  else {
    drupal_alter($type, $data, $context1, $context2);
  }
}

/**
 * A helper function to check wether the user defined by $user
 * matches one of the roles defined by $roles.
 * 
 * @param $user
 *   A Drupal user as returned by user_load().
 * 
 * @param $roles
 *   An array of roles that you want to check against $user.
 * 
 * @return
 *   A boolean, indicating wether or not $user matches one of
 *   the $roles.
 */
function alpha_debug_access($user, $roles) {
  foreach ($roles as $role) {
    if (isset($user->roles[$role])) {
      return TRUE;
    } 
  }
  
  return FALSE;
}

/**
 * A helper function that returns an array of un-wanted Drupal core regions.
 * 
 * @return
 *   An array of un-wanted regions.
 */
function alpha_regions_exclude() {
  return array('page_top', 'page_bottom');
}

/**
 * Adds a debug grid to the site.
 * 
 * @param $settings
 *   The settings array as returned by alpha_settings().
 * 
 * @param $columns
 *   The column layout (number) that you want to add a debugging grid
 *   for.
 */
function alpha_debug_grid($settings, $columns) {
  $debug = &drupal_static(__FUNCTION__);
  $grid = alpha_grids($GLOBALS['theme_key'], $settings['grid']);
  
  if (!isset($debug[$columns])) {
    $debug[$columns] = TRUE;
               
    foreach ($grid['layouts'] as $layout => $info) {
      if ((!$settings['responsive'] && $layout == $grid['primary']) || ($settings['responsive'] && $info['enabled'])) {
        $path = $grid['path'] . '/' . $layout . '/' . str_replace('_', '-', $settings['grid']) . '-' . str_replace('_', '-', $layout) . '-' . $columns . '.png';

        if (is_file($path)) {      
          $media = $settings['responsive'] ? $info['media'] : 'all';          
          $overlay = 'body.alpha-grid-debug .container-' . $columns . ' { background-image: url(' . file_create_url($path) . '); }' . "\n";
          
          drupal_add_css($overlay, array('type' => 'inline', 'media' => $media, 'group' => 500, 'weight' => -500));
        }
      }
    }
  }
}

/**
 * Builds and returns a debugging block.
 * 
 * @param $key
 * 	 The key (machin-readable name) of the theme that you want
 *	 fetch the exclusion candidates for.
 * 
 * @return
 *   An array of files that can be excluded with hook_css_alter().
 * 
 * @see
 *   alpha_css_alter().
 * 
 * @see 
 *   hook_css_alter().
 */
function alpha_excludes($key) {
  $themes = list_themes();
  
  $styles = array();
  foreach (system_rebuild_module_data() as $module => $data) {
    if ($data->status && !empty($data->info['stylesheets'])) {
      foreach ($data->info['stylesheets'] as $media => $content) {        
        foreach ($content as $file) {          
          $styles[$file] = array(
            'type' => 'module',
            'source' => $module,
            'name' => $data->info['name'],
            'file' => $file,
            'media' => $media,
          	'description' => NULL,
          );
        }
      }
    }
  }
  
  foreach (alpha_info($key, 'stylesheets') as $theme => $data) {
    foreach ($data as $media => $content) {
      foreach ($content as $file) {
        $styles[$file] = array(
          'type' => 'theme',
          'source' => $theme,
          'name' => $themes[$theme]->info['name'],
          'file' => $file,
          'media' => $media,
          'description' => NULL,
        );
      }
    }
  }
  
  foreach (alpha_info($key, 'exclude') as $theme => $data) {
    foreach ($data as $file => $description) {
      $styles[$file] = array(
        'type' => 'exclude',
        'source' => $theme,
        'name' => $themes[$theme]->info['name'],
        'file' => $file,
        'media' => NULL,
        'description' => $description,
      );
    }
  }
  
  return $styles;
}

/**
 * @todo
 */
function alpha_region_dimensions($region) {
  $regions = alpha_regions($GLOBALS['theme_key']);
  
  if (isset($regions[$region])) {
    return array(
      'columns' => $regions[$region]['columns'],
      'prefix' => $regions[$region]['prefix'],
      'suffix' => $regions[$region]['suffix'],
      'width' => $regions[$region]['width'],
      'pull' => $regions[$region]['pull'],
      'push' => $regions[$region]['push'],
    );
  }
}

/**
 * @todo
 */
function alpha_sort_layouts($a, $b) {
  if ($a['enabled'] && !$b['enabled']) {
    return -1;
  }
  else if ($b['enabled'] && !$a['enabled']) {
    return 1;
  }
  else if ($b['weight'] > $a['weight']) {
    return -1;
  }
  else if ($a['weight'] > $b['weight']) {
    return 1;
  }
}

/**
 * Helper function that returns an array of Drupal core elements that Alpha
 * can toggle on and off.
 */
function alpha_toggle() {
  return array(
  	'messages' => t('Messages'), 
  	'action_links' => t('Action links'), 
  	'tabs' => t('Tabs'), 
  	'breadcrumb' => t('Breadcrumb'), 
  	'page_title' => t('Page title'), 
  	'feed_icons' => t('Feed icons'),
  );
}

/**
 * Helper function that returns an array of Drupal core elements that Alpha
 * can hide via CSS.
 */
function alpha_visibility() {
  return array(
  	'title' => t('Page title'), 
  	'site_name' => t('Site name'), 
  	'site_slogan' => t('Site slogan'), 
  );
}