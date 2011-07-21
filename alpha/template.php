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
  $vars['attributes_array']['class'] = $vars['classes_array'];
  
  if (isset($vars['elements']['#grid'])) {
    foreach (array('prefix', 'suffix', 'push', 'pull') as $quality) {
      if (!empty($vars['elements']['#grid'][$quality])) {
        array_unshift($vars['attributes_array']['class'], $quality . '-' . $vars['elements']['#grid'][$quality]);
      }
    }
    
    array_unshift($vars['attributes_array']['class'], 'grid-' . $vars['elements']['#grid']['columns']);
  }
  
  if (!empty($vars['elements']['#custom_css'])) {
    foreach (array_map('drupal_html_class', explode(' ', $vars['elements']['#custom_css'])) as $class) {
      $vars['attributes_array']['class'][] = $class;
    }
  }

  if (!empty($vars['elements']['#custom_content_css'])) {
    foreach (array_map('drupal_html_class', explode(' ', $vars['elements']['#custom_content_css'])) as $class) {
      $vars['content_attributes_array']['class'][] = $class;
    }
  }
  
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
  $theme = isset($theme) ? $theme : $GLOBALS['theme_key'];
  
  alpha_clear_cache($theme);
  alpha_build_registry($registry, $theme);
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
  if ($excluded = alpha_settings('excluded')) {  
    foreach(array_filter($excluded) as $item) {
      unset($css[$item]);
    }
  }
}

/**
 * Implements hook_page_alter().
 */
