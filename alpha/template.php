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
 * Implements hook_css_alter().
 */
function alpha_css_alter(&$css) {
  $settings = alpha_settings();
  
  foreach(array_filter($settings['exclude']) as $item) {
    unset($css[$item]);
  }
}

/**
 * Implements hook_init().
 */
function alpha_init() {
  $settings = alpha_settings();
  $css = alpha_css();
  $libraries = alpha_libraries();
  
  foreach (array_filter($settings['libraries']) as $item) {
    if (isset($libraries[$item])) {
      if (!empty($libraries[$item]['js'])) {
        foreach ($libraries[$item]['js'] as $include) {
          drupal_add_js($include['path'], $include['options']);
        }
      }

      if (!empty($libraries[$item]['css'])) {
        foreach ($libraries[$item]['css'] as $include) {
          drupal_add_css($include['path'], $include['options']);
        }
      }
    }
  }

  foreach (array_filter($settings['css']) as $item) {
    if (isset($css[$item])) {
      drupal_add_css($css[$item]['path'], $css[$item]['options']);
    }
  }

  if($settings['responsive'] && $settings['viewport']['enabled']) {
    $meta = array(
  	  '#tag' => 'meta',
      '#attributes' => array(
        'name' => 'viewport',
        'content' => 'width=device-width, initial-scale=' . $settings['viewport']['initial'] . ', maximum-scale=' . $settings['viewport']['max'] . ', minimum-scale=' . $settings['viewport']['min'] . ', user-scalable=' . ($settings['viewport']['user'] ? 'yes' : 'no'),
      ),
    );

    drupal_add_html_head($meta, 'alpha-viewport');
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
          $block = new stdClass();
          $block->delta = 'debug-' . $region;
          $block->region = $region;
          $block->module = 'alpha-debug';
          $block->subject = $item['name'];
          
          $vars[$region]['#sorted'] = FALSE;
          $vars[$region]['alpha_debug_' . $region] = array(       
            '#block' => $block,
            '#weight' => -999,
            '#markup' => t('This is a debugging block'),
            '#theme_wrappers' => array('block'),
          );
        }
      }
    }    
       
    if ($settings['responsive'] && $settings['debug']['grid']) {
      if (empty($vars['page_bottom'])) {
        $vars['page_bottom']['#region'] = 'page_bottom';
        $vars['page_bottom']['#theme_wrappers'] = array('region');
      }
        
      $vars['page_bottom']['alpha_resize_indicator'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="alpha-resize-indicator"></div>',
      );
    }
  }
  
  foreach (alpha_regions() as $region => $item) {  
    if ($item['enabled'] && ($item['force'] || isset($vars[$region]))) {
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
    if ($item['enabled'] && ($item['force'] || isset($regions[$zone]))) {
      $section = $item['section'];
      $columns[$item['columns']] = $item['columns']; 
      
      if (!empty($item['primary']) && isset($regions[$zone][$item['primary']])) {
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
      
      $vars[$section][$zone] = isset($regions[$zone]) ? $regions[$zone] : array();
      $vars[$section][$zone]['#theme_wrappers'] = array('zone');      
      $vars[$section][$zone]['#zone'] = $zone;
      $vars[$section][$zone]['#weight'] = (int) $item['weight'];
      $vars[$section][$zone]['#sorted'] = FALSE;
      $vars[$section][$zone]['#data'] = $item;
      $vars[$section][$zone]['#data']['type'] = !empty($item['primary']) && isset($vars[$section][$zone][$item['primary']]) ? 'dynamic' : 'static';
    }
  }

  foreach (alpha_sections() as $section => $item) {
    if (isset($vars[$section])) {   
      $vars[$section]['#theme_wrappers'] = array('section');
      $vars[$section]['#section'] = $section;
    }
  }
  
  alpha_include_grid($settings['grid'], $columns);
  
  if ($settings['debug']['grid'] && $settings['debug']['access']) {
    alpha_debug_grid($settings['grid'], $columns);
  }
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
  $vars['attributes_array']['id'] = drupal_html_id('zone-' . $vars['zone']);
  $vars['attributes_array']['class'] = array('zone', $vars['attributes_array']['id'], 'zone-' . $vars['type'], 'container-' . $vars['columns'], 'clearfix');
  
  if (!empty($data['css'])) {
    $extra = array_map('drupal_html_class', explode(' ', $data['css']));
      
    foreach ($extra as $class) {
      $vars['attributes_array']['class'][] = $class;
    }
  }
  
  if ($vars['wrapper']) {
    $vars['wrapper_attributes_array']['id'] = $vars['attributes_array']['id'] . '-wrapper';
    $vars['wrapper_attributes_array']['class'] = array('zone-wrapper', 'zone-' . $vars['type'] . '-wrapper', $vars['wrapper_attributes_array']['id']);
    
    if (!empty($data['wrapper_css'])) {
      $extra = array_map('drupal_html_class', explode(' ', $data['wrapper_css']));
        
      foreach ($extra as $class) {
        $vars['wrapper_attributes_array']['class'][] = $class;
      }
    }
    
    $vars['wrapper_attributes_array']['class'][] = 'clearfix';
  }
}

/**
 * Implements hook_process_zone().
 */
function template_process_zone(&$vars) {
  $vars['wrapper_attributes'] = isset($vars['wrapper_attributes_array']) ? drupal_attributes($vars['wrapper_attributes_array']) : '';
}