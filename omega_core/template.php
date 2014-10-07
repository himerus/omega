<?php
require_once(drupal_get_path('theme', 'omega') . '/omega-functions.php');

/**
 * Implements hook_theme().
 */
function omega_theme() {
  return array(
    'omega_indicator' => array(
      //'render element' => 'elements',
      'variables' => array(
        'logo' => NULL,
      ),
      'template' => 'templates/omega-indicator',
    ),
  );
}

/**
 * Custom theme function to render Omega Five screen data indicator.
 */
function omega_preprocess_omega_indicator(&$vars) {
  // find Omega logo to display in indicator.
  $logo_image = theme('image', array(
    'path' => drupal_get_path('theme', 'omega') . '/logo.png',
    'title' => t('Powered by Omega Five'),
  ));  
  $vars['logo'] = l($logo_image, 'http://drupal.org/project/omega', array(
    'attributes' => array(
      //'target' => '_blank',
      'id' => 'indicator-toggle',
      'class' => array(
        'indicator-open'
      ),
      'target' => '_blank',
    ),
    'html' => true,
  ));
}

/**
 * Implements hook_page_alter().
 */
function omega_page_alter (&$page) {
  $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  
  //drupal_set_message(t('Current theme: '. $theme));
  drupal_add_js(drupal_get_path('theme', 'omega') . '/js/omega.js');
  
  $regionDemo = theme_get_setting('block_demo_mode', $theme);
  $screenDemo = theme_get_setting('screen_demo_indicator', $theme);
  
  if ($regionDemo) {
    //dsm($page);
    drupal_set_message(t('Region Demonstration mode is on. This can be turned off in theme settings.'), 'warning');
    // get a list of themes
    $themes = list_themes();
    // get the regions for the default theme
    $theme_regions = $themes[$theme]->info['regions'];
    
    $regionSkip = theme_get_setting('block_demo_excluded_regions', $theme);
    foreach($theme_regions as $region => $region_name) {
      if (!in_array($region, $regionSkip)) {
        // here we want to add a simple block that we can use to demonstrate region placements for ALL regions in the theme(s)
        if (isset($page[$region])) {
          
          $themedemoblock = array(
            'themedemoblock' => array(
              '#markup' => '<div id="theme-demo-block--'. $region .'" class="theme-demo-block active-region clearfix"><h3 class="block-title demo-block-title">' . t('@regionname', array('@regionname' => $region_name . ' Region')) . '</h3><div class="demo-block-content"></div></div>',
              '#weight' => -9999  
            ),
          );
          $page[$region] = $themedemoblock + $page[$region];
        }
        else {
          // no region was already present, so we'll need to insert it
          $page[$region] = array(
            '#region' => $region,
            'themedemoblock' => array(
              '#markup' => '<div id="theme-demo-block--'. $region .'" class="theme-demo-block inactive-region clearfix"><h3 class="block-title demo-block-title">' . t('@regionname', array('@regionname' => $region_name . ' Region')) . '</h3><div class="demo-block-content"></div></div>',
              '#weight' => -9999
            ),
          );
        }
      }
      else {
        // works for not displaying page top and bottom or other omitted regions.
      }
    }
  }
  
  if ($screenDemo) {
    if (isset($page['page_top'])) {      
      $page['page_top']['themedemoblock'] = array(
        '#theme' => 'omega_indicator',
        '#weight' => -9999
      );
    }
    else {
      $page['page_top'] = array(
        '#region' => 'page_top',
        'themedemoblock' => array(
          '#theme' => 'omega_indicator',
          '#weight' => -9999
        ),
      );
    }
  }  
}

/**
 * Implements hook_css_alter().
 */
function omega_css_alter(&$css) {
  $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  $ogsLayout = theme_get_setting('enable_omegags_layout', $theme);
  $hasLayout = isset($ogsLayout) ? $ogsLayout : TRUE;

  // setup default layout
  $defaultOmegaLayout = drupal_get_path('theme', 'omega') . '/style/css/layout/omega_default.css';
  $defaultLayout = omega_return_active_layout();
  $activeLayoutCSS = drupal_get_path('theme', $theme) . '/style/css/layout/'.$defaultLayout.'.css';

  $copyCSS = $css[$defaultOmegaLayout];
  
  // turn off Omega.gs generated layout styles if user has turned off the awesome.
  if (!$hasLayout && isset($css[$defaultOmegaLayout])) {
    unset($css[$defaultOmegaLayout]);
  }
  // alter the CSS loaded based on the $activeLayoutCSS
  if (isset($css[$defaultOmegaLayout])){
    $css[$defaultOmegaLayout]['data'] = $activeLayoutCSS;
  }
  
  $toggleCSS = _omega_optional_css($theme);

  foreach ($toggleCSS as $style => $data) {
    $stylePath = drupal_get_path('theme', 'omega') . '/style/css/base/' . $data['file'];
    // check it is active; if so, enable it
    if ($data['status']) {
      $css[$stylePath] = $copyCSS;
      $css[$stylePath]['data'] = $stylePath;
    }
  }
}

