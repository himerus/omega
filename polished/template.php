<?php
// $Id: template.php,v 1.1 2009/07/12 01:38:18 himerus Exp $
/**
 * Implementation of HOOK_theme().
 */
function polished_theme(&$existing, $type, $theme, $path) {
	// grab the default theme settings from the .info file & merge with database stored values
	include_once './' . drupal_get_path('theme', 'omega') . '/theme-functions.inc';
  omega_theme_get_default_settings($theme);
  return array();
}

/**
 * Override or insert variables into all templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered (name of the .tpl.php file.)
 */
/* -- Delete this line if you want to use this function
function polished_preprocess(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the page templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("page" in this case.)
 */
function polished_preprocess_page(&$vars, $hook) {
	// overriding the mission statement layout from main theme, as it's being moved and needs it's own grid class(es)
	// implement mission statement settings
	global $theme_key;
	include_once './' . drupal_get_path('theme', 'omega') . '/theme-functions.inc';
	$settings = theme_get_settings($theme_key);
	$omega = omega_theme_get_default_settings('omega');
	// Merge the saved variables and their default values.
	$settings = array_merge($omega, $settings);
	
	$vars['mission'] = t(variable_get('site_mission', ''));
	if ($settings['mission_statement_pages'] == 'all' || $vars['is_front'] && $settings['mission_statement_pages'] == 'home') {
		$vars['omega']['mission_classes'] = ao($vars, array('mission', 'header_first', 'header_last'), 'mission', TRUE);
	  $vars['mission'] = '<div id="mission" class="grid-5 '.$vars['omega']['mission_classes'].'"><p>' .$vars['mission']. '</p></div>';
	}
	if ($vars['original']['header_first']) {
	  $vars['omega']['header_first_classes'] = ao($vars, array('mission', 'header_first', 'header_last'), 'header_first', TRUE);
	  $vars['header_first'] = '<div id="header-first" class="'.ns('grid-'. $settings['omega_header_wrapper_width'], $vars['header_last'], $settings['omega_header_last_width']). $vars['omega']['header_first']. $vars['omega']['header_first_classes']. '">'. $vars['header_first']. '</div>';
	}
	// $header_last
	if ($vars['original']['header_last']) {
	  $vars['omega']['header_last_classes'] = ao($vars, array('mission', 'header_first', 'header_last'), 'header_last', TRUE);
	  $vars['header_last'] = '<div id="header-last" class="'.ns('grid-'. $settings['omega_header_wrapper_width'], $vars['header_first'], $settings['omega_header_first_width']). $vars['omega']['header_last_classes']. '">'. $vars['header_last']. '</div>';
	}
	unset($vars['site_slogan']);
}
// */

/**
 * Override or insert variables into the node templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("node" in this case.)
 */
/* -- Delete this line if you want to use this function
function polished_preprocess_node(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the comment templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("comment" in this case.)
 */
/* -- Delete this line if you want to use this function
function polished_preprocess_comment(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */

/**
 * Override or insert variables into the block templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
/* -- Delete this line if you want to use this function
function polished_preprocess_block(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
// */
