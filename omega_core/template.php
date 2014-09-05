<?php

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

function omega_preprocess_omega_indicator(&$vars) {
  //dsm($vars);
  
  $logo_image = '<img src="' . drupal_get_path('theme', 'omega') . '/logo.png" />';
  
  $vars['logo'] = l($logo_image, 'http://drupal.org/project/omega', array(
    'attributes' => array(
      //'target' => '_blank',
      'id' => 'indicator-toggle',
      'class' => array(
        'indicator-open'
      ),
    ),
    'html' => true,
  ));
  
  //dsm($vars);
}

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
    // get the default theme
    //$config = \Drupal::config('system.theme');
    //$default_theme = $config->get('default');
    // get the regions for the default theme
    $theme_regions = $themes[$theme]->info['regions'];
    //dsm($regions);
    
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
          // for some reason something changed between alpha10 and 11 that made the weight in the commented out code below not work anymore.
          $page[$region] = $themedemoblock + $page[$region];
          /*
          $page[$region]['themedemoblock'] = array(
            '#markup' => '<div id="theme-demo-block--'. $region .'" class="theme-demo-block active-region clearfix"><h3 class="block-title demo-block-title">' . t('@regionname', array('@regionname' => $region_name . ' Region')) . '</h3><div class="demo-block-content"></div></div>',
            '#weight' => -9999
          );
          */
        }
        else {
          // no region was already present, so we'll need to insert it
          $page[$region] = array(
            '#region' => $region,
            'themedemoblock' => array(
              //'#markup' => '<div id="theme-demo-block--'. $region .'" class="theme-demo-block clearfix"><h3 class="block-title demo-block-title">' . t('@regionname', array('@regionname' => $region_name . ' Region')) . '</h3><div class="demo-block-content"><p>This sample content is provided to demonstrate the display of all regions.</p></div></div>',
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
    //krumo($page);
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

function omega_css_alter(&$css) {
  $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  $ogsLayout = theme_get_setting('enable_omegags_layout', $theme);
  $hasLayout = isset($ogsLayout) ? $ogsLayout : TRUE;
  
  $defaultLayout = theme_get_setting('default_layout', $theme);
  //dsm($defaultLayout);
  $defaultOmegaLayout = drupal_get_path('theme', 'omega') . '/style/css/layout/omega_default.css';
  $activeLayoutCSS = drupal_get_path('theme', $theme) . '/style/css/layout/'.$defaultLayout.'.css';

  //dsm($defaultOmegaLayout);
  
  
  // turn off Omega.gs generated layout styles if user has turned off the awesome.
  if (!$hasLayout && isset($css[$defaultOmegaLayout])) {
    unset($css[$defaultOmegaLayout]);
  }
  // alter the CSS loaded based on the $activeLayoutCSS
  if (isset($css[$defaultOmegaLayout])){
    $css[$defaultOmegaLayout]['data'] = $activeLayoutCSS;
  }
  //dsm($css);
}

/**
 * Implements hook_js_alter().
 */
function omega_js_alter(&$javascript) {
  
  // If >=1 JavaScript asset has declared a dependency on drupalSettings, the
  // 'settings' key will exist. Thus when that key does not exist, return early.
  if (!isset($javascript['settings'])) {
    //return;
  }
  
  $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  $themes = list_themes();
  $themeSettings = $themes[$theme];  
  
  $screenDemo = theme_get_setting('screen_demo_indicator', $theme);
  $activeLayout = theme_get_setting('default_layout', $theme);
  
  $breakpoints = $themeSettings->info['breakpoints'];
  //dsm($breakpoints);
  
  if ($screenDemo) {
    $layouts = array();

    //$javascript['settings']['data']['omega_breakpoints'] = array();
    
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
  //dsm($javascript['settings']);
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

function omega_preprocess_page(&$vars) {
  $vars['region_classes'] = '';
  // removing help region if it is empty.
  $helpsize = count($vars['page']['help']);
  if (isset($vars['page']['help']) && $helpsize == 0) {
    unset($vars['page']['help']);
  }
}