function alpha_page_alter(&$vars) {
  $regions = array();
  $columns = array();
  $debug = alpha_settings('debug');

  // If no module has taken care of the main content, add it to the page now.
  // This allows the site to still be usable even if no modules that
  // control page regions (for example, the Block module) are enabled.
  if (!drupal_static('system_main_content_added', FALSE)) {
    $vars['content']['system_main'] = drupal_set_page_content();
  }
  
  if ($debug['access']) {      
    if ($debug['grid'] || $debug['block']) {
      if (empty($vars['page_bottom'])) {
        $vars['page_bottom']['#region'] = 'page_bottom';
        $vars['page_bottom']['#theme_wrappers'] = array('region');
      }
      
      if (alpha_settings('responsive')) {        
        $vars['page_bottom']['alpha_resize_indicator'] = array(
          '#type' => 'markup',
          '#markup' => '<div class="alpha-resize-indicator"></div>',
        );
      }
      
      if ($debug['grid']) {
        $vars['page_bottom']['alpha_grid_toggle'] = array(
          '#type' => 'markup',
          '#markup' => '<a class="alpha-grid-toggle" href="#"></a>',
        );
      }
      
      if ($debug['block']) {
        $vars['page_bottom']['alpha_block_toggle'] = array(
          '#type' => 'markup',
          '#markup' => '<a class="alpha-block-toggle" href="#"></a>',
        );
      }
    }
    
    if ($debug['block']) {      
      foreach (alpha_regions() as $region => $item) {
        if ($item['enabled']) {  
          if (empty($vars[$region])) {
            $vars[$region]['#region'] = $region;
            $vars[$region]['#theme_wrappers'] = array('region');
          }
          
          $vars[$region]['#sorted'] = FALSE;
          $vars[$region]['alpha_debug_' . $region] = array(       
            '#type' => 'markup',
            '#markup' => '<div class="alpha-debug-block"><h2>' . $item['name'] . '</h2><p>' . t('This is a debugging block') . '</p></div>',
            '#weight' => -999,
          );
        }
      }
    }
  }
  
  foreach (alpha_regions() as $region => $item) {
    if ($item['enabled'] && ($item['force'] || !empty($vars[$region]))) {
      $regions[$item['zone']][$region] = !empty($vars[$region]) ? $vars[$region] : array();
      $regions[$item['zone']][$region]['#weight'] = (int) $item['weight'];
      $regions[$item['zone']][$region]['#position'] = $item['position'];
      $regions[$item['zone']][$region]['#data'] = $item;
      $regions[$item['zone']][$region]['#custom_css'] = $item['css'];
      $regions[$item['zone']][$region]['#grid'] = array(
        'prefix' => $item['prefix'],
        'suffix' => $item['suffix'],
        'push' => $item['push'],
        'pull' => $item['pull'],
        'columns' => $item['columns'],
      );
      
      if (empty($vars[$region])) {
        $regions[$item['zone']][$region]['#region'] = $region;
        $regions[$item['zone']][$region]['#theme_wrappers'] = array('region');
      }
    }
    else if (!empty($vars[$region])) {
      $vars['#excluded'][$region] = !empty($vars[$region]) ? $vars[$region] : array();
      $vars['#excluded'][$region]['#weight'] = (int) $item['weight'];
      $vars['#excluded'][$region]['#data'] = $item;
      $vars['#excluded'][$region]['#custom_css'] = $item['css'];
      $vars['#excluded'][$region]['#grid'] = array(
        'prefix' => $item['prefix'],
        'suffix' => $item['suffix'],
        'push' => $item['push'],
        'pull' => $item['pull'],
        'columns' => $item['columns'],
      );
    }
    
    unset($vars[$region]);
  }
  
  foreach (alpha_zones() as $zone => $item) {
    if ($item['enabled'] && ($item['force'] || !empty($regions[$zone]))) {
      $columns[$item['columns']] = $item['columns'];
      
      if (isset($item['primary']) && isset($regions[$zone][$item['primary']])) {
        alpha_calculate_primary($regions[$zone], $item['primary'], $item['columns']);
      }
      
      if ($item['order']) {
        alpha_calculate_position($regions[$zone]);
      }
      
      $vars[$item['section']][$zone] = !empty($regions[$zone]) ? $regions[$zone] : array();
      $vars[$item['section']][$zone]['#theme_wrappers'] = array('zone');      
      $vars[$item['section']][$zone]['#zone'] = $zone;
      $vars[$item['section']][$zone]['#weight'] = (int) $item['weight'];
      $vars[$item['section']][$zone]['#data'] = $item;  
      $vars[$item['section']][$zone]['#data']['dynamic'] = isset($item['primary']) && isset($vars[$item['section']][$zone][$item['primary']]);
      $vars[$item['section']][$zone]['#custom_css'] = $item['wrapper_css'];
      $vars[$item['section']][$zone]['#custom_content_css'] = $item['css'];
    }
  }

  foreach (alpha_sections() as $section => $item) {
    if (isset($vars[$section])) {   
      $vars[$section]['#theme_wrappers'] = array('section');
      $vars[$section]['#section'] = $section;
    }
  }
  
  foreach ($columns as $count) {
    alpha_grid_include(alpha_settings('grid'), $count, alpha_settings('responsive'));
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
  $vars['theme_hook_suggestions'] = array('zone__' . $vars['elements']['#zone']);
  $vars['zone'] = $vars['elements']['#zone'];
  $vars['content'] = $vars['elements']['#children'];
  $vars['type'] = $vars['elements']['#data']['dynamic'] ? 'dynamic' : 'static';
  $vars['wrapper'] = $vars['elements']['#data']['wrapper'];
  $vars['columns'] = $vars['elements']['#data']['columns'];
  
  $vars['content_attributes_array']['id'] = drupal_html_id('zone-' . $vars['zone']);
  $vars['content_attributes_array']['class'] = array('container-' . $vars['columns'], 'zone', $vars['content_attributes_array']['id'], 'zone-' . $vars['type'], 'clearfix');
  
  if ($vars['wrapper']) {
    $vars['attributes_array']['id'] = drupal_html_id($vars['content_attributes_array']['id'] . '-wrapper');
    $vars['attributes_array']['class'] = array('zone-wrapper', 'zone-' . $vars['type'] . '-wrapper', $vars['attributes_array']['id'], 'clearfix');
  }
}

/**
 * Implements hook_preprocess_block().
 */
function alpha_preprocess_block(&$vars) {
  $vars['content_attributes_array']['class'] = array('content', 'clearfix');
  $vars['attributes_array']['id'] = $vars['block_html_id'];
  $vars['attributes_array']['class'][] = drupal_html_class('block-' . $vars['block']->delta);  
  $vars['attributes_array']['class'][] = $vars['block_html_id'];
}

/**
 * Implements hook_preprocess_html().
 */
function alpha_preprocess_html(&$vars) { 
  $settings = alpha_settings();
  $css = alpha_css();
  $libraries = alpha_libraries();
  
  $vars['attributes_array']['class'] = &$vars['classes_array'];
  
  foreach (array('two-sidebars', 'one-sidebar sidebar-first', 'one-sidebar sidebar-second', 'no-sidebars') as $exclude) {
    if ($index = array_search($exclude, $vars['attributes_array']['class'])) {      
      unset($vars['attributes_array']['class'][$index]);
    }
  }
  
  // Add a CSS class based on the current page context.
  if (!drupal_is_front_page()) {
    $context = explode('/', drupal_get_path_alias());
    $context = reset($context);
    
    if (!empty($context)) {
      $vars['attributes_array']['class'][] = drupal_html_class('context-' . $context);
    }
  }
  
  if (($settings['debug']['grid'] || $settings['debug']['block']) && $settings['debug']['access']) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/alpha-debug.css', array('group' => CSS_THEME, 'weight' => -5));   
    drupal_add_js(drupal_get_path('theme', 'alpha') . '/js/alpha-debug.js', array('group' => JS_THEME, 'weight' => -5));
    
    if ($settings['debug']['grid'] && $settings['debug']['grid_active']) {
      $vars['attributes_array']['class'][] = 'alpha-grid-debug';
    }
    
    if ($settings['debug']['block'] && $settings['debug']['block_active']) {
      $vars['attributes_array']['class'][] = 'alpha-region-debug';
    }
  }

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
 * Implements hook_preprocess_page().
 */
function alpha_preprocess_page(&$vars) {
  $GLOBALS['page'] = &$vars;
  $toggle = alpha_settings('toggle');
  $hidden = alpha_settings('hidden');
  
  $vars['feed_icons'] = $toggle['feed_icons'] ? $vars['feed_icons'] : NULL;
  $vars['tabs'] = $toggle['tabs'] ? $vars['tabs'] : NULL;
  $vars['action_links'] = $toggle['action_links'] ? $vars['action_links'] : NULL;
  $vars['show_messages'] = $toggle['messages'] ? $vars['show_messages'] : FALSE;  
  $vars['site_name_hidden'] = $hidden['site_name'];
  $vars['site_slogan_hidden'] = $hidden['site_slogan'];
  $vars['title_hidden'] = $hidden['title'];   
  $vars['attributes_array']['id'] = 'page';
  $vars['attributes_array']['class'] = array('clearfix');
}

/**
 * Implements hook_preprocess_region().
 */
function alpha_preprocess_region(&$vars) {
  $vars['attributes_array']['id'] = drupal_html_id('region-' . $vars['region']);
  $vars['content_attributes_array']['class'][] = 'region-inner';
  $vars['content_attributes_array']['class'][] = $vars['attributes_array']['id'] . '-inner';
}

/**
 * Implements hook_process_page().
 */
function alpha_process_page(&$vars) {
  $toggle = alpha_settings('toggle');

  $vars['title'] = $toggle['page_title'] ? $vars['title'] : NULL;
  $vars['breadcrumb'] = $toggle['breadcrumb'] ? $vars['breadcrumb'] : NULL;
}