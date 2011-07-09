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

  // If no module has taken care of the main content, add it to the page now.
  // This allows the site to still be usable even if no modules that
  // control page regions (for example, the Block module) are enabled.
  if (!drupal_static('system_main_content_added', FALSE)) {
    $vars['content']['system_main'] = drupal_set_page_content();
  }
  
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
       
    if ($settings['debug']['grid'] || $settings['debug']['block']) {
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
      
      if ($settings['debug']['grid']) {
        $vars['page_bottom']['alpha_grid_toggle'] = array(
          '#type' => 'markup',
          '#markup' => '<a class="alpha-grid-toggle" href="#"></a>',
        );
      }
      
      if ($settings['debug']['block']) {
        $vars['page_bottom']['alpha_block_toggle'] = array(
          '#type' => 'markup',
          '#markup' => '<a class="alpha-block-toggle" href="#"></a>',
        );
      }
    }
  }
  
  foreach (alpha_regions() as $region => $item) {
    alpha_children_first_last($vars[$region]);
    
    if ($item['enabled'] && ($item['force'] || !empty($vars[$region]))) {
      $zone = $item['zone'];
      
      $regions[$zone]['#sorted'] = FALSE;
      $regions[$zone][$region] = $vars[$region];
      $regions[$zone][$region]['#region'] = $region;
      $regions[$zone][$region]['#theme_wrappers'] = array('region');
      $regions[$zone][$region]['#data'] = $item;      
      $regions[$zone][$region]['#weight'] = (int) $item['weight'];
    }
    else if (!empty($vars[$region])) {
      $vars['#excluded'][$region] = $vars[$region];
      $vars['#excluded'][$region]['#weight'] = (int) $item['weight'];
      $vars['#excluded'][$region]['#data'] = $item;
    }
    
    unset($vars[$region]);
  }
  
  foreach (alpha_zones() as $zone => $item) {
    if ($item['enabled'] && ($item['force'] || !empty($regions[$zone]))) {
      $columns[$item['columns']] = $item['columns'];
      $section = $item['section'];
      
      if (isset($item['primary']) && isset($regions[$zone][$item['primary']])) {
        alpha_calculate_primary($regions[$zone], $item['primary'], $item['columns']);
      }
      
      if ($item['order']) {
        alpha_calculate_position($regions[$zone]);
      }
      
      $vars[$section][$zone] = !empty($regions[$zone]) ? $regions[$zone] : array();      
      $vars[$section][$zone]['#theme_wrappers'] = array('zone');      
      $vars[$section][$zone]['#zone'] = $zone;
      $vars[$section][$zone]['#weight'] = (int) $item['weight'];
      $vars[$section][$zone]['#data'] = $item;
      $vars[$section][$zone]['#data']['type'] = isset($item['primary']) && isset($vars[$section][$zone][$item['primary']]) ? 'dynamic' : 'static';
    }
  }

  foreach (alpha_sections() as $section => $item) {
    if (isset($vars[$section])) {   
      $vars[$section]['#theme_wrappers'] = array('section');
      $vars[$section]['#section'] = $section;
      $vars[$section]['#sorted'] = FALSE;
    }
  }
  
  alpha_grid_include($settings['grid'], $columns);
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
      $vars['content_attributes_array']['class'][] = $class;
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