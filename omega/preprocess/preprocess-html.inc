<?php

/**
 * @file
 * Container for hook_preprocess_html().
 */

/**
 * Implements hook_preprocess_html().
 * 
 * @see
 *   alpha_preprocess().
 */
function omega_alpha_preprocess_html(&$vars) {
  $settings = alpha_settings();
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
    $grid = alpha_grids($settings['grid']);
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