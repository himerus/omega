<?php

require_once dirname(__FILE__) . '/includes/omega.inc';
require_once dirname(__FILE__) . '/includes/omega.theme.inc';

/**
 * Implements hook_alpha_regions_alter().
 */
function omega_alpha_regions_alter(&$regions, $theme) {
  foreach ($regions as $region => &$item) {
    $item['equal_height'] = alpha_region_get_setting('equal_height', $region, FALSE, $theme);
  }
}

/**
 * Implements hook_alpha_zones_alter().
 */
function omega_alpha_zones_alter(&$zones, $theme) {
  foreach ($zones as $zone => &$item) {
    $item['equal_height'] = alpha_zone_get_setting('equal_height', $zone, FALSE, $theme);
  }
}

/**
 * Implements hook_preprocess_block().
 */
function omega_preprocess_block(&$vars) {
  $vars['attributes_array']['class'] = &$vars['classes_array'];  
  
  // Adding a class to the title attributes
  $vars['title_attributes_array']['class'][] = 'block-title';

  // Add odd/even zebra classes into the array of classes
  $vars['attributes_array']['class'][] = $vars['block_zebra'];
  
  if(empty($vars['block']->subject) && (string) $vars['block']->subject != '0') {
    // Add a class to provide CSS for blocks without titles.
    $vars['attributes_array']['class'][] = 'block-without-title';
  }
  
  if ($vars['block']->module != 'alpha-debug' && isset($vars['block']->region)) {
    if (alpha_library_active('omega_equalheights') && $region = alpha_regions($vars['block']->region)) {
      if ($region['equal_height']) {
        $vars['attributes_array']['class'][] = 'equal-height-element';
      }
    }
  }
}

/**
 * Implements hook_preprocess_comment().
 */
function omega_preprocess_comment(&$vars) {
  // Prepare the arrays to handle the classes and ids for the node container.
  $vars['attributes_array']['class'] = &$vars['classes_array'];
  $vars['attributes_array']['class'][] = 'clearfix';
  
  $vars['datetime'] = format_date($vars['comment']->created, 'custom', 'c');
  $vars['unpublished'] = '';
  
  if ($vars['status'] == 'comment-unpublished') {
    $vars['unpublished'] = '<div class="unpublished">' . t('Unpublished') . '</div>';
  }
}

/**
 * Implements hook_preprocess_html().
 */
