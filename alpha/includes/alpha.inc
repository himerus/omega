<?php 

/*
 * @todo
 */
function alpha_invoke($type, $hook, &$vars) {
  global $theme_key;

  $registry = variable_get('alpha_theme_registry_' . $theme_key);

  if (!empty($registry[$type][$hook])) {    
    foreach ($registry[$type][$hook] as $item) {
      $file = $type . '-' . str_replace('_', '-', $hook) . '.inc';
      $file = DRUPAL_ROOT . '/' . drupal_get_path('theme', $item['theme']) . '/' . $type . '/' . $file;
      
      if (is_file($file)) {
        include_once $file;      
        
        $function = implode('_', array($item['theme'], 'alpha', $type, $hook));      
        
        if (function_exists($function)) {
          $function($vars);
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
    $static = !empty($themes[$name]->info['base theme']) ? system_find_base_themes($themes, $name) : array();
    
    $static[$themes[$name]->name] = $themes[$name]->info['name'];
  }

  return $static;
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
          $path = DRUPAL_ROOT . '/' . drupal_get_path('theme', $name) . '/' . $type;
          
          if (is_file($path . '/' . $file)) {
            $cache[$type][$item][] = array(
              'type' => $type,
              'hook' => $item,
              'theme' => $name,
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
function alpha_theme_get_setting($setting_name, $default = NULL) {
  // Need to change this for Delta implementation.
  $setting = theme_get_setting($setting_name);
  
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
    $output[$key] = t('<strong>@name</strong> (@media) - !description', array('@name' => ucwords(str_replace('_', ' ', $key)), '@media' => $info['media'], '!description' => $info['description']));
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
function alpha_container_options($key, $grid) {
  $grids = alpha_grids($key);
  
  $output = array();  
  foreach ($grids[$grid]['columns'] as $count) {
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
        'path' => DRUPAL_ROOT . '/' . drupal_get_path('theme', $theme) . '/css/' . $info['file'],
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
function alpha_grids($key) {
  $output = array();
  foreach (alpha_info($key, 'grids') as $theme => $data) {
    foreach ($data as $name => $info) {
      $output[$name] = array(
        'name' => $info['name'],
        'path' => DRUPAL_ROOT . '/' . drupal_get_path('theme', $theme) . '/css/grid/' . $name,
        'columns' => array_map('trim', explode(',', $info['columns'])),
      );
    }
  }

  return $output;
}

/*
 * @todo
 */
function alpha_zones($key) {
  $regions = system_region_list($key);
  
  $output = array();
  foreach (alpha_info($key, 'zones') as $theme => $data) {
    foreach ($data as $name => $info) {
      $output[$name] = array(
        'name' => $info['name'],
        'regions' => array(),
      );
      
      if (!empty($info['regions'])) {
        foreach ($info['regions'] as $region) {
          if (isset($regions[$region])) {
            $output[$name]['regions'][$region] = $regions[$region];
          }
        }
      }
    }
  }

  return $output;
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