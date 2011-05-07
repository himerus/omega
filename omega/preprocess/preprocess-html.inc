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
  $settings = alpha_settings($GLOBALS['theme_key']);
  
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
}