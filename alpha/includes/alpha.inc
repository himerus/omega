<?php 

/*
 * @todo
 */
function alpha_invoke($type, $hook, &$vars) {
  global $theme_key;
  $registry = &drupal_static(__FUNCTION__);
  if (!isset($registry)) {
    $registry = variable_get('alpha_theme_registry_' . $theme_key);
  }

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
function alpha_css_options($key, $responsive = FALSE) {
  $items = alpha_css($key, $responsive);
  
  $output = array(); 
  foreach ($items as $key => $info) {
    $output[$key] = t('<strong>@name</strong> (@media) - @file: !description', array('@name' => $info['name'], '@file' => $info['file'], '@media' => $info['media'], '!description' => $info['description']));
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
function alpha_css($key, $responsive = FALSE) {
  $output = &drupal_static(__FUNCTION__);
  
  if (!isset($output[$key][$responsive])) {
    $output[$key][$responsive] = array();  

    foreach (alpha_info($key, 'css') as $theme => $data) {
      foreach ($data as $name => $info) {
        if (($responsive && $info['media'] == 'responsive') || (!$responsive && $info['media'] != 'responsive')) {
          $output[$key][$responsive][$name] = array(
            'name' => $info['name'],
            'file' => $info['file'],
            'weight' => isset($info['weight']) ? $info['weight'] : 0,
            'path' => drupal_get_path('theme', $theme) . '/css/' . $info['file'],
            'description' => isset($info['description']) ? $info['description'] : '',
            'media' => isset($info['media']) ? $info['media'] : 'all',
          );
        }
      }
    }
  }

  return $output[$key][$responsive];
}

/*
 * @todo
 */
function alpha_include_grid($name, $columns) {
  global $theme_key, $language, $user;

  $included = &drupal_static(__FUNCTION__);
  
  if (!isset($included[$name][$columns]) || !$included[$name][$columns]) {
    $included[$name][$columns] = TRUE;
    
    $grid = alpha_grids($theme_key, $name);
    $settings = alpha_settings($theme_key);
    $css = alpha_css($theme_key, TRUE);
    
    if (!empty($grid) && isset($columns) && isset($grid['columns'][$columns])) {
      $path = $grid['columns'][$columns];
  
      if ($grid['type'] == 'fluid') {
        drupal_add_css($path . '/fluid-grid' . ($language->direction == LANGUAGE_RTL ? '-rtl.css' : '.css'));
        drupal_add_css($path . '/columns/fluid-' . $columns . '.css');
      }
      else {
        $widths = array('narrow' => -3, 'normal' => -2, 'wide' => -1);
  
        foreach ($widths as $type => $weight) {
          if ($type == 'normal' || ($settings['responsive']['enabled'] && $grid[$type] && $settings['responsive'][$type]['enabled'])) {
            $media = $settings['responsive']['enabled'] ? $settings['responsive'][$type]['media'] : 'all';
            $options = array('media' => $media, 'weight' => $weight);
            
            drupal_add_css($path . '/' . $type . '/' . $type . '-grid' . ($language->direction == LANGUAGE_RTL ? '-rtl.css' : '.css'), $options);
            drupal_add_css($path . '/' . $type . '/' . $type . '-' . $columns . '.css', $options);
            
            foreach ($settings['responsive'][$type]['css'] as $item => $enabled) {              
              if (isset($css[$item]) && $enabled) {
                drupal_add_css($css[$item]['path'], $options);
              }
            }
          }        
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
        'push' => 0,
        'pull' => 0,
        'css' => alpha_region_get_setting('css', $region, $key),
        'weight' => alpha_region_get_setting('weight', $region, $key, 0),
        'primary' => isset($zone) && isset($zones[$zone]) ? $zones[$zone]['primary'] == $region : FALSE,
      );
      
      $output[$region]['width'] = $output[$region]['columns'] + $output[$region]['prefix'] + $output[$region]['suffix'];
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

/*
 * @todo
 */
function alpha_settings($key) {
  $settings = array(
    'grid' => alpha_theme_get_setting('alpha_grid', $key, 'default'),
    'css' => alpha_theme_get_setting('alpha_css', $key, array()),
    'responsive' => array(
   	  'enabled' => alpha_theme_get_setting('alpha_responsive', $key, FALSE),
      'narrow' => array(
        'enabled' => alpha_theme_get_setting('alpha_responsive_narrow', $key, FALSE),
        'media' => alpha_theme_get_setting('alpha_responsive_narrow_media', $key, 'all'),
        'css' => alpha_theme_get_setting('alpha_responsive_narrow_css', $key),
      ),
      'normal' => array(
        'enabled' => alpha_theme_get_setting('alpha_responsive_normal', $key, TRUE),
        'media' => alpha_theme_get_setting('alpha_responsive_normal_media', $key, 'all'),
        'css' => alpha_theme_get_setting('alpha_responsive_normal_css', $key),
      ),
      'wide' => array(
        'enabled' => alpha_theme_get_setting('alpha_responsive_wide', $key, FALSE),
        'media' => alpha_theme_get_setting('alpha_responsive_wide_media', $key, 'all'),
        'css' => alpha_theme_get_setting('alpha_responsive_wide_css', $key),
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
      'roles' => alpha_theme_get_setting('alpha_debug_grid_roles', $key),
    ),
    // adding in fake block rendering settings to be passed through
    'blocks' => array(
      'branding' => array(
        'enabled' => alpha_theme_get_setting('alpha_blocks_branding', $key, TRUE),
        'region' => alpha_theme_get_setting('alpha_blocks_branding_region', $key),
        'weight' => alpha_theme_get_setting('alpha_blocks_branding_weight', $key),
        'name' => alpha_theme_get_setting('alpha_blocks_branding_name_hidden', $key),
        'slogan' => alpha_theme_get_setting('alpha_blocks_branding_slogan_hidden', $key, TRUE),
      ),
    ),
  );
  
  foreach (array('narrow', 'normal', 'wide') as $type) {
    $settings['responsive'][$type]['css'] = is_array($settings['responsive'][$type]['css']) ? $settings['responsive'][$type]['css'] : array();
  }
  
  $settings['debug']['roles'] = is_array($settings['debug']['roles']) ? $settings['debug']['roles'] : array();
  $settings['css'] = is_array($settings['css']) ? $settings['css'] : array();
  $settings['blocks'] = is_array($settings['blocks']) ? $settings['blocks'] : array();
  return $settings;
}


/*
 * @todo
 */
function alpha_debug_access($user, $roles) {
  foreach ($roles as $role => $assigned) {
    if ($assigned && isset($user->roles[$role])) {
      return TRUE;
    }
  }
  
  return FALSE;
}


/*
 * @todo
 */
function alpha_regions_exclude() {
  return array('page_top', 'page_bottom');
}


/*
 * @todo
 */
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




/*
 * @todo
 */
function alpha_debug_grid($settings, $columns) {
  global $theme_key;
  
  $debug = &drupal_static(__FUNCTION__);
  $grid = alpha_grids($theme_key, $settings['grid']);
  
  if ($grid['type'] != 'fluid' && !isset($debug[$columns])) {
    $widths = array('narrow' => -3, 'normal' => -2, 'wide' => -1);
                
    foreach ($widths as $type => $weight) {
      if ($type == 'normal' || ($settings['responsive']['enabled'] && $grid[$type] && $settings['responsive'][$type]['enabled'])) {
        $media = $settings['responsive']['enabled'] ? $settings['responsive'][$type]['media'] : 'all';
        $path = $grid['columns'][$columns] . '/' . $type . '/debug-' . $columns . '.png';
        $css = 'body.alpha-grid-debug .container-' . $columns . ' { background-image: url(' . url($path) . '); }';
        
        drupal_add_css($css, array('type' => 'inline', 'media' => $media, 'weight' => $weight));
      }        
    }
  }
  
  $debug[$columns] = TRUE;
}