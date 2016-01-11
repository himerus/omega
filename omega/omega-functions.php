<?php

// Include Breakpoint Functionality
use Drupal\breakpoint;
//use Drupal\views\Views;

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
  $node = \Drupal::routeMatch()->getParameter('node');
  $term = \Drupal::routeMatch()->getParameter('taxonomy_term');
  //$view = \Drupal::routeMatch()->getParameter('view_id');
  
  // setup default layout
  $defaultLayout = theme_get_setting('default_layout', $theme);
  $layout = $defaultLayout;
  
  // if it is a node, check for and assign alternate layout
  if ($node) {
    $type = $node->getType();
    $nodeLayout = theme_get_setting('node_type_' . $type . '_layout', $theme);
    $layout = $nodeLayout ? $nodeLayout : $defaultLayout;
  }
  
  // if it is a views page, check for and assign alternate layout 
/*
  if ($view) {
    $viewData = Views::getView($view);
    //dsm($viewData);
    //$viewLayout = theme_get_setting('view_' . $view . '_layout');
    //$layout = $viewLayout ? $viewLayout : $defaultLayout;
  }
*/
  
  // if it is a term page, check for and assign alternate layout 
  if ($term) {
    $vocab = $term->getVocabularyId();
    $vocabLayout = theme_get_setting('taxonomy_' . $vocab . '_layout');
    $layout = $vocabLayout ? $vocabLayout : $defaultLayout;
  }
  
  // if it is the front page, check for an alternate layout
  // this should come AFTER all other adjustments
  // This ensures if someone has set an individual node page, term page, etc. 
  // as the front page, the front page setting has more priority
  if ($front) {
    $homeLayout = theme_get_setting('home_layout', $theme);
    $layout = $homeLayout ? $homeLayout : $defaultLayout;
  }
  
  return $layout;
}

/**
 *  Takes $theme as argument, and returns ALL breakpoint groups available to this theme
 *  which includes breakpoints defined by the theme itself or any base theme of this theme
 */
function _omega_getAvailableBreakpoints($theme) {
  // Check for breakpoints module and set a warning and a flag to disable much of the theme settings if its not available
  $breakpoints_module = \Drupal::moduleHandler()->moduleExists('breakpoint');
  
  if ($breakpoints_module == TRUE) {
    // get all the breakpoint groups
    $all_breakpoint_groups = \Drupal::service('breakpoint.manager')->getGroups();
    //dsm($all_breakpoint_groups);
    // get all the base themes of this theme    
    $baseThemes = \Drupal::theme()->getActiveTheme()->getBaseThemes();
    
    $theme_ids = array(
      $theme => \Drupal::theme()->getActiveTheme()->getExtension()->info['name']
    );
    foreach($baseThemes AS $theme_key => $data) {
      // create/add to array with base themes as values
      $clean_theme_name = $data->getExtension()->info['name'];
      $theme_ids[$theme_key] = $clean_theme_name;
    }
    
    // cycle all the breakpoint groups and see if they are a part of this theme or its base theme(s)
    foreach ($all_breakpoint_groups as $group_key => $group_values) {
      // get the theme name that provides this breakpoint group
      $breakpoint_theme = \Drupal::service('breakpoint.manager')->getGroupProviders($group_key);
      // see if the theme providing the breakpoint group is part of our base theme structure
      $breakpoint_theme_name = key($breakpoint_theme);
      if (array_key_exists($breakpoint_theme_name, $theme_ids)) {
        $breakpoint_groups[$group_key] = \Drupal::service('breakpoint.manager')->getBreakpointsByGroup($group_key);
      }
    }
    
    foreach($breakpoint_groups as $group => $breakpoint_values)  {
      if ($breakpoint_values !== array()) {
        // get the theme name that provides this breakpoint group
        $breakpoint_theme = \Drupal::service('breakpoint.manager')->getGroupProviders($group);
        // see if the theme providing the breakpoint group is part of our base theme structure
        $breakpoint_theme_id = key($breakpoint_theme);
        $breakpoint_theme_name = $theme_ids[$breakpoint_theme_id];
        $breakpoint_options[$breakpoint_theme_name][$group] = $group;
      }
    }
  }
  else {
    drupal_set_message(t('Omega requires the <b>Breakpoint module</b>. Open the <a href="@extendpage" target="_blank">Extend</a> page and enable Breakpoint.', array('@extendpage' => base_path() . 'admin/modules')), 'warning');
  }
  return $breakpoint_options;
}

function _omega_getActiveBreakpoints($layout, $theme) {
  // get the default layout and convert to name for breakpoint group
  $breakpointGroupId = theme_get_setting('breakpoint_group_' . $layout, $theme);
  //dsm($breakpointGroupId);
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
    'scss_taxonomy_terms' => array(
      'title' => 'Taxonomy Terms',
      'description' => 'Custom styles for Drupal taxonomy listings on nodes.',
      'library' => 'omega/omega_taxonomy_terms',
      'status' => $status['scss_taxonomy_terms'],
    ),
  );
}





