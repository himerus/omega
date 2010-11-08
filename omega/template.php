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
	//krumo($hook);
  // Collect all information for the active theme.
  $themes_active = array();
  global $theme_info;
  
  if (substr($hook, 0, 4) == 'zone') {
  	$hook = 'zone';
  }
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
	//krumo($hook);
// Collect all information for the active theme.
  $themes_active = array();
  global $theme_info;
  if (substr($hook, 0, 4) == 'zone') {
    $hook = 'zone';
  }
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

function omega_preprocess_zone(&$vars) {
  //krumo('WEEEEEWT, preprocess_zone called');
}
function omega_process_zone(&$vars) {
  //krumo('WEEEEEWT, process_zone called');
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
 * 
 * @see delta_theme()
 * @see http://api.drupal.org/api/function/hook_theme/7
 * - There was cause to create a module here to implement a proper theme 
 *   function. There was major issue with attempting to get the zone elements 
 *   to work properly. zone.tpl.php was being used when declared here in 
 *   omega_theme(), however, suggestions for more specific templates was NOT.
 *   
 * - The need here was to have the priority order be:
 *   - zone-ZONEID.tpl.php (the actual zone itself has a custom override)
 *   - zone-ZONETYPE.tpl.php (the zone type ('above', 'below', 'content'))
 *     each have their own custom template to use for more generic implementations
 *   - zone.tpl.php (default)
 */
function omega_theme($existing, $type, $theme, $path) {
	$hooks = array();
  $variables = array(
    'zid' => NULL, 
    'type' => NULL, 
    'enabled' => NULL, 
    'wrapper' => NULL, 
    'zone_type' => NULL, 
    'container_width' => NULL, 
    'regions' => NULL
  );
  $template_path = drupal_get_path('module', 'delta') .'/theme';
  $preprocess_functions = array(
    'template_preprocess', 
    'template_preprocess_zone',
    'omega_preprocess',
    'omega_preprocess_zone',
  );
  $process_functions = array(
    'template_process', 
    'template_process_zone',
    'omega_process',
    'omega_process_zone'
  );
  $hooks['zone'] = array(
    'variables' => $variables,
    'pattern' => 'zone__',
    'preprocess functions' => $preprocess_functions,
    'process functions' => $process_functions,
  );
  return $hooks;
}
/**
 * Implements hook_theme_registry_alter()
 * 
 * @param array $registry
 * 
 * @see http://api.drupal.org/api/function/hook_theme_registry_alter/7
 */
function omega_theme_registry_alter($registry) {
  //krumo($registry);
}
/*
function omega_page_alter($page) {
	global $theme_key, $theme_info;
	// theme_key is the name of the current theme
	//$vars['theme_key'] = $theme_key;
	// theme_info is the array of theme information (region, settings, zones, etc.)
	//$vars['theme_info'] = $theme_info;
	// default container width will be used in special zones and zones without a 
	// container width defined in theme settings
	$default_container_width = theme_get_setting('omega_default_container_width');
	// pulling just the zone data out of the theme_info array
	$theme_zones = $theme_info->info['zones'];
	// creating empty array to hold our custom zone[group] data
	$zones = array(
	  'before' => array(),
	  'content' => array(),
	  'after' => array(),
	);
	// separate out the specific content zone (a very special case)
	$content_zone = $theme_zones['content'];
	// zone keys give us a way to find the numerical position of the content zone
	// thus giving us a way to split up the array into before and after content zones
	$zone_keys = array_keys($theme_zones);
	// content_position is the numberical location of the content zone
	$content_position = array_search('content', $zone_keys);
	// zones_before_content are all zones that appear before content in the array
	$zones_before_content = array_slice($theme_zones, 0, $content_position, TRUE);
	// zones_after_content are all zones that appear after content in the array
	$zones_after_content = array_slice($theme_zones, $content_position + 1, count($theme_zones), TRUE);
	
	

	foreach ($theme_zones as $zone_ref => $regions) {
	  $zone = array();
	  $zone['#zid'] = $zone_ref;
	  if(array_key_exists($zone_ref, $zones_before_content)) {
	    $zone['#type'] = 'before';
	  }
	  elseif(array_key_exists($zone_ref, $zones_after_content)) {
	    $zone['#type'] = 'after';
	  }
	  else {
	    $zone['#type'] = 'content';
	  }
	  $zone['#theme_wrappers'] = array('zone');
	  //$zone['#sorted'] = TRUE;
	  $zone['#enabled'] = theme_get_setting('omega_'. $zone_ref .'_enabled') || theme_get_setting('omega_'. $zone_ref .'_enabled') == 0 ? theme_get_setting('omega_'. $zone_ref .'_enabled') : 1;
	  $zone['#wrapper'] = theme_get_setting('omega_'. $zone_ref .'_wrapper') ? theme_get_setting('omega_'. $zone_ref .'_wrapper') : 0;
	  $zone['#zone_type'] = theme_get_setting('omega_'. $zone_ref .'_zone_type') ? theme_get_setting('omega_'. $zone_ref .'_zone_type') : 'static';
	  $zone['#container_width'] = theme_get_setting('omega_'. $zone_ref .'_container_width') ? theme_get_setting('omega_'. $zone_ref .'_container_width') : $default_container_width;
	  
	  //$zone['regions'] = array();
	  if ($zone['#enabled']) {
      //$zones[$zone['type']][$zone['zid']] = theme(array('zone__' . $zone['zid'], 'zone__' . $zone['type'], 'zone'), $zone);
      $page[$zone['#zid']] = $zone;
      $page[$zone['#zid']]['#markup'] = '';
		  foreach($regions as $region) {
	      $page[$zone['#zid']]['#markup'] .= render($page[$region]);
	      $page[$zone['#zid']]['#regions'][$region] = $page[$region];
	      unset($page[$region]);
	    }
    }
	  
	  
	}
	
	// zones appearing before content on page
	$page['zones_above'] = array();
	$before = array_keys($zones_before_content);
	foreach($before as $k => $zone) {
	  if (isset($zones['before'][$zone])) {
	    $page['zones_above'][$zone] = $zones['before'][$zone];
	  }
	}
	// required content zone
	$page['content_zone'] = $zones['content'];
	// zones appearing after content on page
	$page['zones_below'] = array();
	$after = array_keys($zones_after_content);
	foreach($after as $k => $zone) {
	  if (isset($zones['after'][$zone])) {
	    $page['zones_below'][$zone] = $zones['after'][$zone];
	  }
	}
	
	krumo($page);
}
*/