/**
 * Implements hook_js_alter().
 */
function omega_js_alter(&$javascript) {
  
  $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  $themes = list_themes();
  $themeSettings = $themes[$theme];  
  
  $screenDemo = theme_get_setting('screen_demo_indicator', $theme);
  // need to use a function for this after setting up layout "switcheroo"
  $activeLayout = omega_return_active_layout();
  
  $breakpoints = $themeSettings->info['breakpoints'];
  
  $layouts = array();
    
  foreach($breakpoints as $breakpointName => $breakpointMedia) {
    
    $layouts[$breakpointName] = array(
      'query' => $breakpointMedia,
      'name' => $breakpointName
    );
  }
  
  $javascript['settings']['data'][] = array(
    'omega_breakpoints' => array(
      'layouts' => $layouts,
    ),
    'omega' => array(
      'activeLayout' => $activeLayout,
      'activeTheme' => $theme
    )  
  );
}

/**
 * hook_html_head_alter()
 */
function omega_html_head_alter(&$head_elements) {
  global $theme;
  // cleartype
  $head_elements['omega_meta_clear_type'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array(
      'http-equiv' => "cleartype",
      'content' => "on",
    ),
    '#weight' => -998,
  );
  // update viewport tag
  $head_elements['viewport'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array(
      'content' => 'width=device-width, initial-scale=1, maximum-scale=2, minimum-scale=1, user-scalable=yes',
      'name' => 'viewport',
    ),
    '#weight' => -997,
  );
}

/**
 * Implements template_preprocess_page().
 */
function omega_preprocess_page(&$vars) {
  $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  // get a list of themes
  $themes = list_themes();
  // get theme settings for active theme
  $themeSettings = $themes[$theme];
  // defined regions for active theme
  $theme_regions = $themeSettings->info['regions'];

  // create an array to define the with-sidebar_first without-sidebar_first classes
  $region_classes = array();
  $layout = omega_return_active_layout();
  $regionGroups = $themeSettings->info['region_groups'];
  
  // grab the layout data stored in the DB
  $layouts = is_array(variable_get('theme_' . $theme . '_layouts')) ? variable_get('theme_' . $theme . '_layouts') : FALSE;
  // if there's not data in the DB, create it to avoid warnings.
  if (!$layouts) {
    $layouts = _omega_get_layout_json_data($theme);
  }
  
  foreach($regionGroups as $group_id => $group_name) {
    $groupRegions = element_children($layouts[$layout]['data']['all'][$group_id]['regions']);
    
    foreach($groupRegions as $region_id) {
    
      $altered_region_id = str_replace("_", "-", $region_id);
    
      if (isset($vars['page'][$region_id]['#region'])) {
        $region_classes[$group_id][] = 'with--' . $altered_region_id;
      }
      else {
        $region_classes[$group_id][] = 'without--' . $altered_region_id;
      }
    } 
    // convert to string version
    $region_classes[$group_id] = implode(" ", $region_classes[$group_id]);
    
  }
  // assign classes to page.tpl.php
  $vars['region_classes'] = $region_classes;
  

  // removing help region if it is empty.
  $helpsize = isset($vars['page']['help']) ? count($vars['page']['help']) : 0;
  if (isset($vars['page']['help']) && $helpsize == 0) {
    unset($vars['page']['help']);
  }
}

/**
 * Custom function to return the active layout to be used for the active page.
 */
function omega_return_active_layout() {
  $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  $front = drupal_is_front_page();
  $node = menu_get_object();

  // setup default layout
  $defaultLayout = theme_get_setting('default_layout', $theme);
  $layout = $defaultLayout;
  // if it is a node, check for an alternate layout
  if ($node) {
    $type = $node->type;
    $nodeLayout = theme_get_setting($type . '_layout', $theme);
    $layout = $nodeLayout ? $nodeLayout : $defaultLayout;
  }
  // if it is the front page, check for an alternate layout
  if ($front) {
    $homeLayout = theme_get_setting('home_layout', $theme);
    $layout = $homeLayout ? $homeLayout : $defaultLayout;
  }
  
  return $layout;
}

/**
 * Overrides theme_system_powered_by().
 */
function omega_system_powered_by() {
  // Drupal
  $drupal = l(t('Drupal'), 'http://drupal.org/', array(
    'attributes' => array(
      'target' => '_blank',
      'class' => array(
        'powered-by-link'
      ),
    ),
  ));
  // Omega
  $omega = l(t('Omega Five'), 'http://drupal.org/project/omega', array(
    'attributes' => array(
      'target' => '_blank',
      'class' => array(
        'powered-by-link'
      ),
    ),
  ));
  return '<div class="powered-by">Powered by ' . $drupal . ' and ' . $omega . '</div>';
}