function omega_preprocess_html(&$vars) {
  $vars['rdf'] = new stdClass;  
  
  if (module_exists('rdf')) {
    $vars['doctype'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML+RDFa 1.1//EN">' . "\n";
    $vars['rdf']->version = ' version="HTML+RDFa 1.1"';
    $vars['rdf']->namespaces = $vars['rdf_namespaces'];
    $vars['rdf']->profile = ' profile="' . $vars['grddl_profile'] . '"';
  } 
  else {
    $vars['doctype'] = '<!DOCTYPE html>' . "\n";
    $vars['rdf']->version = '';
    $vars['rdf']->namespaces = '';
    $vars['rdf']->profile = '';
  }
  
  if (alpha_library_active('omega_mediaqueries')) {
    $grid = alpha_grids(alpha_settings('grid'));
    $layouts = array();
    
    foreach ($grid['layouts'] as $name => $layout) {
      if ($layout['enabled']) {
        $layouts[$name] = $layout['media'];
      }
    }
    
    drupal_add_js(array('omega' => array(      
      'layouts' => array(
        'primary' => $grid['primary'],
        'order' => array_keys($layouts), 
        'queries' => $layouts,
      ),        
    )), 'setting');
  }
}

/**
 * Implements hook_preprocess_node().
 */
function omega_preprocess_node(&$vars) {
  // Prepare the arrays to handle the classes and ids for the node container.
  $vars['attributes_array']['id'] = drupal_html_id('node-' . $vars['type'] . '-' . $vars['nid']);
  
  // Add a class to allow styling based on publish status.
  if ($vars['status']) {
    $vars['attributes_array']['class'][] = 'node-published';
  }
  
  // Add a class to allow styling based on promotion.
  if (!$vars['promote']) {
    $vars['attributes_array']['class'][] = 'node-not-promoted';
  }
  
  // Add a class to allow styling based on sticky status.
  if (!$vars['sticky']) {
    $vars['attributes_array']['class'][] = 'node-not-sticky';
  }
  
  // Add a class to allow styling of nodes being viewed by the author of the node in question.
  if ($vars['uid'] == $vars['user']->uid) {
    $vars['attributes_array']['class'][] = 'self-posted';
  }
  
  // Add a class to allow styling based on the node author.
  $vars['attributes_array']['class'][] = drupal_html_class('author-' . $vars['node']->name);
  
  // Add a class to allow styling for zebra striping.
  $vars['attributes_array']['class'][] = drupal_html_class($vars['zebra']);
  
  // Add a class to make the node container self clearing.
  $vars['attributes_array']['class'][] = 'clearfix';  
  $vars['content_attributes_array']['class'] = array('content', 'clearfix');
  
  // Adding a class to the title attributes
  $vars['title_attributes_array']['class'] = 'node-title';
}

/**
 * Implements hook_preprocess_region().
 */
function omega_preprocess_region(&$vars) {
  if (isset($vars['elements']['#data'])) {
    $data = $vars['elements']['#data'];    
    
    if (alpha_library_active('omega_equalheights')) {
      if ($data['equal_height']) {      
        $vars['content_attributes_array']['class'][] = 'equal-height-container';
      }

      if ($data['zone'] && $zone = alpha_zones($data['zone'])) {      
        if ($zone['equal_height']) {
          $vars['attributes_array']['class'][] = 'equal-height-element';
        }
      }
    }
  }
}

/**
 * Implements hook_preprocess_zone().
 */
function omega_preprocess_zone(&$vars) {
  $data = $vars['elements']['#data'];
  
  if (alpha_library_active('omega_equalheights') && $data['equal_height']) {
    $vars['content_attributes_array']['class'][] = 'equal-height-container';
  }
}

/**
 * Implements hook_process_region().
 */
function omega_process_region(&$vars) {
  if (in_array($vars['elements']['#region'], array('content', 'menu', 'branding'))) {
    switch ($vars['elements']['#region']) {
      case 'content':
        $vars['title_prefix'] = $GLOBALS['page']['title_prefix'];
        $vars['title'] = $GLOBALS['page']['title'];
        $vars['title_suffix'] = $GLOBALS['page']['title_suffix'];
        $vars['tabs'] = $GLOBALS['page']['tabs'];
        $vars['action_links'] = $GLOBALS['page']['action_links'];      
        $vars['title_hidden'] = $GLOBALS['page']['title_hidden'];
        break;
      
      case 'menu':
        $vars['main_menu'] = $GLOBALS['page']['main_menu'];
        $vars['secondary_menu'] = $GLOBALS['page']['secondary_menu'];
        break;
      
      case 'branding':    
        $vars['site_name'] = $GLOBALS['page']['site_name'];
        $vars['linked_site_name'] = l($vars['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home'), 'html' => TRUE));
        $vars['site_slogan'] = $GLOBALS['page']['site_slogan'];      
        $vars['site_name_hidden'] = $GLOBALS['page']['site_name_hidden'];
        $vars['site_slogan_hidden'] = $GLOBALS['page']['site_slogan_hidden'];
        $vars['logo'] = $GLOBALS['page']['logo'];
        $vars['logo_img'] = $vars['logo'] ? '<img src="' . $vars['logo'] . '" alt="' . $vars['site_name'] . '" id="logo" />' : '';
        $vars['linked_logo_img'] = $vars['logo'] ? l($vars['logo_img'], '<front>', array('rel' => 'home', 'title' => t($vars['site_name']), 'html' => TRUE)) : '';    
        break;      
    }
  }
}

/**
 * Implements hook_process_zone().
 */
function omega_process_zone(&$vars) {
  if ($vars['elements']['#zone'] == 'content') {
    $vars['messages'] = $GLOBALS['page']['messages'];
    $vars['breadcrumb'] = $GLOBALS['page']['breadcrumb'];
  }
}