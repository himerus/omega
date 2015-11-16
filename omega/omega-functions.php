<?php

// Include Breakpoint Functionality
use Drupal\breakpoint;

/**
 * Function returns the trimmed name of the breakpoint id
 * converting omega.standard.all to simply 'all'
 */
function omega_return_clean_breakpoint_id($breakpoint) {
  return str_replace($breakpoint->getGroup() . '.', "", $breakpoint->getBaseId());
}

/**
 * Custom function to return the available layouts (and config) for a given Omega theme/subtheme
 */
function omega_return_layouts($theme) {
  
  // grab the defined layouts in config/install/$theme.layouts.yml
  $layouts = \Drupal::config($theme . '.layouts')->get();
  
  foreach ($layouts AS $layout => $null) {
    // grab the configuration for the requested layout
    $layout_config_object = \Drupal::config($theme . '.layout.' . $layout);
    // assign the values to our array
    $layouts[$layout] = $layout_config_object->get();
  }
  return $layouts;
}

/**
 * Custom function to return the active layout to be used for the active page.
 */
function omega_return_active_layout() {
  $theme = \Drupal::theme()->getActiveTheme()->getName();
  $front = \Drupal::service('path.matcher')->isFrontPage();
  //$node = menu_get_object();

  // setup default layout
  $defaultLayout = theme_get_setting('default_layout', $theme);
  $layout = $defaultLayout;
  
  // if it is the front page, check for an alternate layout
  if ($front) {
    $homeLayout = theme_get_setting('home_layout', $theme);
    $layout = $homeLayout ? $homeLayout : $defaultLayout;
  }
  
  /*
  // if it is a node, check for an alternate layout
  if ($node) {
    $type = $node->type;
    $nodeLayout = theme_get_setting($type . '_layout', $theme);
    $layout = $nodeLayout ? $nodeLayout : $defaultLayout;
  }
  
  */
  
  return $layout;
}

function _omega_getActiveBreakpoints($theme) {
  // get the default layout and convert to name for breakpoint group
  $breakpointGroupId = str_replace("_", ".", theme_get_setting('default_layout', $theme));
  $breakpointGroup = \Drupal::service('breakpoint.manager')->getBreakpointsByGroup($breakpointGroupId);
  if ($breakpointGroup) {
    // custom theme breakpoints
    return $breakpointGroup;
  }
  else {
    // default omega breakpoints
    return \Drupal::service('breakpoint.manager')->getBreakpointsByGroup('omega.standard');
  }
}

/** 
 *  Returns array of optional Libraries that can be enabled/disabled in theme settings
 *  for Omega, and Omega sub-themes. The listings here are tied to entries in omega.libraries.yml.
 */
function _omega_optional_libraries($theme) {
  $status = theme_get_setting('styles', $theme);
  
  return array(
    'scss_html_elements' => array(
      'title' => 'Generic HTML Elements',
      'description' => 'Provides basic styles for generic tags like &lt;a&gt;, &lt;p&gt;, &lt;h2&gt;, etc.',
      'library' => 'omega/omega_html_elements',
      'status' => $status['scss_html_elements'],
    ),
    
    'scss_branding' => array(
      'title' => 'Branding Styles',
      'description' => 'Provides basic layout and styling for logo area.',
      'library' => 'omega/omega_branding',
      'status' => $status['scss_branding'],
    ),
    
    'scss_breadcrumbs' => array(
      'title' => 'Breadcrumbs',
      'description' => 'Basic breadcrumb styling.',
      'library' => 'omega/omega_breadcrumbs',
      'status' => $status['scss_breadcrumbs'],
    ),
    
    'scss_main_menus' => array(
      'title' => 'Main Menu Styling',
      'description' => 'Basic layout and styling for main menu elements.',
      'library' => 'omega/omega_main_menus',
      'status' => $status['scss_main_menus'],
    ),
    'scss_messages' => array(
      'title' => 'Messages',
      'description' => 'Custom styles for Drupal system messages.',
      'library' => 'omega/omega_messages',
      'status' => $status['scss_messages'],
    ),
    'scss_pagers' => array(
      'title' => 'Pagers',
      'description' => 'Custom styles for Drupal pagers.',
      'library' => 'omega/omega_pagers',
      'status' => $status['scss_pagers'],
    ),
    'scss_tabs' => array(
      'title' => 'Local Task Tabs',
      'description' => 'Custom styles for Drupal tabs.',
      'library' => 'omega/omega_tabs',
      'status' => $status['scss_tabs'],
    ),
  );
}





