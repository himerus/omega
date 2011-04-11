<?php

require_once dirname(__FILE__) . '/includes/alpha.inc';

/**
 * Implements hook_theme()
 *
 * @todo figure out WTF with template suggestions
 * 
 * @see delta_theme()
 * @see http://api.drupal.org/api/function/hook_theme/7
 * - There was cause to create a module here to implement a proper theme 
 *   function. There was major issue with attempting to get the zone elements 
 *   to work properly. zone.tpl.php was being used when declared here in 
 *   alpha_theme(), however, suggestions for more specific templates was NOT.
 *   
 * - The need here was to have the priority order be:
 *   - zone-ZONEID.tpl.php (the actual zone itself has a custom override)
 *     each have their own custom template to use for more generic implementations
 *   - zone.tpl.php (default)
 */
function alpha_theme($existing, $type, $theme, $path) {
  $preprocess = array(
    'template_preprocess', 
    'template_preprocess_zone',
    'alpha_preprocess',
    'alpha_preprocess_zone',
  );
  
  $process = array(
    'template_process', 
    'template_process_zone',
    'alpha_process',
    'alpha_process_zone'
  );
  
  return array(
  	'zone' => array(
      'template' => 'zone',
      'path' => $path . '/templates',
      'render element' => 'zone',
      'pattern' => 'zone__',
      'preprocess functions' => $preprocess,
      'process functions' => $process,
    ),  
  );
}

/**
  * Implements hook_preprocess()
  *
  * This function checks to see if a hook has a preprocess file associated with it
  * and if so, loads it.
  *
  * This makes it easier to keep sorted the preprocess functions that can be present
  * in the template.php file. You may still use hook_preprocess_page, etc in
  * template.php or create a file preprocess-page.inc in the preprocess folder to
  * include the appropriate logic to your preprocess functionality.
  *
  * @param $vars
  * @param $hook
  */
function alpha_preprocess(&$vars, $hook) {
  alpha_invoke('preprocess', $hook, $vars);
}

/**
 * Implements hook_process()
 * 
 * @see alpha_preprocess().
 * 
 * @param $vars
 * @param $hook
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