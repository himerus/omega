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
 * Implements hook_block_list_alter().
 */
function alpha_block_list_alter(&$list) {
  $debug = alpha_debug_settings($GLOBALS['theme_key']);  
  $regions = alpha_regions($GLOBALS['theme_key']);
  $zones = alpha_zones($GLOBALS['theme_key']);
  
  if ($debug['block'] && $debug['access']) {
    foreach ($regions as $region => $item) {
      if ($item['enabled'] && $zones[$item['zone']]['enabled']) {
        $block = new stdClass();
        $block->delta = 'debug-' . $region;
        $block->region = $region;
        $block->module = 'alpha-debug';
        $block->title = $item['name'];
        $block->cache = DRUPAL_NO_CACHE;
        
        $list['alpha-debug-' . $region] = $block;
      }
    }
  }
}

/**
 * Implements hook_block_view_alter().
 */
function alpha_block_view_alter(&$data, $block) {
  if ($block->module == 'alpha-debug') {
    $data['content'] = array(
      '#weight' => -999,
      '#markup' => t('This is a debugging block.'),
    );
  }
}

/**
 * Implements hook_menu_contextual_links_alter().
 */
function alpha_menu_contextual_links_alter(&$links, $router_item, $root_path) {
  $block = array_pop($router_item['map']);
  $module = array_pop($router_item['map']);

  if ($module == 'alpha-debug') {
    $links = array();

    $regions = alpha_regions($GLOBALS['theme_key']);
    $region = substr($block, 6);

    if (!empty($GLOBALS['delta']) && module_exists('delta_ui')) {
      $links['edit-delta'] = array(
        'title' => t('Edit Delta template'),
        'href' => 'admin/appearance/delta/layouts/edit/' . $GLOBALS['delta']->machine_name,
        'localized_options' => array(),
      );
      
      $links['configure-delta'] = array(
        'title' => t('Configure Delta template'),
        'href' => 'admin/appearance/delta/layouts/configure/' . $GLOBALS['delta']->machine_name,
        'localized_options' => array(),
      );
      
      $path = 'admin/appearance/delta/layouts/configure/' . $GLOBALS['delta']->machine_name;
    }
    else {
      $links['theme-settings'] = array(
        'title' => t('Edit theme settings'),
        'href' => 'admin/appearance/settings/' . $GLOBALS['theme_key'],
      	'localized_options' => array(),
      );
      
      $path = 'admin/appearance/settings/' . $GLOBALS['theme_key'];
    }
  }
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

  if (!empty($settings['exclude'])) {
    foreach(array_keys(array_filter($settings['exclude'])) as $item) {
      unset($css[$item]);
    }
  }

  // This is the media query fix for Internet Explorer
  // Only need special processing if the responsive grid is enabled.
  if ($settings['responsive']['enabled']) {
    $grid = alpha_grids($GLOBALS['theme_key'], $settings['grid']);
    $name = str_replace('_', '-', $settings['grid']);

    $responsive = alpha_css($GLOBALS['theme_key'], TRUE);
    
    $css['ie-normal-grid-defaults'] = $css[$grid['path'] . '/normal/' . $name . '-normal-grid.css'];
    $css['ie-normal-grid-defaults']['media'] = 'all';
    $css['ie-normal-grid-defaults']['basename'] = 'ie-normal-grid-defaults';
    $css['ie-normal-grid-defaults']['browsers'] = array('IE' => '(lt IE 9)&(!IEMobile)', '!IE' => FALSE);
    
    foreach($grid['columns'] as $columns => $path) {
      $path = $path . '/normal/' . $name . '-normal-' . $columns . '.css';
      
      // Attempt to push back in normal for IE < 9 (which all don't support media queries)
      // this is a must have or all IE browsers < 9 will be given the mobile version
      // instead, we'll just revert to giving them the default 960gs
      if (isset($css[$path])) {
        $css['ie-normal-grid-defaults-' . $columns] = $css[$path];
        $css['ie-normal-grid-defaults-' . $columns]['media'] = 'all';
        $css['ie-normal-grid-defaults-' . $columns]['basename'] = 'ie-normal-grid-defaults-' . $columns;
        $css['ie-normal-grid-defaults-' . $columns]['browsers'] = array('IE' => '(lt IE 9)&(!IEMobile)', '!IE' => FALSE);
      }
    }
    
    // Add all enabled optional stylesheets for this responsive layout via drupal_add_css().
    foreach (array_keys(array_filter($settings['responsive']['normal']['css'])) as $item) {            
      if (isset($responsive[$item], $css[$responsive[$item]['path']])) {
        $css['ie-normal-responsive-' . $css[$responsive[$item]['path']]] = $css[$responsive[$item]['path']];
        $css['ie-normal-responsive-' . $css[$responsive[$item]['path']]]['media'] = 'all';
        $css['ie-normal-responsive-' . $css[$responsive[$item]['path']]]['basename'] = 'ie-normal-responsive-' . $css[$responsive[$item]['path']];
        $css['ie-normal-responsive-' . $css[$responsive[$item]['path']]]['browsers'] = array('IE' => '(lt IE 9)&(!IEMobile)', '!IE' => FALSE);
      }
    }
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
 * Implements hook_process_zone().
 */
function template_process_zone(&$vars) {
  $vars['wrapper_attributes'] = isset($vars['wrapper_attributes_array']) ? drupal_attributes($vars['wrapper_attributes_array']) : '';
}

/**
 * Implements hook_delta_exclude()
 */
function alpha_delta_exclude(&$settings) {
  return array('alpha_debug_block_toggle', 'alpha_debug_grid_toggle', 'alpha_debug_grid_roles');
}