<?php

require_once('omega-functions.php');
//require_once('lib/Drupal/omega/phpsass/SassParser.php');


function omega_form_system_theme_settings_alter(&$form, &$form_state) {
  global $base_path;
  //dsm($form);
  
  // Get the theme name.
  $theme = $form_state['build_info']['args'][0];
  // get a list of themes
  $themes = list_themes();
  
  $themeSettings = $themes[$theme];  
  $defaultLayout = theme_get_setting('default_layout', $theme);
  // Pull the JSON file for the current layout
  $layoutLocation = drupal_get_path('theme', $theme) . '/layouts/' . $defaultLayout . '.json';
  //dsm($layoutLocation);
  $layoutJson = omega_json_load_layout_file($layoutLocation);
  //dsm($layoutJson);
  $databaseLayout = theme_get_setting('layouts', $theme);
  //dsm($databaseLayout);
  // merge the defaults from the JSON file and the settings in the DB
  $layouts = array_replace_recursive($layoutJson, $databaseLayout);
  //dsm($layouts);
  
  $newJson = omega_json_get($layouts);
  //dsm($newJson);
  
  // pull an array of "region groups" based on the "all" media query that should always be present
  //$region_groups = $layouts[$defaultLayout]['all'];
  //dsm($region_groups);
  $theme_regions = $themeSettings->info['regions'];
  
  $adminCSS = drupal_get_path('theme', 'omega') . '/style/css/omega_admin.css';  
  $adminJS = drupal_get_path('theme', 'omega') . '/js/omega_admin.js';
  $form['#attached'] = array(
    'library' => array(
      array('system', 'ui.slider'),
    ),
    'js' => array(
      $adminJS,
    ),
    'css' => array(
      $adminCSS,
    ),
  );
  
  // add in custom JS for Omega administration
  //$form['#attached']['library'][] = 'omega/omega_admin';
  
  // move the default theme settings to our custom vertical tabs for core theme settings
  $form['core'] = array(
    '#type' => 'vertical_tabs',
    '#attributes' => array('class' => array('entity-meta')),
    '#weight' => 99,
  );
  
  $form['theme_settings']['#group'] = 'core';
  $form['logo']['#group'] = 'core';
  $form['favicon']['#group'] = 'core';
  
  $form['theme_settings']['#open'] = FALSE;
  $form['logo']['#open'] = FALSE;
  $form['favicon']['#open'] = FALSE;
  
  
  $toggle_omega_intro = theme_get_setting('omega_toggle_intro', $theme);  
  
  $form['welcome'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('welcome', 'omega-help')),
    '#title' => t('Welcome to Omega Five'),
    '#weight' => -1000,
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => FALSE,
  );
  
  $screenshot = $base_path . drupal_get_path('theme', 'omega') . '/screenshot.png';
  $form['welcome']['omega5'] = array(
    '#prefix' => '<div class="omega-welcome clearfix">',
    '#markup' => '<img class="screeny" src="'. $screenshot .'" />',
    '#suffix' => '</div>',
    '#weight' => -9999,
  );
  
  $form['welcome']['omega5']['#markup'] .= t('<h3>Omega Five <small>(8.x-5.x)</small></h3>');
  $form['welcome']['omega5']['#markup'] .= t('<p><strong>Project Page</strong> - <a href="http://drupal.org/project/omega" target="_blank">drupal.org/project/omega</a>');
  $form['welcome']['omega5']['#markup'] .= t('<p>Omega Five will change the way you build subthemes, and use a consistent theme structure behind all your projects for an intuitive, innovative spin on responsive layouts, design, and SASS compiling on the fly.</p>');
  $form['welcome']['omega5']['#markup'] .= t('<p>Most settings in the <strong>Omega Subtheme Generator</strong> are well documented inline. For additional information and links, visit the project page listed above.</p>');
  
  $welcome_status = $toggle_omega_intro ? TRUE : FALSE;
  $form['welcome']['omega_toggle_intro'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show this message/introduction by default'),
    '#description' => t(''),
    '#default_value' => $welcome_status,
  );
  
  // close the welcome fieldset by default if $toggle_omega_intro is not checked
  if (!$welcome_status) {
    $form['welcome']['#collapsed'] = TRUE;
  }  
  
  // Custom settings in Vertical Tabs container
  $form['omega'] = array(
    '#type' => 'vertical_tabs',
    '#attributes' => array('class' => array('entity-meta')),
    '#weight' => -999,
    '#default_tab' => 'edit-layouts',
  );
  
  // Vertical tab sections
  $form['options'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Default Options'),
    '#weight' => -999,
    '#group' => 'omega',
    '#open' => FALSE,
  );
  
  $form['styles'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('styles')),
    '#title' => t('Optional CSS/SCSS Includes'),
    '#weight' => -899,
    '#group' => 'omega',
    '#open' => FALSE,
    '#tree' => TRUE,
  );
  
  $form['layouts'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Layout Configuration'),
    '#description' => t('<p class="description">You are able to configure your layout based on the breakpoints defined in your theme .info file.</p>'),
    '#weight' => -799,
    '#group' => 'omega',
    //'#open' => TRUE,
    '#tree' => TRUE,
  );
  
  $enable_omegags_layout = theme_get_setting('enable_omegags_layout', $theme);
  $form['layouts']['enable_omegags_layout'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable the Awesome'),
    '#description' => t('Turn on the awesome Omega.gs layout management system'),
    '#default_value' => isset($enable_omegags_layout) ? $enable_omegags_layout : TRUE,
    '#weight' => -999,
    '#tree' => FALSE,
  );
  
  $form['layouts']['non_omegags_info'] = array(
    '#type' => 'item',
    '#prefix' => '',
    '#markup' => '<div class="messages warning omega-styles-info"><p>Since you have "<strong><em>disabled the Awesome</em></strong>" above, now the Omega.gs layout is not being used in your theme/subtheme. This means that you will need to provide your own layout system. Easy huh?!? Although, I would really just use the awesome...</p></div>',
    '#suffix' => '',
    '#weight' => -99,
    '#states' => array(
      'invisible' => array(
       ':input[name="enable_omegags_layout"]' => array('checked' => TRUE),
      ),
    ),
  );
  
  $breakpoints = $themeSettings->info['breakpoints'];
  $regionGroups = $themeSettings->info['region_groups'];
  
  
  //dsm($breakpoints);
  //dsm($regionGroups);
  foreach($breakpoints as $breakpointName => $breakpointMedia) {
    $form['layouts'][$defaultLayout][$breakpointName] = array(
      '#type' => 'fieldset',
      '#attributes' => array('class' => array('layout-breakpoint')),
      '#title' => $breakpointName . ' -- <small>' . $breakpointMedia . '</small>',
      //'#weight' => $breakpoint->weight,
      '#group' => 'layout',
      '#states' => array(
        'invisible' => array(
         ':input[name="enable_omegags_layout"]' => array('checked' => FALSE),
        ),
      ),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    
    foreach ($regionGroups as $groupId => $groupName ) {
      $form['layouts'][$defaultLayout][$breakpointName][$groupId] = array(
        '#type' => 'fieldset',
        '#attributes' => array(
          'class' => array(
            'layout-breakpoint-regions', 
            'clearfix'
          ),
        ),
        '#title' => 'Region Group: ' . $groupName,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );
      
      
      $possible_cols = array();
      
      for ($i = 12; $i <= 12; $i++) {
        $possible_cols[$i] = $i . '';
      }
      
      $form['layouts'][$defaultLayout][$breakpointName][$groupId]['row'] = array(
        '#prefix' => '<div class="region-group-layout-settings">',
        '#type' => 'select',
        '#attributes' => array(
          'class' => array(
            'row-column-count', 
            'clearfix'
          ),
        ),
        '#title' => 'Columns',
        '#options' => $possible_cols,
        '#default_value' => isset($layouts[$defaultLayout][$breakpointName][$groupId]['row']) ? $layouts[$defaultLayout][$breakpointName][$groupId]['row'] : '12',
        '#group' => '',
      );
      
      
      $form['layouts'][$defaultLayout][$breakpointName][$groupId]['visual_controls'] = array(
        '#prefix' => '<div class="omega-layout-controls form-item">',
        '#markup' => '<label>Show/Hide: </label><div class="clearfix"><a class="push-pull-toggle" href="#">Push/Pull</a> | <a class="prefix-suffix-toggle" href="#">Prefix/Suffix</a></div>',
        '#suffix' => '</div>',
      );
      
      $form['layouts'][$defaultLayout][$breakpointName][$groupId]['maxwidth'] = array(
        '#type' => 'textfield',
        '#size' => 3, 
        '#maxlength' => 4,
        '#attributes' => array(
          'class' => array(
            'row-max-width', 
            'clearfix'
          ),
        ),
        '#title' => 'Max-width: ',
        '#default_value' => isset($layouts[$defaultLayout][$breakpointName][$groupId]['maxwidth']) ? $layouts[$defaultLayout][$breakpointName][$groupId]['maxwidth'] : '100',
        '#group' => '',
      );
      
      $form['layouts'][$defaultLayout][$breakpointName][$groupId]['maxwidth_type'] = array(
        '#type' => 'radios',
        '#attributes' => array(
          'class' => array(
            'row-max-width-type', 
            'clearfix'
          ),
        ),
        '#options' => array(
          'percent' => '%',
          'pixel' => 'px'
        ),
        '#title' => 'Max-width type',
        '#default_value' => isset($layouts[$defaultLayout][$breakpointName][$groupId]['maxwidth_type']) ? $layouts[$defaultLayout][$breakpointName][$groupId]['maxwidth_type'] : 'percent',
        '#group' => '',
        '#suffix' => '</div>',
      );
      
      
      
      
      
      
      
      // get columns for this region group
      $available_cols = array();
      for ($i = 0; $i <= $layouts[$defaultLayout][$breakpointName][$groupId]['row']; $i++) {
        $available_cols[$i] = $i . '';
      }
      //dsm($groupId);
      //dsm($layouts[$defaultLayout][$breakpointName][$groupId]['regions']);
      
      foreach($layouts[$defaultLayout][$breakpointName][$groupId]['regions'] as $rid => $data) {
        //dsm($rid);
        
        $thisRegion = $layouts[$defaultLayout][$breakpointName][$groupId]['regions'][$rid];

        $current_push = $thisRegion['push'];
        $current_prefix = $thisRegion['prefix'];
        $current_width = $thisRegion['width'];
        $current_suffix = $thisRegion['suffix'];
        $current_pull = $thisRegion['pull'];
        
        
        $regionTitle = isset($theme_regions[$rid]) ? $theme_regions[$rid] : $rid;
         
         $form['layouts'][$defaultLayout][$breakpointName][$groupId]['regions'][$rid] = array(
           '#type' => 'fieldset',
           //'#prefix' => '<div class="region-container-box clearfix" data-omega-row="'. $info['row'] .'">',
           //'#suffix' => '</div>',
           '#title' => t($regionTitle),
           //'#description' => t('This is my description'),
           '#attributes' => array(
             'class' => array(
               'region-settings',
               'clearfix',
              ),
              'data-omega-push' => $current_push,
              'data-omega-prefix' => $current_prefix,
              'data-omega-width' => $current_width,
              'data-omega-suffix' => $current_suffix,
              'data-omega-pull' => $current_pull,
              //'data-omega-region-title' => $theme_regions[$rid],
            ),
            '#collapsible' => FALSE,
            '#collapsed' => FALSE,
            //'#group' => $gid . '-wrapper',
         );
         // push (in columns)
         $form['layouts'][$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['push'] = array(
           '#type' => 'select',
           '#attributes' => array(
             'class' => array(
               'push-controller'
             ),
           ),
           '#title' => 'Push',
           '#options' => $available_cols,
           '#default_value' => $current_push,
         );
         // prefix (in columns)
         $form['layouts'][$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['prefix'] = array(
           '#type' => 'select',
           '#attributes' => array(
             'class' => array(
               'prefix-controller'
             ),
           ),
           '#title' => 'Prefix',
           '#options' => $available_cols,
           '#default_value' => $current_prefix,
         );
         // width (in columns)
         $form['layouts'][$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['width'] = array(
           '#type' => 'select',
           '#attributes' => array(
             'class' => array(
               'width-controller'
             ),
           ),
           '#title' => 'Width',
           '#options' => $available_cols,
           '#default_value' => $current_width,
         );
         // suffix (in columns)
         $form['layouts'][$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['suffix'] = array(
           '#type' => 'select',
           '#attributes' => array(
             'class' => array(
               'suffix-controller'
             ),
           ),
           '#title' => 'Suffix',
           '#options' => $available_cols,
           '#default_value' => $current_suffix,
         );
         // pull (in columns)
         $form['layouts'][$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['pull'] = array(
           '#type' => 'select',
           '#attributes' => array(
             'class' => array(
               'pull-controller'
             ),
           ),
           '#title' => 'Pull',
           '#options' => $available_cols,
           '#default_value' => $current_pull,
         );
      }

      
      
    }
  }
  
  
  
  
  $form['debug'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Debugging & Development'),
    '#weight' => -699,
    '#group' => 'omega',
    //'#open' => TRUE,
  );

  $form['debug']['block_demo_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable region demo mode <small>(global setting)</small>'),
    '#description' => t('Display demonstration blocks in each theme region to aid in theme development and configuration. When this setting is enabled, ALL site visitors will see the demo blocks. <br /><strong>This should never be enabled on a live site.</strong>'),
    '#default_value' => theme_get_setting('block_demo_mode', $theme),
  );
  
  $form['debug']['screen_demo_indicator'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable screen size indicator <small>(global setting)</small>'),
    '#description' => t('Display data about the screen size, current media query, etc. When this setting is enabled, ALL site visitors will see the overlay data. <br /><strong>This should never be enabled on a live site.</strong>'),
    '#default_value' => theme_get_setting('screen_demo_indicator', $theme),
  );
  
   $form['export'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('export')),
    '#title' => t('Export Layouts'),
    '#weight' => 999,
    '#group' => 'omega',
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  
  $form['export']['export_info'] = array(
    '#prefix' => '<div class="messages warning omega-export-info">',
    '#markup' => '',
    '#suffix' => '</div>',
    '#weight' => -9999,
  );
  $form['export']['export_info']['#markup'] .= '<p><strong>WARNING:</strong> The export settings for this form are only currently placeholder fields. This functionality will be completed soon.</p>';
  
  // suffix (in columns)
  $form['export']['json'] = array(
    '#type' => 'textarea',
      '#attributes' => array(
        'class' => array(
          'json-layout-export'
        ),
      ),
    '#title' => 'Export JSON Layout Data',
    '#description' => 'You can copy/paste this data.',
    '#default_value' => $newJson,
  );
  
  
  
  
  
  $form['actions']['#type'] = 'actions';
  $form['actions']['save_layout'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration & layout'),
    '#weight' => 10,
    '#submit' => array('system_theme_settings_submit', 'omega_theme_settings_submit'),
    '#validate' => array('system_theme_settings_validate', 'omega_theme_settings_validate'),
  );
  
  //$form['#submit'] = array('system_theme_settings_submit');
  //$form['#validate'] = array('system_theme_settings_validate');
  
  
  //dsm($form_state);
  // push in the default functionality of theme settings form
  /*
$form['actions']['submit']['#value'] = t('Save Settings');
  $form['actions']['submit']['#states'] = array(
    // Hide the submit buttons appropriately
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => TRUE),
    ),
  );
  //$defaultSubmit = $form_state['build_info']['callback_object'];
  
  $form['actions']['submit_layout'] = $form['actions']['submit'];
  $form['actions']['submit_layout']['#value'] = t('Save Settings & Layout');
  //$form['actions']['submit_layout']['#submit'][] = array($defaultSubmit, 'submitForm');
  // add in custom submit handler
  //$form['actions']['submit_layout']['#submit'][] = 'omega_theme_settings_submit';
  
  
  $form['actions']['submit_layout']['#states'] = array(
    'visible' => array(
     ':input[name="enable_omegags_layout"]' => array('checked' => TRUE),
     ':input[name="export_new_subtheme"]' => array('checked' => FALSE),     
    ),
  );
*/
  
  
  /*
$form['actions']['generate_subtheme'] = $form['actions']['submit'];
  $form['actions']['generate_subtheme']['#value'] = t('Export as Subtheme');
  
  $form['actions']['generate_subtheme']['#submit'] = array('omega_theme_generate_submit');
  $form['actions']['generate_subtheme']['#validate'] = array('omega_theme_generate_validate');
  
  // show export only when appropriate
  $form['actions']['generate_subtheme']['#states'] = array(
    // Hide the submit buttons appropriately
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => FALSE),
    ),
  );
*/
  
  //dsm($form);
  //dsm($form_state);
}


function omega_theme_settings_validate(&$form, &$form_state) {
  //dsm('Running omega_theme_settings_validate(&$form, &$form_state)');
}

function omega_theme_settings_submit(&$form, &$form_state) {
  
  //dsm('Running omega_theme_settings_submit(&$form, &$form_state)');
  // Get the theme name.
  $theme = $form_state['build_info']['args'][0];
  
  $values = $form_state['values'];
  $layout = $values['layouts'];
  //dsm($layout);
  // Options for phpsass compiler. Defaults in SassParser.php
  $options = array(
    'style' => 'nested',
    'cache' => FALSE,
    'syntax' => 'scss',
    'debug' => TRUE,
    //'debug_info' => $debug,
    //'load_paths' => array(dirname($file['data'])),
    //'filename' => $file['data'],
    //'load_path_functions' => array('sassy_load_callback'),
    //'functions' => sassy_get_functions(),
    'callbacks' => array(
      //'warn' => $watchdog ? 'sassy_watchdog_warn' : NULL,
      //'debug' => $watchdog ? 'sassy_watchdog_debug' : NULL,
    ),
  );
  
  // Execute the compiler.
  
  
  //$parser = new SassParser($options);
  // create CSS from SCSS
  $scss = _omega_compile_layout_sass($layout, $theme, $options);
  //dsm($scss);

  $css = _omega_render_layout_css($scss, $options);
  //dsm($css);
  
  _omega_save_layout_files($scss, $css, $theme);
  //dsm($form_state['values']);
}