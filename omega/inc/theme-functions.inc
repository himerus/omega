<?php
// $Id$

/**
 * @file
 * This file contains functions that must be accessed BOTH in template.php
 * AND in theme-settings.php
 * 
 * putting the functions in template.php alone is not enough when using an 
 * alternate administrative interface to call specific php functions related
 * to the Omega theme.
 */

/**
 * NINESIXTY - Contextually adds 960 Grid System classes.
 *
 * The first parameter passed is the *default class*. All other parameters must
 * be set in pairs like so: "$variable, 3". The variable can be anything
 * available within a template file and the integer is the width set for the
 * adjacent box containing that variable.
 *
 *  class="<?php print ns('grid-16', $var_a, 6); ?>"
 *
 * If $var_a contains data, the next parameter (integer) will be subtracted from
 * the default class. See the README.txt file.
 */
function ns() {
  $args = func_get_args();
  $default = array_shift($args);
  // Get the type of class, i.e., 'grid', 'pull', 'push', etc.
  // Also get the default unit for the type to be procesed and returned.
  list($type, $return_unit) = explode('-', $default);

  // Process the conditions.
  $flip_states = array('var' => 'int', 'int' => 'var');
  $state = 'var';
  foreach ($args as $arg) {
    if ($state == 'var') {
      $var_state = !empty($arg);
    }
    elseif ($var_state) {
      $return_unit = $return_unit - $arg;
    }
    $state = $flip_states[$state];
  }

  $output = '';
  // Anything below a value of 1 is not needed.
  if ($return_unit > 0) {
    $output = $type . '-' . $return_unit;
  }
  return $output;
}


/**
 * The region_builder function will create the variables needed to create
 * a dynamic group of regions. This function is simply a quick pass-thru
 * that will create either inline or stacked regions. This function will
 * not do any advanced functionality, but simply assing the appropriate
 * classes based on the settings for the theme.
 *
 * For a more advanced set of regions, dynamic_region_builder() will be used.
 */
function static_region_builder($region_data, $container_width, $vars) {
  // let's cycle the region data, and determine what we have
  foreach ($region_data AS $region => $info) {
    // if we do have content for this region, let's create it.
    if ($info['data']) {
      $vars[$region . '_classes'] = ns('grid-' . $info['width']);
    }
    if (isset($info['spacing'])) {
      foreach ($info['spacing'] AS $attribute => $value) {
        if ($value) {
          $vars[$region . '_classes'] .= ' ' . $attribute . '-' . $value;
        } 
      }
    }
  }
  return $vars;
}

/*
function _omega_dynamic_zones($width, $conditions, $vars) {
  foreach ($conditions AS $variable => $reaction) {
    if (($reaction['type'] && isset($vars['page'][$variable])) || (!$reaction['type'] && !isset($vars['page'][$variable]))) {
      $width = $width - $reaction['value'];
    }
  }
  return $width;
}
*/

function _omega_dynamic_zones($width, $conditions, $vars) {
  foreach ($conditions AS $variable => $reaction) {
    if (($reaction['type'] && is_array($vars['page'][$variable]) && count($vars['page'][$variable]) > 0 ) || (!$reaction['type'] && (!is_array($vars['page'][$variable]) || count($vars['page'][$variable]) == 0)  )) {
      $width = $width - $reaction['value'];
    }
  }
  return $width;
} 


function _omega_dynamic_widths($width, $conditions, $vars) {
  foreach ($conditions AS $variable => $zone) {
    if ((isset($vars['page'][$variable])) && count($vars['page'][$variable]) > 0) {
      $width = $width - $zone['width'];
    }
  }
  return $width;
}

/**
 * The dynamic_region_builder function will be used to pass important zones
 * like the content regions where the regions sent to the function MUST appear
 * inline, and advanced calculations need to be done in order to display the as such
 *
 * Stacked regions are not possible using this function, and should be passed through
 * static_region_builder() instead.
 */
function dynamic_region_builder($region_data, $container_width, $vars) {
  // let's cycle the region data, and determine what we have
  foreach ($region_data AS $region => $info) {
    // if we do have content for this region, let's create it.
    if (isset($info['data'])) {
      if (isset($info['primary'])) {
        $width = $container_width;
        $vars[$region . '_classes'] = ns('grid-' . _omega_dynamic_widths($width, $info['related'], $vars));
      }
      else {
        $width = $info['width'];
        $vars[$region . '_classes'] = ns('grid-' . $info['width']);
      }
      // we know we have stuff to put here, so we can check for push & pull options
      if ($info['pull']) {
        // looks like we do wanna pull, or this value would have been false, so let's boogie
        $vars[$region . '_classes'] .= ' ' . ns('pull-' . _omega_dynamic_zones($info['pull']['width'], $info['pull']['conditions'], $vars));
      }
      if ($info['push']) {
        // looks like a push
        $vars[$region . '_classes'] .= ' ' . ns('push-' . _omega_dynamic_zones($info['push']['width'], $info['push']['conditions'], $vars));
      }
    }
    // currently ignored becuase we have not given prefix/suffix class options
    // to the primary content zones... this will become active again later
    if (isset($info['spacing'])) {
      foreach ($info['spacing'] AS $attribute => $value) {
        if ($value) {
          $vars[$region . '_classes'] .= ' ' . $attribute . '-' . $value;
        }
      }
    }
    // \unused prefix/suffix stuffs
  }
  return $vars;
}