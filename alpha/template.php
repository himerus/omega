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
  alpha_build_registry($GLOBALS['theme_key'], $registry);
  alpha_register_grids($GLOBALS['theme_key']);
  alpha_register_css($GLOBALS['theme_key']);
  alpha_register_libraries($GLOBALS['theme_key']);
}

/**
 * Implements hook_css_alter().
 */
function alpha_css_alter(&$css) {
  $settings = alpha_settings($GLOBALS['theme_key']);
  
  foreach(array_filter($settings['exclude']) as $item) {
    unset($css[$item]);
  }
}

/**
 * Implements hook_css_alter().
 */
function alpha_library_alter(&$css) {
  $settings = alpha_settings($GLOBALS['theme_key']);
  
  foreach(array_filter($settings['exclude']) as $item) {
    unset($css[$item]);
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
  $settings = $vars['elements']['#page']['#alpha'];
  $data = $vars['elements']['#data'];
  
  $vars['theme_hook_suggestions'][] = 'zone__' . $vars['elements']['#zone'];
  
  alpha_include_grid($settings['grid'], $data['columns']);
  
  if($settings['debug']['grid'] && alpha_debug_access($vars['user'], $settings['debug']['roles'])) {
    alpha_debug_grid($settings, $data['columns']);
  } 
  
  $vars['zone'] = $vars['elements']['#zone'];
  $vars['content'] = $vars['elements']['#children'];  
  $vars['columns'] = $data['columns'];
  $vars['wrapper'] = $data['wrapper'];
  $vars['type'] = $data['type'];
  
  $vars['attributes_array']['id'] = drupal_html_id('zone-' . $vars['zone']);
  $vars['attributes_array']['class'] = array('zone', $vars['attributes_array']['id'], 'zone-' . $data['type'], 'container-' . $vars['columns'], 'clearfix');
  
  if (!empty($data['css'])) {
    $extra = array_map('drupal_html_class', explode(' ', $data['css']));
      
    foreach ($extra as $class) {
      $vars['attributes_array']['class'][] = $class;
    }
  }
  
  if ($vars['wrapper']) {
    $vars['wrapper_attributes_array']['id'] = $vars['attributes_array']['id'] . '-wrapper';
    $vars['wrapper_attributes_array']['class'] = array('zone-wrapper', 'zone-' . $data['type'] . '-wrapper', $vars['wrapper_attributes_array']['id']);
    
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
 * Implements hook_page_alter().
 */
function alpha_page_alter(&$vars) {
  $settings = alpha_settings($GLOBALS['theme_key']);
  
  if ($settings['debug']['access']) {    
    if ($settings['responsive'] && $settings['debug']['grid']) {
      if (empty($vars['page_bottom'])) {
        $vars['page_bottom']['#region'] = 'page_bottom';
        $vars['page_bottom']['#theme_wrappers'] = array('region');
      }
        
      $vars['page_bottom']['alpha-resize-indicator'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="alpha-resize-indicator"></div>',
      );
    }

    if ($settings['debug']['block']) {
      $regions = alpha_regions($GLOBALS['theme_key']);
      $zones = alpha_zones($GLOBALS['theme_key']);
      
      foreach ($regions as $region => $item) {        
        if ($item['enabled'] && $zones[$item['zone']]['enabled']) {
          if (empty($vars[$region])) {
            $vars[$region]['#region'] = $region;
            $vars[$region]['#theme_wrappers'] = array('region');
          }                
                
          $block = new stdClass();
          $block->delta = 'debug-' . $region;
          $block->region = $region;
          $block->module = 'alpha-debug';
          $block->subject = $item['name'];
          
          $vars[$region]['#sorted'] = FALSE;
          $vars[$region]['alpha-debug-' . $region] = array(       
            '#block' => $block,
            '#weight' => -999,
            '#markup' => t('This is a debugging block'),
            '#theme_wrappers' => array('block'),
          );
        }
      }
      
      $zones = &drupal_static('alpha_zones');
      $zones[$GLOBALS['theme_key']] = NULL;
      
      $regions = &drupal_static('alpha_regions');
      $regions[$GLOBALS['theme_key']] = NULL;
    }
  }
}

/**
 * Implements hook_process_zone().
 */
function template_process_zone(&$vars) {
  $vars['wrapper_attributes'] = isset($vars['wrapper_attributes_array']) ? drupal_attributes($vars['wrapper_attributes_array']) : '';
}