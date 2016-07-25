<?php

// Include Breakpoint Functionality
use Drupal\breakpoint;
use Drupal\omega\Theme\OmegaSettingsInfo;

/**
 * Function returns the trimmed name of the breakpoint id
 * converting omega.standard.all to simply 'all'
 * @param \Drupal\breakpoint\Breakpoint $breakpoint
 * @return mixed
 */
function omega_return_clean_breakpoint_id(\Drupal\breakpoint\Breakpoint $breakpoint) {
  return str_replace($breakpoint->getGroup() . '.', "", $breakpoint->getBaseId());
}

/**
 * Custom function to return the available layouts (and config) for a given Omega theme/subtheme
 * @param $theme
 * @return array|mixed|null
 */
function omega_return_layouts($theme) {
  
  // grab the defined layouts in config/install/$theme.layouts.yml
  $layouts = \Drupal::config($theme . '.omega-layouts')->get();
  foreach ($layouts AS $layout => $null) {
    // grab the configuration for the requested layout
    $layout_config_object = \Drupal::config($theme . '.layout.' . $layout);
    // assign the values to our array
    $layouts[$layout] = $layout_config_object->get();
    unset($layouts[$layout]['_core']); // where did this come from??
  }
  unset($layouts['_core']); // where did this come from??
  return $layouts;
}

/**
 * Custom function to return the theme that is providing a layout
 * This is either the theme itself ($theme) or a parent theme
 * @param $theme
 * @return int|string
 */
function omega_find_layout_provider($theme) {
  // Create Omega Settings Object
  $omegaSettings = new OmegaSettingsInfo($theme);
  
  // get the default settings for the current theme
  $themeSettings = $omegaSettings->getThemeInfo();
  
  // get the value of 'inherit_layout' from THEME.info.yml
  $inherit_layout = isset($themeSettings->info['inherit_layout']) ? $themeSettings->info['inherit_layout'] : FALSE;
  
  // we have encountered a theme that inherits layout from a base theme
  // now we will scan the array of applicable base themes looking for the 
  // closest parent providing layout and not inheriting it
  if ($inherit_layout) {
    // grab the base themes
    $baseThemes = $themeSettings->base_themes;
    // remove the core themes from the list
    unset($baseThemes['stable'], $baseThemes['classy']);
    // put the base themes in the proper order to traverse for layouts
    $baseThemes = array_reverse($baseThemes);
    
    foreach ($baseThemes AS $baseKey => $baseName) {
      //dpm($baseKey);
      $baseThemeSettings = $omegaSettings->getThemeInfo($baseKey);
      $base_inherit_layout = $baseThemeSettings->info['inherit_layout'];

      if (!$base_inherit_layout) {
        // we've found the first base theme in the chain that does provide its own layout
        // so we will return the key of that theme to use.
        return $baseKey;
      }
    }
    
  }
  // this theme provides its own layout, so just return the appropriate theme name
  else {
    return $theme;
  }
  //$provider = theme_get_setting($layout, $theme);
  return FALSE;
}

/**
 * Custom function to return the active layout to be used for the active page.
 */
function omega_return_active_layout() {
  $theme = \Drupal::theme()->getActiveTheme()->getName();
  $front = \Drupal::service('path.matcher')->isFrontPage();
  $node = \Drupal::routeMatch()->getParameter('node');
  $term = \Drupal::routeMatch()->getParameter('taxonomy_term');
  /*$view = \Drupal::routeMatch()->getParameter('view_id');*/
  
  $layoutProvider = omega_find_layout_provider($theme);
  //dpm($layoutProvider);
  // setup default layout
  $defaultLayout = theme_get_setting('default_layout', $layoutProvider);
  $layout = $defaultLayout;
  
  // if it is a node, check for and assign alternate layout
  if ($node) {
    $type = $node->getType();
    $nodeLayout = theme_get_setting('node_type_' . $type . '_layout', $layoutProvider);
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
    $homeLayout = theme_get_setting('home_layout', $layoutProvider);
    $layout = $homeLayout ? $homeLayout : $defaultLayout;
  }
  
  return array(
    'theme' => $layoutProvider,
    'layout' => $layout,
  );
}

/**
 *  Takes $theme as argument, and returns ALL breakpoint groups available to this theme
 *  which includes breakpoints defined by the theme itself or any base theme of this theme
 * @param $theme
 * @return mixed
 */
