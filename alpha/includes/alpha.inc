<?php 

/**
 * @file
 * Helper functions for the Alpha base theme.
 */

/**
 * @todo
 */
global $page;

/**
 * Invokes a preprocess or process hook in all base themes aswell 
 * as the subtheme (in that order) by including the corresponding 
 * .inc file and calling the associated function.
 * 
 * @param $type
 *   The type of the hook. Can be preprocess or process.
 * 
 * @param $hook
 *   The name of the hook.
 * 
 * @param &$vars
 *   An array of variables belonging to the (pre)process hook.
 *   Handed by reference.
 */
function alpha_invoke($type, $hook, &$vars) {
  // Fetch the hook registry from cache or static (if already populated).
  $registry = &drupal_static(__FUNCTION__);
  
  if (!isset($registry)) {
    $registry = alpha_cache_get('alpha_registry', $GLOBALS['theme_key']);
  }
  
  // If one of the themes in the theme trail implements this hook
  // include the corresponding .inc file and call the associated function.
  if (!empty($registry[$type][$hook])) {    
    foreach ($registry[$type][$hook] as $item) {
      if (is_file($item['path'])) {
        if (!function_exists($item['function'])) {
          include $item['path'];
        }
       
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
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 * 
 * @param $hook
 *   The name of the hook.
 * 
 * @return 
 *   An array of all themes in the trail, keyed by theme key.
 */
function alpha_theme_trail($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $static = &drupal_static(__FUNCTION__);
  
  if (!isset($static)) {
    $themes = list_themes();
    
    if (isset($themes[$theme]->info['base theme'])) {
      foreach (system_find_base_themes($themes, $theme) as $base => $name) {
        if ($name && isset($themes[$base])) {
          $static[$theme][$base] = $themes[$base];
        }
      }
    }

    // Add our current subtheme ($key) to that array.
    $static[$theme][$theme] = $themes[$theme];
  }

  return $static[$theme];
}

/**
 * Builds the hook registry tree for a theme and its ancestors
 * and saves it as a variable.
 * 
 * @param $registry
 *   The theme registry as an array.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @see 
 *   hook_theme_registry_alter().
 */
function alpha_build_registry($registry, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $cache = array();
  
  // Loop through all items in the registry items and check for
  // existing (pre)process hooks in our theme trail.
  foreach ($registry as $item => $info) {
    foreach (array('preprocess', 'process') as $type) {
      $key = $type . ' functions';
      
      // Only process this array if it has hooks of this $type.
      if (!empty($info[$key])) {
        foreach (alpha_theme_trail($theme) as $name => $data) {
          $file = $type . '-' . str_replace('_', '-', $item) . '.inc';    
          $path = DRUPAL_ROOT . '/' . drupal_get_path('theme', $name) . '/' . $type . '/' . $file;

          // If the corresponding file exists add it to the registry.
          if (is_file($path)) {
            $cache[$type][$item][] = array(
              'type' => $type,
              'hook' => $item,
              'theme' => $theme,
              'path' => $path,
              'function' => $name . '_alpha_' . $type . '_' . $item,
            );
          }
        }
      }
    }
  }
  
  alpha_alter('alpha_registry', $cache, $theme);
  
  // Save the registry in the database.
  alpha_cache_set('alpha_registry', $cache, $theme);
}

/**
 * @todo
 */
function alpha_cache_get($cid, $theme = NULL) {
  static $executed;
  
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];

  if (empty($executed[$theme])) {
    $executed[$theme] = TRUE;
    $items = array('alpha_registry:' . $theme, 'alpha_grids:' . $theme, 'alpha_css:' . $theme, 'alpha_libraries:' . $theme);
    
    cache_get_multiple($items);
  }
  
  return cache_get($cid . ':' . $theme)->data;
}

/**
 * @todo
 */
function alpha_cache_set($cid, $data, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  
  return cache_set($cid . ':' . $theme, $data);
}

/**
 * A wrapper function for theme_get_settings().
 * 
 * @param $name
 *   The name of the setting that you want to retrieve.
 * 
 * @param $default (optional)
 *   The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @see 
 *   theme_get_setting().
 */
function alpha_theme_get_setting($name, $default = NULL, $theme = NULL) {
  $setting = theme_get_setting($name, $theme);
  
  return isset($setting) ? $setting : $default; 
}

/**
 * A helper function for retrieving zone settings.
 * 
 * @param $name
 *   The name of the setting that you want to retrieve.
 * 
 * @param $zone
 *   The zone that you want to fetch the setting for.
 * 
 * @param $default (optional)
 *   The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @see 
 *   alpha_theme_get_setting().
 * 
 * @see
 *   theme_get_setting().
 */
function alpha_zone_get_setting($name, $zone, $default = NULL, $theme = NULL) {
  return alpha_theme_get_setting('alpha_zone_' . $zone . '_' . $name, $default, $theme);
}

/**
 * A helper function for retrieving region settings.
 * 
 * @param $name
 *    The name of the setting that you want to retrieve.
 * 
 * @param $region
 *    The region that you want to fetch the setting for.
 * 
 * @param $default (optional)
 *   The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.
 *   
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @see 
 *   alpha_theme_get_setting().
 * 
 * @see
 *   theme_get_setting().
 */
function alpha_region_get_setting($name, $region, $default = NULL, $theme = NULL) {
  return alpha_theme_get_setting('alpha_region_' . $region . '_' . $name, $default, $theme);
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $start
 *   The number to start with.
 * 
 * @param $end
 *   The number to end with.
 *   
 * @param $step
 *   The size of a step.
 *   
 * @return 
 *   An array of scale options.
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
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return 
 *   An array of optional or responsive stylesheet options.
 */
function alpha_css_options($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $items = alpha_css($theme);
  
  $output = array();
  if (!empty($items)) { 
    foreach ($items as $key => $info) {
      $output[$key] = '<strong>' . check_plain($info['name']) . '</strong> (' . (isset($info['options']['media']) ? $info['options']['media'] : 'all') . ') - ' . $info['file'] . '<div class="description">' . $info['description'] . '</div>';
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 * 
 * @see
 *   hook_css_alter().
 *   
 * @return 
 *   An array of stylesheets that can be disabled / excluded with
 *   hook_css_alter().
 */
function alpha_exclude_options($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $items = alpha_excludes($theme);
  
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
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return 
 *   An array of available grids.
 */
function alpha_grid_options($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $items = alpha_grids(NULL, $theme);
  
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
 * @param $grid
 *   The name of a grid.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return 
 *   An array of available layouts.
 */
function alpha_grid_layouts_options($grid, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $grid = alpha_grids($grid, $theme);
  
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
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return 
 *   An array of available libraries.
 */
function alpha_library_options($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];

  $output = array();
  if ($items = alpha_libraries(NULL, $theme)) {
    foreach ($items as $key => $info) {
      $output[$key] = check_plain($info['name']) . '<div class="description">' . $info['description'] . '</div>';
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return 
 *   An array of available zones.
 */
function alpha_zone_options($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  
  $output = array();
  if ($items = alpha_zones(NULL, $theme)) {
    foreach ($items as $key => $info) {
      $output[$key] = check_plain($info['name']);
    }
  }
  
  return $output;
}

/**
 * A helper function to return a proper options array for a form.
 * 
 * @param $grid
 *   The grid that you want to fetch the available containers for.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return 
 *   An array of available containers.
 */
function alpha_container_options($grid, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $grids = alpha_grids($grid, $theme);
  
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
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return 
 *   An array of available regions.
 */
function alpha_region_options($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];

  $output = array();
  if ($regions = alpha_regions(NULL, $theme)) {
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
 *   The maximum number of columns that you want to cover.
 *   
 * @return 
 *   An array of available columns counts.
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
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 * 
 * @return
 *   An array that holds the information about the available CSS files.
 */
function alpha_css($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  
  $css = &drupal_static(__FUNCTION__);
  
  if (!isset($css[$theme])) {
    $css[$theme] = alpha_cache_get('alpha_css', $theme);
  }

  if (!isset($css[$theme])) {
    $css[$theme] = alpha_register_css($theme);
  }
  
  return $css[$theme];
}

/**
 * Retrieves all available CSS files for a theme and caches them in a 
 * variable.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 */
function alpha_register_css($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $cache = array();  

  foreach (alpha_info('css', $theme) as $name => $data) {
    foreach ($data as $file => $info) {
      $options = isset($info['options']) ? $info['options'] : array();
      $options = $options + array('group' => CSS_THEME);
      
      $cache[$file] = array(
        'name' => $info['name'],
        'description' => isset($info['description']) ? $info['description'] : '',
        'file' => $file,
        'path' => drupal_get_path('theme', $name) . '/' . (isset($info['path']) ? $info['path'] : 'css') . '/' . $file,
        'options' => $options,
      );
    }
  }
  
  alpha_alter('alpha_css_registry', $cache, $theme);
  alpha_cache_set('alpha_css', $cache, $theme);
    
  return $cache;
}

/**
 * Retrieves all available libraries for a theme.
 * 
 * @param $library (optional)
 *   The name of the library that you want to fetch. Defaults to NULL.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 * 
 * @return
 *   An array of libraries or the library defined by $library.
 */
function alpha_libraries($library = NULL, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $libraries = &drupal_static(__FUNCTION__);
  
  if (!isset($libraries[$theme])) {
    $libraries[$theme] = alpha_cache_get('alpha_libraries', $theme);
  }

  if (!isset($libraries[$theme])) {
    $libraries[$theme] = alpha_register_libraries($theme);
  }
  
  if (isset($library) && isset($libraries[$theme][$library])) {
    return $libraries[$theme][$library];
  } 
  else if (!isset($library)) {
    return $libraries[$theme];
  }
}

/**
 * Retrieves all available libraries for a theme and caches them in a 
 * variable.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 */
function alpha_register_libraries($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $cache = array();  

  foreach (alpha_info('libraries', $theme) as $key => $data) {
    foreach ($data as $name => $info) {
      $cache[$name] = array(
        'name' => $info['name'],
        'description' => isset($info['description']) ? $info['description'] : '',
      );
      
      foreach (array('css', 'js') as $type) {
        if (!empty($info[$type])) {
          foreach ($info[$type] as $index => $item) {
            $options = isset($item['options']) ? $item['options'] : array();
            $options = $options + array('group' => ($type == 'css' ? CSS_THEME : JS_THEME));
            
            $cache[$name][$type][$index] = array(
              'path' => drupal_get_path('theme', $key) . '/' . (isset($item['path']) ? $item['path'] : $type) . '/' . $item['file'],
              'options' => $options,
            );
          }
        }
      }
    }
  }
  
  alpha_alter('alpha_libraries_registry', $cache, $theme);
  alpha_cache_set('alpha_libraries', $cache, $theme);
    
  return $cache;
}

/**
 * @todo
 */
function alpha_library_active($library, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $settings = alpha_settings($theme);
  
  if (isset($settings['libraries'][$library]) && $settings['libraries'][$library] === $library) {
    return TRUE;
  }
  
  return FALSE;
}

/**
 * Adds a column layout for a grid to your site.
 * 
 * @param $grid
 *   A valid grid name.
 *
 * @param $columns
 *   An array of column sizes.
 */
function alpha_grid_include($grid, $columns) {
  if ($grid = alpha_grids($grid)) {
    $settings = alpha_settings();

    foreach (alpha_grid_css($grid, $columns) as $key => $options) {
      drupal_add_css($key, $options);
    }

    if ($settings['debug']['grid'] && $settings['debug']['access']) {
      alpha_grid_debug($grid, $columns);
    }
  }
}

/**
 * @todo
 */
function alpha_grid_debug($grid, $columns) {
  $settings = alpha_settings();
  
  if (!$settings['responsive']) {
    $grid['layouts'] = array($grid['primary'] => $grid['layouts'][$grid['primary']]);
  }

  foreach ($grid['layouts'] as $layout => $item) {
    if (!$settings['responsive'] || $item['enabled']) {
      $path = $path = $grid['path'] . '/' . $layout . '/' . str_replace('_', '-', $settings['grid']) . '-' . str_replace('_', '-', $layout);
      $options = array(
        'type' => 'inline',          
        'browsers' => $settings['responsive'] ? array('IE' => 'gte IE 9', '!IE' => TRUE) : array('IE' => TRUE, '!IE' => TRUE),
        'group' => -1000,
        'weight' => $item['weight'],
        'media' => $settings['responsive'] ? $item['media'] : 'all',
      );

      foreach ($columns as $count => $title) {
        if (isset($grid['columns'][$count])) {
          $file = $path . '-' . $count . '.png';            
          $overlay = '.alpha-grid-debug .container-' . $count . ' { background-image: ' . (is_file($file) ? 'url(' . file_create_url($file) . ')' : 'none') . '; }';

          if ($settings['responsive'] && $layout == $grid['primary']) {
            drupal_add_css($overlay, array('media' => 'all', 'browsers' => array('IE' => '(lt IE 9)&(!IEMobile)', '!IE' => FALSE), 'group' => -2000) + $options);
          }

          drupal_add_css($overlay, $options);
        }
      }
    }
  }
}

/**
 * @todo
 */
function alpha_grid_css($grid, $columns) {
  $output = array();
  
  $settings = alpha_settings();
  $name = str_replace('_', '-', $grid['grid']);    
  $browsers = $settings['responsive'] ? array('IE' => 'gte IE 9', '!IE' => TRUE) : array('IE' => TRUE, '!IE' => TRUE);

  if (!$settings['responsive']) {
    $grid['layouts'] = array($grid['primary'] => $grid['layouts'][$grid['primary']]);
  }

  foreach ($grid['layouts'] as $layout => $item) {
    if (!$settings['responsive'] || $item['enabled']) {
      $path = $grid['path'] . '/' . $layout . '/' . $name . '-' . str_replace('_', '-', $layout);
      $media = $settings['responsive'] ? $item['media'] : 'all';

      foreach ($columns as $count => $data) {          
        if (isset($grid['columns'][$count])) {
          $file = $path . '-' . $count . '.css';

          if ($settings['responsive'] && $layout == $grid['primary']) {
            $basename = 'ie::' . $layout . '::' . $path . '-' . $count . '.css';
            $output[$basename] = array(
              'group' => 1000,
              'weight' => $item['weight'],
              'data' => $file,
              'basename' => $basename,
              'browsers' => array('IE' => '(lt IE 9)&(!IEMobile)', '!IE' => FALSE),                
            );
          }

          $output[$file] = array(
            'group' => 2000,
            'weight' => $item['weight'],
            'media' => $media,
            'browsers' => $browsers,          
            'aggregate' => TRUE,
          );

          foreach ($item['attached'] as $attached) {
            $basename = $layout . '::' . $attached;

            if ($settings['responsive'] && $layout == $grid['primary']) {
              $output['ie::' . $basename] = array(
                'group' => 1000,
                'weight' => $item['weight'],
                'data' => $attached, 
                'basename' => 'ie::' . $basename,
                'browsers' => array('IE' => '(lt IE 9)&(!IEMobile)', '!IE' => FALSE),
              );
            }

            $output[$basename] = array(
              'group' => 2000,
              'weight' => $item['weight'],                
              'data' => $attached, 
              'basename' => $basename,                
              'media' => $media,
              'browsers' => $browsers,
              'aggregate' => TRUE,                
            );
          }
        }
      }
    }
  }
  
  return $output;
}

/**
 * @todo
 */
function alpha_grid_css_group($items) {
  $output = array();
  $group = $media = NULL;
  $i = $j = -1;

  foreach ($items as $item) {
    ksort($item['browsers']);
          
    if (array($item['group'], $item['every_page'], $item['browsers']) !== $group) {        
      $group = array($item['group'], $item['every_page'], $item['browsers']);
      
      $output[++$i] = array(
        'subgroups' => array(),
        'browsers' => $item['browsers'],
      	'group' => $item['group'],
       	'every_page' => $item['every_page'],
      );
    }
    
    if ($item['media'] !== $media) {
      $media = $item['media'];
      $subkey = hash('sha256', serialize($media));
      
      $output[$i]['subgroups'][++$j] = array(
        'media' => $media,
        'items' => array(),
      );
    }
    
    $output[$i]['subgroups'][$j]['items'][] = $item;
  }
  
  return $output;
}

/**
 * @todo
 */
function alpha_grid_css_aggregate($elements) {
  $settings = alpha_settings();
  
  if ($settings['responsive']) {
    if ($chunks = alpha_grid_css_chunks($elements['#items'])) {
      foreach ($chunks as $items) {
        $offset = alpha_array_key_search(key($items), $elements['#items']);
        
        if ($offset !== FALSE) {
          $aggregated = array();
          
          foreach (alpha_grid_css_group($items) as $key => $info) {
            $info['data'] = alpha_grid_css_cache($info);
            $info['media'] = 'all';
            $info['preprocess'] = TRUE;
            $info['type'] = 'file';
            $aggregated[$key] = $info;
          }

          array_splice($elements['#items'], $offset, count($items), $aggregated);
        }
      }
    }
  }
  
  return $elements;
}

/**
 * @todo
 */
function alpha_grid_css_chunks($items) {
  $output = array();
  $i = 0;
  
  foreach ($items as $key => $item) {
    if (!empty($item['aggregate']) && $item['type'] == 'file' && $item['preprocess']) {
      $output[$i][$key] = $item;
    }
    else if (!empty($output[$i])) {
      $i++;
    }
  }
  
  return $output;
}

/**
 * @todo
 */
function alpha_grid_css_cache($group) {
  $map = variable_get('drupal_css_cache_files', array());
  $key = hash('sha256', serialize($group));
  $uri = isset($map[$key]) ? $map[$key] : NULL;
  
  if (empty($uri) || !file_exists($uri)) {
    $data = alpha_grid_css_merge($group['subgroups']);
    
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
    
    variable_set('drupal_css_cache_files', $map);
  }
  
  return $uri;
}

/**
 * @todo
 */
function alpha_grid_css_merge($group) {
  $data = '';
  
  foreach ($group as $info) {
    $contents = '';
    
    foreach ($info['items'] as $stylesheet) {
      // Build the base URL of this CSS file: start with the full URL.
      $base = file_create_url($stylesheet['data']);
      $base = substr($base, 0, strrpos($base, '/'));

      if (substr($base, 0, strlen($GLOBALS['base_root'])) == $GLOBALS['base_root']) {
        $base = substr($base, strlen($GLOBALS['base_root']));
      }

      _drupal_build_css_path(NULL, $base . '/');
      
      // Anchor all paths in the CSS with its base URL, ignoring external and absolute paths.
      $contents .= preg_replace_callback('/url\(\s*[\'"]?(?![a-z]+:|\/+)([^\'")]+)[\'"]?\s*\)/i', '_drupal_build_css_path', drupal_load_stylesheet($stylesheet['data'], TRUE));
    }
    
    $data .= $info['media'] != 'all' ? '@media ' . $info['media'] . '{' . $contents . '}' : $contents; 
  }
  
  return $data;
}

/**
 * Retrieves all available grids for a theme.
 * 
 * @param $grid (optional)
 *   The name of the grid that you want to fetch. Defaults to NULL.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return
 *   An array of grids or the grid defined by $grid.
 */
function alpha_grids($grid = NULL, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $grids = &drupal_static(__FUNCTION__);
  
  if (!isset($grids[$theme])) {
    $grids[$theme] = alpha_cache_get('alpha_grids', $theme);
    
    if (!isset($grids[$theme])) {
      $grids[$theme] = alpha_register_grids($theme);
    }

    foreach ($grids[$theme] as $name => $info) {
      $grids[$theme][$name]['primary'] = alpha_theme_get_setting('alpha_primary_' . $name, key($info['layouts']), $theme);
      
      foreach ($grids[$theme][$name]['layouts'] as $layout => $data) {
        $grids[$theme][$name]['layouts'][$layout] = $data + array(
          'enabled' => alpha_theme_get_setting('alpha_layouts_' . $name . '_' . $layout . '_responsive', FALSE, $theme),
          'media' => alpha_theme_get_setting('alpha_layouts_' . $name . '_' . $layout . '_media', 'all', $theme),
          'weight' => alpha_theme_get_setting('alpha_layouts_' . $name . '_' . $layout . '_weight', 0, $theme),
        );
      }
      
      uasort($grids[$theme][$name]['layouts'], 'alpha_sort_layouts');
    }
  }

  if (isset($grid) && isset($grids[$theme][$grid])) {
    return $grids[$theme][$grid];
  } 
  else if (!isset($grid)) {
    return $grids[$theme];
  }
}

/**
 * Retrieves all available grids for a theme and caches them in a 
 * variable.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 */
function alpha_register_grids($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $cache = array();

  $trail = alpha_theme_trail($theme);
  
  foreach (alpha_info('grids', $theme) as $key => $data) {
    foreach ($data as $name => $info) {
      $cache[$name] = array(
        'grid' => $name,
        'name' => $info['name'],
        'path' => drupal_get_path('theme', $key) . '/css/grid/' . $name,
        'layouts' => array(),
        'columns' => $info['columns'],
      );
      
      foreach ($info['layouts'] as $layout => $title) {
        $cache[$name]['layouts'][$layout] = array(
          'layout' => $layout,
          'name' => $title,
          'attached' => array(),
        );
        
        foreach ($trail as $item) {
          $path = drupal_get_path('theme', $item->name) . '/css';
          $file = implode('-', array(str_replace('_', '-', $item->name), str_replace('_', '-', $name)));
          
          if ($attached = alpha_find_stylesheet($path, $file)) {
            $cache[$name]['layouts'][$layout]['attached'][] = $attached;
          }
          
          $file = $file . '-' . str_replace('_', '-', $layout);
          if ($attached = alpha_find_stylesheet($path, $file)) {
            $cache[$name]['layouts'][$layout]['attached'][] = $attached;
          }
        }
      }
    }
  }
  
  alpha_alter('alpha_grids_registry', $cache, $theme);  
  alpha_cache_set('alpha_grids', $cache, $theme);
    
  return $cache;
}

/**
 * @todo
 */
function alpha_find_stylesheet($path, $name) {
  foreach (array('css', 'css.less', 'sass', 'scss') as $extension) {
    $file = $path . '/' . $name . '.' . $extension;
    
    if (is_file($file)) {
      return $file;
    }
  }
  
  return FALSE;
}

/**
 * @todo
 */
function alpha_children_first_last(&$elements) {
  if (!empty($children)) {
    if ($children = element_children($elements)) {
      $last = count($children) - 1;

      foreach ($children as $element) {
        $elements[$element]['#first'] = $element == $children[0];
        $elements[$element]['#last'] = $element == $children[$last];
      }
    }
  }
}

/**
 * @todo
 */
function alpha_calculate_primary(&$regions, $primary, $container) {
  if (!empty($regions)) {
    $reference = &drupal_static('alpha_regions');

    $regions[$primary]['#data']['columns'] = $container - $regions[$primary]['#data']['prefix'] - $regions[$primary]['#data']['suffix'];
    $regions[$primary]['#data']['width'] = $container;

    foreach (element_children($regions) as $region) {
      if ($region != $primary) {
        $regions[$primary]['#data']['columns'] -= $regions[$region]['#data']['width'];
        $regions[$primary]['#data']['width'] -= $regions[$region]['#data']['width'];
      }
    }

    $reference[$GLOBALS['theme_key']][$primary]['width'] = $regions[$region]['#data']['width'];
    $reference[$GLOBALS['theme_key']][$primary]['columns'] = $regions[$region]['#data']['columns'];
  }
}

/**
 * @todo
 */
function alpha_calculate_position(&$regions) {
  if (!empty($regions)) {
    $reference = &drupal_static('alpha_regions');
    $children = element_children($regions, TRUE);

    foreach ($children as $a => $region) {
      foreach ($children as $b => $inner) {
        if ($region != $inner) {
          if ($a >= $b && $regions[$region]['#data']['position'] < $regions[$inner]['#data']['position']) {
            $regions[$region]['#data']['pull'] += $regions[$inner]['#data']['width'];
          }
          else if ($a <= $b && $regions[$region]['#data']['position'] > $regions[$inner]['#data']['position']) {
            $regions[$region]['#data']['push'] += $regions[$inner]['#data']['width'];
          }
        }
      }

      if ($regions[$region]['#data']['pull'] > $regions[$region]['#data']['push']) {
        $regions[$region]['#data']['pull'] -= $regions[$region]['#data']['push'];
        $regions[$region]['#data']['push'] = 0;
      }
      else if ($regions[$region]['#data']['pull'] > $regions[$region]['#data']['push']) {
        $regions[$region]['#data']['push'] -= $regions[$region]['#data']['pull'];
        $regions[$region]['#data']['pull'] = 0;
      }
      else if ($regions[$region]['#data']['pull'] == $regions[$region]['#data']['push']) {
        $regions[$region]['#data']['pull'] = 0;
        $regions[$region]['#data']['push'] = 0;
      }

      $reference[$GLOBALS['theme_key']][$region]['pull'] = $regions[$region]['#data']['pull'];
      $reference[$GLOBALS['theme_key']][$region]['push'] = $regions[$region]['#data']['push'];
    }
  }
}

/**
 * Retrieves all available zones for a theme.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *
 * @return
 *   An array of zones and their settings.
 */
function alpha_zones($name = NULL, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $output = &drupal_static(__FUNCTION__);
  
  if (!isset($output[$theme])) {
    $themes = list_themes();
    $sections = alpha_sections();
    
    $output[$theme] = array();
    if (isset($themes[$theme]->info['zones'])) { 
      foreach ($themes[$theme]->info['zones'] as $zone => $title) {
        $section = alpha_zone_get_setting('section', $zone, NULL, $theme);
        
        $output[$theme][$zone] = array(
          'zone' => $zone,
          'name' => $title,
          'enabled' => isset($sections[$section]),
          'force' => alpha_zone_get_setting('force', $zone, FALSE, $theme),
          'columns' => alpha_zone_get_setting('columns', $zone, 0, $theme),
          'section' => $section,
          'weight' => alpha_zone_get_setting('weight', $zone, 0, $theme),
          'wrapper' => alpha_zone_get_setting('wrapper', $zone, FALSE, $theme),
          'wrapper_css' => alpha_zone_get_setting('wrapper_css', $zone, NULL, $theme),
          'primary' => alpha_zone_get_setting('primary', $zone, NULL, $theme),
          'order' => alpha_zone_get_setting('order', $zone, FALSE, $theme),
          'css' => alpha_zone_get_setting('css', $zone, NULL, $theme),
        );
      }
    }
    
    uasort($output[$theme], 'drupal_sort_weight');
    
    alpha_alter('alpha_zones', $output[$theme], $theme);
  }
  
  if (isset($name) && isset($output[$theme][$name])) {
    return $output[$theme][$name];
  }
  else if (!isset($name)) {
    return $output[$theme];
  }  
}

/**
 * @todo
 */
function alpha_zone_regions($zone, $theme = NULL) {
  $output = array();
  $regions = alpha_regions($theme);
  
  foreach ($regions as $region => $item) {
    if ($item['zone'] == $zone) {
      $output[$region] = $item['name'];
    }
  }
  
  return $output;
}

/**
 * Retrieves all available regions for a theme.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *
 * @return
 *   An array of regions and their settings.
 */
function alpha_regions($name = NULL, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $output = &drupal_static(__FUNCTION__);

  if (!isset($output[$theme])) {
    $regions = system_region_list($theme);
    $zones = alpha_zones(NULL, $theme);
    $exclude = alpha_regions_exclude();
    
    $output[$theme] = array();
    foreach ($regions as $region => $title) {
      if (!in_array($region, $exclude)) {
        $zone = alpha_region_get_setting('zone', $region, NULL, $theme);
        $prefix = alpha_region_get_setting('prefix', $region, 0, $theme);
        $columns = alpha_region_get_setting('columns', $region, 1, $theme);
        $suffix = alpha_region_get_setting('suffix', $region, 0, $theme);

        $output[$theme][$region] = array(
          'region' => $region,
          'name' => $title,
          'zone' => $zone,
          'enabled' => !empty($zone) && isset($zones[$zone]),
          'force' => alpha_region_get_setting('force', $region, FALSE, $theme),
          'prefix' => $prefix,
          'columns' => $columns,
          'suffix' => $suffix,
          'width' => $prefix + $columns + $suffix,
          'push' => 0,
          'pull' => 0,
          'css' => alpha_region_get_setting('css', $region, NULL, $theme),
          'weight' => alpha_region_get_setting('weight', $region, 0, $theme),
          'position' => alpha_region_get_setting('position', $region, 0, $theme),
          'primary' => isset($zone) && isset($zones[$zone]) ? $zones[$zone]['primary'] == $region : FALSE,
        );
      }
    }
    
    uasort($output[$theme], 'drupal_sort_weight');
    
    alpha_alter('alpha_regions', $output[$theme], $theme);
  }
  
  if (isset($name) && isset($output[$theme][$name])) {
    return $output[$theme][$name];
  }
  else if (!isset($name)) {
    return $output[$theme];
  }  
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
 * @param $item
 *   The name of the variable that you want to fetch.
 *   
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @return
 *   The $item content of all themes .info files in the theme trail.
 */
function alpha_info($item, $theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $output = &drupal_static(__FUNCTION__);
  
  if (!isset($output[$theme][$item])) {
    $output[$theme][$item] = array();
    foreach (alpha_theme_trail($theme) as $name => $info) {
      if (!empty($info->info[$item])) {
        $output[$theme][$item][$name] = $info->info[$item];
      }
    }    
  }
  
  return $output[$theme][$item];
}

/**
 * A helper function to gather all non-region and non-zone settings for the
 * theme defined by $key.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *
 * @return
 *   A nested array of theme settings.
 */
function alpha_settings($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  $settings = &drupal_static(__FUNCTION__);

  if (!isset($settings[$theme])) {
    $css = alpha_css($theme);
    
    $settings[$theme] = array(
      'grid' => alpha_theme_get_setting('alpha_grid', 'default', $theme),
      'primary' => alpha_theme_get_setting('alpha_primary', NULL, $theme),
      'css' => alpha_theme_get_setting('alpha_css', array(), $theme),
      'libraries' => alpha_theme_get_setting('alpha_libraries', array(), $theme),
      'exclude' => alpha_theme_get_setting('alpha_exclude', array(), $theme),      
      'responsive' => alpha_theme_get_setting('alpha_responsive', FALSE, $theme),
      'toggle' => array(),
      'hidden' => array(),
      'viewport' => array(
        'enabled' => alpha_theme_get_setting('alpha_viewport', FALSE, $theme),
        'initial' => alpha_theme_get_setting('alpha_viewport_initial_scale', 1, $theme),
        'min' => alpha_theme_get_setting('alpha_viewport_min_scale', 1, $theme),
        'max' => alpha_theme_get_setting('alpha_viewport_max_scale', 1, $theme),
        'user' => alpha_theme_get_setting('alpha_viewport_user_scaleable', TRUE, $theme),
      ),
      'debug' => array(
        'block' => alpha_theme_get_setting('alpha_debug_block_toggle', FALSE, $theme),
        'block_active' => alpha_theme_get_setting('alpha_debug_block_active', FALSE, $theme),
        'grid' => alpha_theme_get_setting('alpha_debug_grid_toggle', FALSE, $theme),
        'grid_active' => alpha_theme_get_setting('alpha_debug_grid_active', FALSE, $theme),
        'roles' => array_keys(array_filter(alpha_theme_get_setting('alpha_debug_grid_roles', array(), $theme))),
      ),
    );

    foreach (alpha_toggle() as $item => $title) {
      $settings[$theme]['toggle'][$item] = alpha_theme_get_setting('alpha_toggle_' . $item, TRUE, $theme);
    }
    
    foreach (alpha_visibility() as $item => $title) {
      $settings[$theme]['hidden'][$item] = alpha_theme_get_setting('alpha_hidden_' . $item, FALSE, $theme);
    }

    $settings[$theme]['debug']['access'] = alpha_debug_access($GLOBALS['user'], $settings[$theme]['debug']['roles']);
    
    alpha_alter('alpha_settings', $settings[$theme], $theme);
  }
  
  return $settings[$theme];
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
      $functions = &drupal_static('drupal_alter');
      
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
      
      $theme = $theme_original;
      $base_theme_info = $base_theme_info_original;
      
      unset($functions[$type]);
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
 * Builds and returns a debugging block.
 * 
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
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
function alpha_excludes($theme = NULL) {
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
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
  
  foreach (alpha_info('stylesheets', $theme) as $item => $data) {
    foreach ($data as $media => $content) {
      foreach ($content as $file) {
        $styles[$file] = array(
          'type' => 'theme',
          'source' => $item,
          'name' => $themes[$item]->info['name'],
          'file' => $file,
          'media' => $media,
          'description' => NULL,
        );
      }
    }
  }
  
  foreach (alpha_info('exclude', $theme) as $item => $data) {
    foreach ($data as $file => $description) {
      $styles[$file] = array(
        'type' => 'exclude',
        'source' => $item,
        'name' => $themes[$item]->info['name'],
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

/**
 * @todo
 */
function alpha_array_key_search($needle, $haystack) {
  $offset = 0;
  
  foreach ($haystack as $key => $item) {
    if ($needle === $key) {
      return $offset;
    }
    
    $offset++;
  }
  
  return FALSE;
}