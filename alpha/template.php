<?php

require_once dirname(__FILE__) . '/includes/alpha.inc';

/**
 * Implements hook_theme().
 */
function alpha_theme($existing, $type, $theme, $path) {
  return array(
    'section' => array(
      'template' => 'section',
      'path' => $path . '/templates',
      'render element' => 'elements',
      'pattern' => 'section__',
      'preprocess functions' => array(
        'template_preprocess', 
        'template_preprocess_section',
        'alpha_preprocess',
        'alpha_preprocess_section',
      ),
      'process functions' => array(
        'template_process', 
        'template_process_section',
        'alpha_process',
        'alpha_process_section'
      ),
    ),
    'zone' => array(
      'template' => 'zone',
      'path' => $path . '/templates',
      'render element' => 'elements',
      'pattern' => 'zone__',
      'preprocess functions' => array(
        'template_preprocess', 
        'template_preprocess_zone',
        'alpha_preprocess',
        'alpha_preprocess_zone',
      ),
      'process functions' => array(
        'template_process', 
        'template_process_zone',
        'alpha_process',
        'alpha_process_zone'
      ),
    ),
  );
}

/**
 * Implements hook_preprocess().
 */
function alpha_preprocess(&$vars, $hook) {
  alpha_invoke('preprocess', $hook, $vars);
}

/**
 * Implements hook_process().
 */
function alpha_process(&$vars, $hook) {
  alpha_invoke('process', $hook, $vars);
}

/**
 * Implements hook_theme_registry_alter().
 */
function alpha_theme_registry_alter(&$registry) {
  alpha_build_registry($registry);
  alpha_register_grids();
  alpha_register_css();
  alpha_register_libraries();
}

/**
 * Implements hook_element_info_alter().
 */
