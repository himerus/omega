<?php

require_once dirname(__FILE__) . '/includes/alpha.inc';

/**
 * Implements hook_theme()
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
  * Implements hook_preprocess()
  */
function alpha_preprocess(&$vars, $hook) {
  alpha_invoke('preprocess', $hook, $vars);
}

/**
 * Implements hook_process()
 */
function alpha_process(&$vars, $hook) {
  alpha_invoke('process', $hook, $vars);
}

/*
 * Implements hook_theme_registry_alter()
 */
function alpha_theme_registry_alter(&$registry) {
  global $theme_key;
  
  alpha_build_registry($theme_key, $registry);
}

/**
 * Implements hook_css_alter().
 */
function alpha_css_alter(&$css) {
  global $theme_key;
  
  $settings = alpha_settings($theme_key);
  
  if (!empty($settings['exclude'])) {
    foreach ($settings['exclude'] as $file => $exclude) {
      if ($exclude) {
        unset($css[$file]);
      }
    }
  }
}