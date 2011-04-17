<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_html(&$vars) {
  global $theme_key;
  
  $settings = alpha_settings($theme_key);
  $access = alpha_debug_access($vars['user'], $settings['debug']['roles']);
  $css = alpha_css($theme_key);

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

  foreach ($settings['css'] as $item => $enabled) {
    if (isset($css[$item]) && $enabled) {
      $weight = $css[$item]['weight'] >= 0 ? $css[$item]['weight'] : $css[$item]['weight'] - 3;
      
      drupal_add_css($css[$item]['path'], array('media' => $css[$item]['media'], 'weight' => $weight));
    }
  }
  
  if(($settings['debug']['grid'] || $settings['debug']['block']) && $access) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/debug.css', array('weight' => 1));

    if ($settings['debug']['grid']) {
      $vars['classes_array'][] = 'alpha-grid-debug';
    }
    
    if ($settings['debug']['block']) {
      $vars['classes_array'][] = 'alpha-region-debug';
    }
  }

  if($settings['responsive']['enabled'] && $settings['viewport']['enabled']) {
    $meta = array(
  	  '#tag' => 'meta',
      '#attributes' => array(
        'name' => 'viewport',
        'content' => 'width=device-width; initial-scale=' . $settings['viewport']['initial'] . '; maximum-scale=' . $settings['viewport']['max'] . '; minimum-scale=' . $settings['viewport']['min'] . '; user-scalable=' . $settings['viewport']['user'] . ';',
      ),
    );

    drupal_add_html_head($meta, 'alpha-viewport');
  }  
}