function alpha_element_info_alter(&$elements) {
  if (variable_get('preprocess_css', FALSE) && (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update')) {
    array_unshift($elements['styles']['#pre_render'], 'alpha_grid_css_aggregate');
  }
}

/**
 * Implements hook_css_alter().
 */
function alpha_css_alter(&$css) {
  $settings = alpha_settings();
  
  foreach(array_filter($settings['exclude']) as $item) {
    unset($css[$item]);
  }
}

/**
 * Implements hook_page_alter().
 */
function alpha_page_alter(&$vars) {
  $settings = alpha_settings();
  $regions = $columns = array();
  $reference = &drupal_static('alpha_regions');

  if ($settings['debug']['access']) {
    if ($settings['debug']['block']) {      
      foreach (alpha_regions() as $region => $item) {
        if ($item['enabled']) {          
          $vars[$region]['#sorted'] = FALSE;
          $vars[$region]['alpha_debug_' . $region] = array(       
            '#type' => 'markup',
            '#markup' => '<div class="alpha-debug-block"><h2>' . $item['name'] . '</h2><p>' . t('This is a debugging block') . '</p></div>',
            '#weight' => -999,
          );
        }
      }
    }    
       
    if ($settings['debug']['grid']) {
      $class = 'alpha-grid-toggle alpha-grid-toggle-' . ($settings['debug']['active'] ? 'active' : 'inactive');
      
      if (empty($vars['page_bottom'])) {
        $vars['page_bottom']['#region'] = 'page_bottom';
        $vars['page_bottom']['#theme_wrappers'] = array('region');
      }
      
      if ($settings['responsive']) {
        $vars['page_bottom']['alpha_resize_indicator'] = array(
          '#type' => 'markup',
          '#markup' => '<div class="alpha-resize-indicator"></div>',
        );
      }
      
      $vars['page_bottom']['alpha_grid_toggle'] = array(
        '#type' => 'markup',
        '#markup' => '<a class="' . $class . '" href="#"></a>',
      );
    }
  }
  
  foreach (alpha_regions() as $region => $item) {  
    if ($item['enabled'] && ($item['force'] || !empty($vars[$region]))) {
      $zone = $item['zone'];
      
      $regions[$zone][$region] = isset($vars[$region]) ? $vars[$region] : array();
      $regions[$zone][$region]['#region'] = $region;
      $regions[$zone][$region]['#theme_wrappers'] = array('region');
      $regions[$zone][$region]['#data'] = $item;      
      $regions[$zone][$region]['#weight'] = (int) $item['weight'];
      
      if ($children = element_children($regions[$zone][$region])) {
        $last = count($children) - 1;
        
        foreach ($children as $element) {
          $regions[$zone][$region][$element]['#first'] = $element == $children[0];
          $regions[$zone][$region][$element]['#last'] = $element == $children[$last];
        }
      }
    }
    
    unset($vars[$region]);
  }
  
  foreach (alpha_zones() as $zone => $item) {
    if ($item['enabled'] && ($item['force'] || !empty($regions[$zone]))) {
      $section = $item['section'];
      $columns[$item['columns']] = $item['columns']; 
      
      if (!empty($item['primary']) && !empty($regions[$zone][$item['primary']])) {
        $children = element_children($regions[$zone]);
        $theme = $GLOBALS['theme_key'];
        $primary = &$regions[$zone][$item['primary']];
        $primary['#weight'] = -999;
        $primary['#data']['columns'] = $item['columns'] - $primary['#data']['prefix'] - $primary['#data']['suffix'];
        $primary['#data']['width'] = $item['columns'];
           
        foreach ($children as $region) {
          if (!$regions[$zone][$region]['#data']['primary']) {
            $primary['#data']['columns'] -= $regions[$zone][$region]['#data']['width'];
            $primary['#data']['width'] -= $regions[$zone][$region]['#data']['width'];
    
            if ($primary['#data']['weight'] > $regions[$zone][$region]['#data']['weight']) {
              $primary['#data']['push'] += $regions[$zone][$region]['#data']['width'];              
            }
          }
        }
        
        $reference[$theme][$item['primary']]['columns'] = $primary['#data']['columns'];
        $reference[$theme][$item['primary']]['width'] = $primary['#data']['width'];
        $reference[$theme][$item['primary']]['push'] = $primary['#data']['push'];
        
        foreach ($children as $region) {
          if (!$regions[$zone][$region]['#data']['primary'] && $primary['#data']['weight'] > $regions[$zone][$region]['#data']['weight']) {
            $regions[$zone][$region]['#data']['pull'] = $primary['#data']['width'];            
            $reference[$theme][$region]['pull'] = $primary['#data']['width'];
          }
        }
      }
      
      $vars[$section][$zone] = !empty($regions[$zone]) ? $regions[$zone] : array();
      $vars[$section][$zone]['#theme_wrappers'] = array('zone');      
      $vars[$section][$zone]['#zone'] = $zone;
      $vars[$section][$zone]['#weight'] = (int) $item['weight'];
      $vars[$section][$zone]['#sorted'] = FALSE;
      $vars[$section][$zone]['#data'] = $item;
      $vars[$section][$zone]['#data']['type'] = !empty($item['primary']) && !empty($vars[$section][$zone][$item['primary']]) ? 'dynamic' : 'static';
    }
  }

  foreach (alpha_sections() as $section => $item) {
    if (isset($vars[$section])) {   
      $vars[$section]['#theme_wrappers'] = array('section');
      $vars[$section]['#section'] = $section;
    }
  }
  
  alpha_include_grid($settings['grid'], $columns);
}

/**
 * Implements hook_preprocess_section().
 */
function template_preprocess_section(&$vars) {
  $vars['theme_hook_suggestions'][] = 'section__' . $vars['elements']['#section'];  
  $vars['section'] = $vars['elements']['#section'];  
  $vars['content'] = $vars['elements']['#children'];
  $vars['attributes_array']['id'] = drupal_html_id('section-' . $vars['section']);
  $vars['attributes_array']['class'] = array('section', $vars['attributes_array']['id']);
}

/**
 * Implements hook_preprocess_zone().
 */
function template_preprocess_zone(&$vars) {
  $data = $vars['elements']['#data'];
  $vars['theme_hook_suggestions'] = array('zone__' . $vars['elements']['#zone']);
  $vars['zone'] = $vars['elements']['#zone'];
  $vars['content'] = $vars['elements']['#children'];  
  $vars['columns'] = $data['columns'];
  $vars['wrapper'] = $data['wrapper'];
  $vars['type'] = $data['type'];  
  $vars['content_attributes_array']['id'] = drupal_html_id('zone-' . $vars['zone']);
  $vars['content_attributes_array']['class'] = array('container-' . $vars['columns'], 'zone', $vars['content_attributes_array']['id'], 'zone-' . $vars['type'], 'clearfix');
  
  if (!empty($data['css'])) {
    $extra = array_map('drupal_html_class', explode(' ', $data['css']));
      
    foreach ($extra as $class) {
      $vars['attributes_array']['class'][] = $class;
    }
  }
  
  if ($vars['wrapper']) {
    $vars['attributes_array']['id'] = $vars['content_attributes_array']['id'] . '-wrapper';
    $vars['attributes_array']['class'] = array('zone-wrapper', 'zone-' . $vars['type'] . '-wrapper', $vars['attributes_array']['id']);
    
    if (!empty($data['wrapper_css'])) {
      $extra = array_map('drupal_html_class', explode(' ', $data['wrapper_css']));
        
      foreach ($extra as $class) {
        $vars['attributes_array']['class'][] = $class;
      }
    }
    
    $vars['attributes_array']['class'][] = 'clearfix';
  }
}