function _omega_getAvailableBreakpoints($theme) {
  // Check for breakpoints module and set a warning and a flag to disable much of the theme settings if its not available
  $breakpoints_module = \Drupal::moduleHandler()->moduleExists('breakpoint');
  $breakpoint_groups = array();
  $breakpoint_options = array();
  if ($breakpoints_module == TRUE) {
    // get all the breakpoint groups available to Drupal
    $all_breakpoint_groups = \Drupal::service('breakpoint.manager')->getGroups();
    // get all the base themes of this theme    
    $baseThemes = \Drupal::theme()->getActiveTheme()->getBaseThemes();
    //dpm($baseThemes);
    $debug = \Drupal::theme()->getActiveTheme()->getExtension();

    $theme_ids = array(
      $theme => \Drupal::theme()->getActiveTheme()->getExtension()->info['name']
    );
    foreach($baseThemes AS $theme_key => $data) {
      // create/add to array with base themes as values
      $clean_theme_name = $data->getExtension()->info['name'];
      $theme_ids[$theme_key] = $clean_theme_name;
    }
    
    //dpm($theme_ids);
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
  //dpm($breakpoint_options);
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
    drupal_set_message('The breakpoint group for your theme could not be found. Using default Omega version instead.', 'warning');
    return \Drupal::service('breakpoint.manager')->getBreakpointsByGroup('omega.standard');
  }
}

/**
 *  Returns array of optional Libraries that can be enabled/disabled in theme settings
 *  for Omega, and Omega sub-themes. The listings here are tied to entries in omega.libraries.yml.
 * @param $theme
 * @return array
 */

function _omega_optional_libraries($theme) {
  $status = theme_get_setting('styles', $theme);
  $themeHandler = \Drupal::service('theme_handler');
  $library_discovery = \Drupal::service('library.discovery');
  $themes = $themeHandler->rebuildThemeData();
  $themeObject = $themes[$theme];
  $baseThemes = $themeObject->base_themes;
  
  $ignore_libraries = array(
    'omega/omega_admin', // removed as it is only used for theme admin page(s) and is required
  );
  
  // create a variable to hold the full library data
  $allLibraries = array();
  // create a variable to combine all the libraries we can select/desect in our form
  $returnLibraries = array();
  // the libraries for the primary theme
  $themeLibraries = $library_discovery->getLibrariesByExtension($theme);
  foreach ($themeLibraries as $libraryKey => $themeLibrary) {
    if (!in_array($theme . '/' . $libraryKey, $ignore_libraries)) {
      $allLibraries[$libraryKey] = $themeLibrary;
      $returnLibraries[$theme . '/' . $libraryKey] = array(
        'title' => isset($themeLibrary['omega']['title']) ? $themeLibrary['omega']['title'] : $theme . '/' . $libraryKey,
        'description' => isset($themeLibrary['omega']['description']) ? $themeLibrary['omega']['description'] : 'No Description Available. :(',
        'library' => $theme . '/' . $libraryKey,
        'status' => isset($status[$theme . '/' . $libraryKey]) ? $status[$theme . '/' . $libraryKey] : TRUE,
        'allow_disable' => isset($themeLibrary['omega']['allow_enable_disable']) ? $themeLibrary['omega']['allow_enable_disable'] : TRUE,
        'allow_clone' => isset($themeLibrary['omega']['allow_clone_for_subtheme']) ? $themeLibrary['omega']['allow_clone_for_subtheme'] : TRUE,
      );
    }
  }
  
  // setup some themes to skip. 
  // Essentially trimming this down to only Omega and any Omega subthemes.
  $ignore_base_themes = array(
    'stable', 
    'classy'
  );
  
  // the libraries for any parent theme
  foreach ($baseThemes as $baseKey => $baseTheme) {
    if (!in_array($baseKey, $ignore_base_themes)) {
      foreach ($library_discovery->getLibrariesByExtension($baseKey) as $libraryKey => $themeLibrary) {
        //dpm($themeLibrary);
        if (!in_array($baseKey . '/' . $libraryKey, $ignore_libraries)) {
          $allLibraries[$libraryKey] = $themeLibrary;
          $returnLibraries[$baseKey . '/' . $libraryKey] = array(
            'title' => isset($themeLibrary['omega']['title']) ? $themeLibrary['omega']['title'] : $baseKey . '/' . $libraryKey,
            'description' => isset($themeLibrary['omega']['description']) ? $themeLibrary['omega']['description'] : 'No Description Available. :(',
            'library' => $baseKey . '/' . $libraryKey,
            'status' => isset($status[$baseKey . '/' . $libraryKey]) ? $status[$baseKey . '/' . $libraryKey] : TRUE,
            'allow_disable' => isset($themeLibrary['omega']['allow_enable_disable']) ? $themeLibrary['omega']['allow_enable_disable'] : TRUE,
            'allow_clone' => isset($themeLibrary['omega']['allow_clone_for_subtheme']) ? $themeLibrary['omega']['allow_clone_for_subtheme'] : TRUE,
          );
        }
      }
    }  
  }
  return $returnLibraries;
}





