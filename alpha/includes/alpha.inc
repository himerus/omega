<?php 

/*
 * @todo
 */
function alpha_invoke($type, $hook, &$vars) {
  global $theme_key;

  $registry = variable_get('alpha_theme_registry_' . $theme_key);

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

/*
 * @todo
 */
function alpha_theme_trail($name) {
  $static = &drupal_static(__FUNCTION__);
  
  if (!isset($static)) {
    $themes = list_themes();
    $static[$name] = !empty($themes[$name]->info['base theme']) ? system_find_base_themes($themes, $name) : array();
    
    $static[$name][$themes[$name]->name] = $themes[$name]->info['name'];
  }

  return $static[$name];
}

/*
 * @todo
 */
function alpha_build_registry($theme, $registry) {
  $cache = array();
  foreach ($registry as $item => $info) {
    foreach (array('preprocess', 'process') as $type) {
      $key = $type . ' functions';
     
      if (!empty($info[$key])) {
        foreach (alpha_theme_trail($theme) as $name => $friendly) {
          $file = $type . '-' . str_replace('_', '-', $item) . '.inc';    
          $path = DRUPAL_ROOT . '/' . drupal_get_path('theme', $name) . '/' . $type . '/' . $file;
          
          if (is_file($path)) {
            $cache[$type][$item][] = array(
              'type' => $type,
              'hook' => $item,
              'theme' => $name,
              'path' => $path,
              'function' => $name . '_alpha_' . $type . '_' . $item,
            );
          }
        }
      }
    }
  }

  variable_set('alpha_theme_registry_' . $theme, $cache);
}

/**
 * @todo
 */
function alpha_theme_get_setting($name, $theme = NULL, $default = NULL) {
  // Need to change this for Delta implementation.
  $setting = theme_get_setting($name, $theme);
  
  return isset($setting) ? $setting : $default; 
}

/*
 * @todo
 */
function alpha_scale_options($start, $end, $step) {
  $options = array();  
  foreach (range($start, $end, $step) as $number) {
    $options[(string) $number] = number_format($number, 1);
  }
  
  return $options;
}

/*
 * @todo
 */
function alpha_css_options($key) {
  $items = alpha_css($key);
  
  $output = array(); 
  foreach ($items as $key => $info) {
    $output[$key] = t('<strong>@name</strong> (@media) - !description', array('@name' => $info['name'], '@media' => $info['media'], '!description' => $info['description']));
  }
  
  return $output;
}

/*
 * @todo
 */
function alpha_grid_options($key) {
  $items = alpha_grids($key);
  
  $output = array(); 
  foreach ($items as $key => $info) {
    $output[$key] = check_plain($info['name']);
  }
  
  return $output;
}

/*
 * @todo
 */
function alpha_zone_options($key) {
  $items = alpha_zones($key);
  
  $output = array(); 
  foreach ($items as $key => $info) {
    $output[$key] = check_plain($info['name']);
  }
  
  return $output;
}

/*
 * @todo
 */
function alpha_container_options($key, $grid) {
  $grids = alpha_grids($key);
  
  $output = array();  
  foreach ($grids[$grid]['columns'] as $count => $path) {
    $output[$count] = t('@count columns', array('@count' => $count));
  }
  
  return $output;
}

/*
 * @todo
 */
function alpha_column_options($max) {
  $output = array();
  foreach (range(0, $max) as $width) {
    $output[$width] = t('@width columns', array('@width' => $width));
  }
  
  return $output;
}

/*
 * @todo
 */
function alpha_css($key) {
  $output = array();
  foreach (alpha_info($key, 'css') as $theme => $data) {
    foreach ($data as $name => $info) {    
      $output[$name] = array(
        'name' => $info['name'],
        'file' => $info['file'],
        'path' => drupal_get_path('theme', $theme) . '/css/' . $info['file'],
        'description' => isset($info['description']) ? $info['description'] : '',
        'media' => isset($info['media']) ? $info['media'] : 'all',
      );
    }
  }

  return $output;
}

/*
 * @todo
 */
function alpha_include_grid($name, $columns = NULL) {
  global $theme_key, $language;

  $grid = alpha_grids($theme_key, $name);

  if (!empty($grid)) {
    $file = $grid['path'] . '/grid' . ($language->direction == LANGUAGE_RTL ? '-rtl.css' : '.css');
    
    drupal_add_css($file, array('weight' => -4));
    
    if (isset($columns) && isset($grid['columns'][$columns])) {   
      // Column layouts can be provided by subthemes, therefore each column needs
      // to be included using the path that the column-defining theme
      // resides in. This path can be used for all additional .css files.
      $path = $grid['columns'][$columns];

      // If the current grid is fluid the files and pathes are a bit different.
      if ($grid['type'] == 'fluid') {
        $file = $path . '/columns/fluid-' . $columns . '.css';
        $files[$file] = 'all';
      }
      // If the current grid is a fixed pixel grid we need to do a little bit more
      // (at least if responsive grids are enabled).
      else {
        // We need to react on the 'alpha_responsive_grid' setting and choose the
        // proper CSS files accordingly. The normal width grid CSS files are ALWAYS
        // being included in order to make sure that even browsers / devices that are 
        // incapable of reading the @media tag properly will at least have the
        // normal width grid CSS.
        if (alpha_theme_get_setting('alpha_responsive', $theme_key, FALSE)) {
          // In this case we need to include all the enabled sizes in the order
          // 'narrow' => 'normal' => 'wide'.
          $order = array('narrow' => -3, 'normal' => -2, 'wide' => -1);
                  
          foreach ($order as $type => $weight) {
            if ($type == 'normal' || ($grid[$type] && alpha_theme_get_setting('alpha_responsive_' . $type, $theme_key, FALSE))) {
              $media = alpha_theme_get_setting('alpha_responsive_' . $type . '_media', $theme_key, 'all');
              $media = !empty($media) ? $media : 'all';
              $file = $path . '/' . $type . '/' . $type . '-' . $columns . '.css';            
              
              drupal_add_css($file, array('media' => $media, 'weight' => $weight));
            }
          }          
        }
        else {
          $file = $path . '/normal/normal-' . $columns . '.css';
          
          drupal_add_css($file);
        }
      }
    }
  }
}

/*
 * @todo
 */
function alpha_zone_get_setting($name, $zone, $theme = NULL, $default = NULL) {
  return alpha_theme_get_setting('alpha_zone_' . $zone . '_' . $name, $theme, $default);
}

/*
 * @todo
 */
function alpha_region_get_setting($name, $region, $theme = NULL, $default = NULL) {
  return alpha_theme_get_setting('alpha_region_' . $region . '_' . $name, $theme, $default);
}

/*
 * @todo
 */
function alpha_grids($key, $grid = NULL) {
  $output = &drupal_static(__FUNCTION__);
  
  if (!isset($output[$key])) {
    $output[$key] = array();
    
    foreach (alpha_info($key, 'grids') as $theme => $data) {
      foreach ($data as $name => $info) {
        $path = drupal_get_path('theme', $theme) . '/css/grid/' . $name;
        $fluid = $name == 'fluid';
        
        if (!isset($output[$key][$name])) {
          $output[$key][$name] = array(
            'name' => $info['name'],
            'type' => $fluid ? 'fluid' : 'fixed',
            'path' => $path,
            'narrow' => !$fluid ? is_dir($path . '/narrow') : FALSE,
            'wide' => !$fluid ? is_dir($path . '/wide') : FALSE,
            'columns' => array(),
          );
        }  
          
        foreach ($info['columns'] as $column) {
          $output[$key][$name]['columns'][$column] = $path;
        }   
      }
    }
  }

  if (isset($grid)) {
    return isset($output[$key][$grid]) ? $output[$key][$grid] : FALSE;
  } 
  else {
    return $output[$key];
  }
}

/*
 * @todo
 */
function alpha_zones($key) {
  $themes = list_themes();

  $output = array();
  if (isset($themes[$key]->info['zones'])) { 
    foreach ($themes[$key]->info['zones'] as $zone => $name) {
      $output[$zone] = array(
        'zone' => $zone,
        'name' => $name,
        'enabled' => alpha_zone_get_setting('enabled', $zone, $key, TRUE),
        'columns' => alpha_zone_get_setting('columns', $zone, $key, 0),
        'section' => alpha_zone_get_setting('section', $zone, $key),
        'weight' => alpha_zone_get_setting('weight', $zone, $key, 0),
        'wrapper' => alpha_zone_get_setting('wrapper', $zone, $key, FALSE),
        'primary' => alpha_zone_get_setting('primary', $zone, $key),
        'css' => alpha_zone_get_setting('css', $zone, $key),
      );
    }
  }
  
  uasort($output, 'drupal_sort_weight');
  
  return $output;
}

/*
 * @todo
 */
function alpha_regions($key) {
  $regions = system_region_list($key);
  $zones = alpha_zones($key);
  $exclude = alpha_regions_exclude();
  
  $output = array();
  foreach ($regions as $region => $name) {
    if (!in_array($region, $exclude)) {
      $zone = alpha_region_get_setting('zone', $region, $key);
      
      $output[$region] = array(
        'region' => $region,
        'name' => $name,
        'zone' => $zone,
        'prefix' => alpha_region_get_setting('prefix', $region, $key, 0),
        'columns' => alpha_region_get_setting('columns', $region, $key, 0),
        'suffix' => alpha_region_get_setting('suffix', $region, $key, 0),
        'css' => alpha_region_get_setting('css', $region, $key),
        'weight' => alpha_region_get_setting('weight', $region, $key, 0),
        'primary' => isset($zone) && isset($zones[$zone]) ? alpha_zone_get_setting('primar', $zone, $key) == $region : FALSE,
        'css' => alpha_region_get_setting('css', $region, $key),
      );
    }
  }
  
  uasort($output, 'drupal_sort_weight');
  
  return $output;
}

/*
 * @todo
 */
function alpha_sections() {
  return array(
    'header' => t('Header'),
    'content' => t('Content'),
    'footer' => t('Footer'),
  );
}

/*
 * @todo
 */
function alpha_info($key, $item) {
  $output = &drupal_static(__FUNCTION__);
  
  if (!isset($output[$key][$item])) {
    $themes = list_themes(); 
    
    $output[$key][$item] = array();
    foreach (alpha_theme_trail($key) as $theme => $info) {
      if (!empty($themes[$theme]->info[$item])) {
        $output[$key][$item][$theme] = $themes[$theme]->info[$item];
      }
    }    
  }
  
  return $output[$key][$item];
}

function alpha_settings($key) {
  return array(
    'grid' => alpha_theme_get_setting('alpha_grid', $key, 'default'),
    'css' => alpha_theme_get_setting('alpha_css', $key, array()),
    'responsive' => array(
   	  'enabled' => alpha_theme_get_setting('alpha_responsive', $key, FALSE),
      'narrow' => array(
        'enabled' => alpha_theme_get_setting('alpha_responsive_narrow', $key, FALSE),
        'media' => alpha_theme_get_setting('alpha_responsive_narrow_media', $key),
      ),      
      'normal' => array(
        'enabled' => alpha_theme_get_setting('alpha_responsive_normal', $key, TRUE),
        'media' => alpha_theme_get_setting('alpha_responsive_normal_media', $key),
      ),
      'wide' => array(
        'enabled' => alpha_theme_get_setting('alpha_responsive_wide', $key, FALSE),
        'media' => alpha_theme_get_setting('alpha_responsive_wide_media', $key),
      ),
    ),
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
      'auto' => alpha_theme_get_setting('alpha_debug_grid_load', $key, FALSE),
      'roles' => alpha_theme_get_setting('alpha_debug_grid_roles', $key, array()),
    ),
  );
}

function alpha_debug_access($user, $roles) {
  foreach ($roles as $role => $assigned) {
    if ($assigned && isset($user->roles[$role])) {
      return TRUE;
    }
  }
  
  return FALSE;
}

function alpha_regions_exclude() {
  return array(
  	'page_top', 'page_bottom', 'user_bar_first', 'user_bar_second', 
  	'dashboard_main', 'dashboard_inactive', 'dashboard_sidebar',
  );
}

function alpha_debug_block($region, $info) {
  $block = new stdClass();
  $block->delta = $region;
  $block->region = $region;
  $block->module = 'alpha_debug';
  $block->subject = $info['name'];
  $block->weight = -100;

  return array(
	'#printed' => FALSE,
    '#theme_wrappers' => array('block'),
    '#block' => $block,
    '#markup' => '<div class="alpha-debug-region-data">' . t('This is a debugging block.') . '</div>',
  );
}