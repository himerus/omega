<?php
// $Id$

/**
 * @file
 * Contains theme functions, preprocess and process overrides, and custom
 * functions for the Omega theme.
 *        ___           ___           ___           ___           ___     
 *	     /  /\         /  /\         /  /\         /  /\         /  /\    
 *	    /  /::\       /  /::|       /  /::\       /  /::\       /  /::\   
 *	   /  /:/\:\     /  /:|:|      /  /:/\:\     /  /:/\:\     /  /:/\:\  
 *	  /  /:/  \:\   /  /:/|:|__   /  /::\ \:\   /  /:/  \:\   /  /::\ \:\ 
 *	 /__/:/ \__\:\ /__/:/_|::::\ /__/:/\:\ \:\ /__/:/_\_ \:\ /__/:/\:\_\:\
 *	 \  \:\ /  /:/ \__\/  /~~/:/ \  \:\ \:\_\/ \  \:\__/\_\/ \__\/  \:\/:/
 *	  \  \:\  /:/        /  /:/   \  \:\ \:\    \  \:\ \:\        \__\::/ 
 *	   \  \:\/:/        /  /:/     \  \:\_\/     \  \:\/:/        /  /:/  
 *	    \  \::/        /__/:/       \  \:\        \  \::/        /__/:/   
 *	     \__\/         \__\/         \__\/         \__\/         \__\/    
 */

// include general theme functions required both in template.php AND theme-settings.php
  require_once(drupal_get_path('theme', 'omega') . '/inc/theme-functions.inc');

/**
  * Implements hook_preprocess().
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
function omega_preprocess(&$vars, $hook) {
  // Collect all information for the active theme.
  $themes_active = array();
  global $theme_info;
  // If there is a base theme, collect the names of all themes that may have
  // preprocess files to load.
  if (isset($theme_info->base_theme)) {
    global $base_theme_info;
    foreach ($base_theme_info as $base) {
      $themes_active[] = $base->name;
    }
  }

  // Add the active theme to the list of themes that may have preprocess files.
  $themes_active[] = $theme_info->name;
  // Check all active themes for preprocess files that will need to be loaded.
  foreach ($themes_active as $name) {
    if (is_file(drupal_get_path('theme', $name) . '/preprocess/preprocess-' . str_replace('_', '-', $hook) . '.inc')) {
      include(drupal_get_path('theme', $name) . '/preprocess/preprocess-' . str_replace('_', '-', $hook) . '.inc');
    }
  }
}

/**
 * Implementation of hook_process()
 * 
 * This function checks to see if a hook has a process file associated with 
 * it, and if so, loads it.
 * 
 * This makes it easier to keep sorted the process functions that can be present in the 
 * template.php file. You may still use hook_process_page, etc in template.php
 * or create a file process-page.inc in the process folder to include the appropriate
 * logic to your process functionality
 * 
 * @param $vars
 * @param $hook
 */
function omega_process(&$vars, $hook) {
// Collect all information for the active theme.
  $themes_active = array();
  global $theme_info;
  //krumo($theme_info);
  // If there is a base theme, collect the names of all themes that may have 
  // preprocess files to load.
  if (isset($theme_info->base_theme)) {
    global $base_theme_info;
    foreach ($base_theme_info as $base) {
      $themes_active[] = $base->name;
    }
  }

  // Add the active theme to the list of themes that may have preprocess files.
  $themes_active[] = $theme_info->name;

  // Check all active themes for preprocess files that will need to be loaded.
  foreach ($themes_active as $name) {
    if (is_file(drupal_get_path('theme', $name) . '/process/process-' . str_replace('_', '-', $hook) . '.inc')) {
      include(drupal_get_path('theme', $name) . '/process/process-' . str_replace('_', '-', $hook) . '.inc');
    }
  }
}

/**
 * Implements template_preprocess_html().
 *
 * Preprocessor for page.tpl.php template file.
 * The default functionality can be found in preprocess/preprocess-page.inc
 */
function omega_preprocess_html(&$vars) {
  
}

/**
 * Implements template_preprocess_page().
 */
function omega_preprocess_page(&$vars) {

}

/**
 * Implements template_preprocess_node().
 */
function omega_preprocess_node(&$vars) {

}

/**
 * Implements template_process_page().
 */
function omega_process_page(&$vars) {

}

/**
 * Implements template_process_node().
 */
function omega_process_node(&$vars) {
  // Convert node attributes to a string and append to existing RDFa attributes.
  $vars['attributes'] .= drupal_attributes($vars['node_attributes']);
}




/**
 * The rfilter function takes one argument, an array of values for the regions
 * for a "group" of regions like preface or postscript
 * @param $vars
 */
function rfilter($vars) {
  return count(array_filter($vars));
}

/**
 * ZEN - Returns HTML for a breadcrumb trail.
 *
 * @param $variables
 *   An associative array containing:
 *   - breadcrumb: An array containing the breadcrumb links.
 */
function omega_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  // Determine if we are to display the breadcrumb.
  $show_breadcrumb = theme_get_setting('omega_breadcrumb');
  if ($show_breadcrumb == 'yes' || $show_breadcrumb == 'admin' && arg(0) == 'admin') {

    // Optionally get rid of the homepage link.
    $show_breadcrumb_home = theme_get_setting('omega_breadcrumb_home');
    if (!$show_breadcrumb_home) {
      array_shift($breadcrumb);
    }

    // Return the breadcrumb with separators.
    if (!empty($breadcrumb)) {
      // Provide a navigational heading to give context for breadcrumb links to
      // screen-reader users. Make the heading invisible with .element-invisible.
      $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

      $breadcrumb_separator = theme_get_setting('omega_breadcrumb_separator');
      $trailing_separator = $title = '';
      if (theme_get_setting('omega_breadcrumb_title')) {
        $trailing_separator = $breadcrumb_separator;
        $title = drupal_get_title();
      }
      elseif (theme_get_setting('omega_breadcrumb_trailing')) {
        $trailing_separator = $breadcrumb_separator;
      }
      $output .= '<div class="breadcrumb">' . implode($breadcrumb_separator, $breadcrumb) . "$trailing_separator$title</div>";
      return $output;
    }
  }
  // Otherwise, return an empty string.
  return '';
}





/**
 * Implements hook_css_alter().
 * Alter CSS files before they are output on the page.
 *
 * @param $css
 *   An array of all CSS items (files and inline CSS) being requested on the page.
 */
function omega_css_alter(&$css) {
  // fluid width option
  if (theme_get_setting('omega_fixed_fluid') == 'fluid') {
    $css_960 = drupal_get_path('theme', 'omega') . '/css/960.css';
    if (isset($css[$css_960])) {
      $css[$css_960]['data'] = drupal_get_path('theme', 'omega') . '/css/960-fluid.css';
    }
  }
}

/**
 * Implements hook_theme().
 *
 * @todo figure out WTF with template suggestions
 */
function omega_theme($existing, $type, $theme, $path) {
	
	$items = array();
  $items['zone'] = array(
    'variables' => array('zid' => NULL, 'type' => NULL, 'enabled' => NULL, 'wrapper' => NULL, 'zone_type' => NULL, 'container_width' => NULL, 'regions' => NULL),
    'path' => drupal_get_path('theme', 'omega') .'/templates',
    'template' => 'zone',
    //'pattern' => 'zone',
  );
  return $items;
}
function omega_theme_registry_alter($registry) {
	//krumo($registry);
}