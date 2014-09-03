<?php


require_once(drupal_get_path('theme', 'omega') . '/omega-functions.php');
//require_once('lib/Drupal/omega/phpsass/SassParser.php');


function omega_form_system_theme_settings_alter(&$form, &$form_state) {
  global $base_path;
  
  //require_once(drupal_get_path('module', 'system') . '/theme-settings.php');
  //form_load_include($form_state, 'inc', 'system', 'system.admin');
  //dsm($form);
  //form_load_include($form_state, 'php', 'omega', 'theme-settings');
  //require_once('theme-settings.php');
  // Get the theme name.
  $theme = $form_state['build_info']['args'][0];
  // get a list of themes
  $themes = list_themes();
  
  $themeSettings = $themes[$theme];  
  
  $cc = omega_clear_layout_cache($theme);
  //dsm($cc);
  // grab all the layout data available for this theme
  $layoutData = _omega_get_layout_json_data($theme);
  //dsm($layoutData);
  
  // add in the javascript settings array of the json data so we can 
  // manipulate the layout editor in real time
  omega_json_load_settings_array($layoutData);
  
  
  // check for ajax update of default layout, or use default theme setting
  $defaultLayout = isset($form_state['values']['default_layout']) ? $form_state['values']['default_layout'] : theme_get_setting('default_layout', $theme);
  $edit_this_layout = isset($form_state['values']['edit_this_layout']) ? $form_state['values']['edit_this_layout'] : theme_get_setting('default_layout', $theme);
  
  if (isset($form_state['values']['default_layout'])) {
    //dsm('Pulling default_layout from $form_state[values]');
  }
  
  
  // pull saved layout data from variables table
  //$databaseLayouts = variable_get('theme_' . $theme . '_layouts');
  //dsm($databaseLayouts);
  $layouts = array();
  /*
if (isset($layoutData[$defaultLayout]['data'])) {
    //$layouts[$defaultLayout] = $layoutData[$defaultLayout]['data'];
  }
  else {
    // Pull the JSON file for the current layout
    $layoutLocation = drupal_get_path('theme', $theme) . '/layouts/' . $defaultLayout . '.json';
    $layouts[$defaultLayout] = omega_json_load_layout_file($layoutLocation);
  }
*/
  $layouts = $layoutData;  
  //$layoutJson = omega_json_load_layout_file($layoutLocation);
  //dsm($layoutJson);
  
  // merge the defaults from the JSON file and the settings in the DB
  //$layouts = array_replace_recursive($layouts, $databaseLayouts);
  //$layouts = $layoutJson;
  //dsm($layouts);
  
  //$newJson = omega_json_get($layouts);
  //dsm($newJson);
  
  // pull an array of "region groups" based on the "all" media query that should always be present
  //$region_groups = $layouts[$defaultLayout]['all'];
  //dsm($region_groups);
  $theme_regions = $themeSettings->info['regions'];
  
  // Let's add in some things so that the theme settings form sucks less.
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
  
  $form['welcome']['omega5']['#markup'] .= t('<h3>Omega Five <small>(7.x-5.x)</small></h3>');
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
  
  // #states flag to indicate that Omega.gs has been enabled
  $omegaGSon = array(
    'invisible' => array(
      ':input[name="enable_omegags_layout"]' => array('checked' => FALSE),
    ),
  );
  
  // #states flag to indicate that Omega.gs has been disabled
  $omegaGSoff = array(
    'invisible' => array(
      ':input[name="enable_omegags_layout"]' => array('checked' => TRUE),
    ),
  );
  
  // Fieldset to contain form options to change layout based on various contexts.
  $form['layout-locations'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Layout Configuration'),
    '#description' => t('<p>You can select which layout is used where here. </p>'),
    '#weight' => -800,
    '#group' => 'omega',
    //'#open' => TRUE,
    '#tree' => TRUE,
  );
  $enable_omegags_layout = theme_get_setting('enable_omegags_layout', $theme);
  $form['layout-locations']['enable_omegags_layout'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Omega.gs Layout Management'),
    '#description' => t('Turn on the awesome Omega.gs layout management system'),
    '#default_value' => isset($enable_omegags_layout) ? $enable_omegags_layout : TRUE,
    '#weight' => -999,
    '#tree' => FALSE,
  );
  
  $form['layout-locations']['non_omegags_info'] = array(
    '#type' => 'item',
    '#prefix' => '',
    '#markup' => '<div class="messages warning omega-styles-info"><p>Since you have "<strong><em>disabled the Awesome</em></strong>" above, now the Omega.gs layout is not being used in your theme/subtheme. This means that you will need to provide your own layout system. Easy huh?!? Although, I would really just use the awesome...</p></div>',
    '#suffix' => '',
    '#weight' => -99,
    '#states' => $omegaGSoff,
  );
  
  $availableLayouts = _omega_layout_json_options($layoutData);
  
  $form['layout-locations']['default_layout'] = array(
    '#prefix' => '<div class="default-layout-select">',
    '#suffix' => '</div>',
    '#type' => 'select',
    '#attributes' => array(
      'class' => array(
        'layout-select', 
        'clearfix'
      ),
    ),
    '#title' => 'Select Default Layout',
    '#options' => $availableLayouts,
    '#default_value' => isset($defaultLayout) ? $defaultLayout : theme_get_setting('default_layout', $theme),
    '#tree' => FALSE,
    '#states' => $omegaGSon,
    // attempting possible jQuery intervention rather than ajax 
  );
  
  $homeLayout = isset($form_state['values']['home_layout']) ? $form_state['values']['home_layout'] : theme_get_setting('home_layout', $theme);
  $form['layout-locations']['home_layout'] = array(
    '#prefix' => '<div class="home-layout-select">',
    '#suffix' => '</div>',
    '#type' => 'select',
    '#attributes' => array(
      'class' => array(
        'layout-select', 
        'clearfix'
      ),
    ),
    '#title' => 'Homepage: Select Layout',
    '#options' => $availableLayouts,
    '#default_value' => isset($homeLayout) ? $homeLayout : theme_get_setting('default_layout', $theme),
    '#tree' => FALSE,
    '#states' => $omegaGSon,
    // attempting possible jQuery intervention rather than ajax 
  );
  
  // Show a select menu for each node type, allowing the selection
  // of an alternate layout per node type.
  
  $types = node_type_get_types();
  
  foreach ($types AS $ctype => $ctypeData) {
    $layout_name = $ctype . '_layout';
    $ctypeLayout = isset($form_state['values'][$layout_name]) ? $form_state['values'][$layout_name] : theme_get_setting($layout_name, $theme);
    
    $form['layout-locations'][$layout_name] = array(
      '#prefix' => '<div class="' . $ctype . '-layout-select">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#attributes' => array(
        'class' => array(
          'layout-select', 
          'clearfix'
        ),
      ),
      '#title' => $ctypeData->name . ': Select Layout',
      '#options' => $availableLayouts,
      '#default_value' => isset($ctypeLayout) ? $ctypeLayout : theme_get_setting('default_layout', $theme),
      '#tree' => FALSE,
      '#states' => $omegaGSon,
      // attempting possible jQuery intervention rather than ajax 
    );  
  }
  
  
  // Layout editor
  $form['layouts'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Layout Builder'),
    '#description' => t('<p class="description">You are able to configure layouts here.</p>'),
    '#weight' => -799,
    '#group' => 'omega',
    //'#open' => TRUE,
    '#tree' => TRUE,
  );
  
  
  
  $breakpoints = $themeSettings->info['breakpoints'];
  $regionGroups = $themeSettings->info['region_groups'];
  
  //dsm($layoutFiles);
  
  
  
  // the active layout we are editing.
  // this var will be unset during submit
  $form['layouts']['edit_this_layout'] = array(
    '#prefix' => '<div id="layout-editor-select">',
    '#suffix' => '</div>',
    '#type' => 'select',
    '#attributes' => array(
      'class' => array(
        'layout-select', 
        'clearfix'
      ),
    ),
    '#title' => 'Select Layout to Edit',
    '#options' => $availableLayouts,
    '#default_value' => isset($edit_this_layout) ? $edit_this_layout : theme_get_setting('default_layout', $theme),
    '#tree' => FALSE,
    '#states' => $omegaGSon,
    // attempting possible jQuery intervention rather than ajax 
  );
  
  
  
  $form['layouts']['layout-config'] = array(
    '#type' => 'fieldset',
    '#attributes' => array(
      'id' => array(
        'layout-config',
      ),
      'class' => array(
        'layout-config',
      ),
    ),
    '#prefix' => '<div id="layout-configuration-wrapper">',
    '#suffix' => '</div>',
    '#tree' => FALSE,
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
  );
  // provide some information about the layout(s)
  $form['layouts']['layout-config']['layout_info'] = array(
    '#type' => 'item',
    '#prefix' => '<div class="omega-layout-info form-item">',
    '#markup' => '<h4>Current Layout Information</h4>',
    '#suffix' => '</div>',
    '#states' => $omegaGSon,
    '#weight' => -999,
  );
  // add each "row" of data (active layout)
  $form['layouts']['layout-config']['layout_info']['#markup'] .= '<div><label>Active Default Layout: </label><span>'. $defaultLayout .'</span></div>';
  //dsm($layoutData);
  //dsm($layouts);
  foreach ($layoutData as $lid => $ldata) {
    
    $form['layouts'][$lid] = array(
        '#type' => 'fieldset',
        '#title' => $lid,
        '#prefix' => '<div id="layout-'. $lid . '-config">',
        '#suffix' => '</div>',
        //'#weight' => $breakpoint->weight,
        '#group' => 'layout-config',
        '#states' => $omegaGSon,
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#tree' => TRUE,
      );
  
    //dsm($breakpoints);
    //dsm($regionGroups);
    foreach($breakpoints as $breakpointName => $breakpointMedia) {
      $form['layouts'][$lid][$breakpointName] = array(
        '#type' => 'fieldset',
        '#attributes' => array('class' => array('layout-breakpoint')),
        '#title' => $breakpointName . ' -- <small>' . $breakpointMedia . '</small>',
        //'#weight' => $breakpoint->weight,
        //'#group' => 'layout-config',
        '#states' => $omegaGSon,
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#tree' => TRUE,
      );
      
      foreach ($regionGroups as $groupId => $groupName ) {
        $form['layouts'][$lid][$breakpointName][$groupId] = array(
          '#type' => 'fieldset',
          '#attributes' => array(
            'class' => array(
              'layout-breakpoint-regions', 
              'clearfix'
            ),
          ),
          '#title' => 'Region Group: ' . $groupName,
          '#collapsible' => TRUE,
          '#collapsed' => FALSE,
        );
        
        
        $possible_cols = array();
        
        for ($i = 12; $i <= 12; $i++) {
          $possible_cols[$i] = $i . '';
        }
        
        $form['layouts'][$lid][$breakpointName][$groupId]['row'] = array(
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
          '#default_value' => isset($layouts[$lid]['data'][$breakpointName][$groupId]['row']) ? $layouts[$lid]['data'][$breakpointName][$groupId]['row'] : '12',
          '#group' => '',
        );
        
        
        $form['layouts'][$lid][$breakpointName][$groupId]['visual_controls'] = array(
          '#prefix' => '<div class="omega-layout-controls form-item">',
          '#markup' => '<label>Show/Hide: </label><div class="clearfix"><a class="push-pull-toggle" href="#">Push/Pull</a> | <a class="prefix-suffix-toggle" href="#">Prefix/Suffix</a></div>',
          '#suffix' => '</div>',
        );
        
        $form['layouts'][$lid][$breakpointName][$groupId]['maxwidth'] = array(
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
          '#default_value' => isset($layouts[$lid]['data'][$breakpointName][$groupId]['maxwidth']) ? $layouts[$lid]['data'][$breakpointName][$groupId]['maxwidth'] : '100',
          '#group' => '',
        );
        
        $form['layouts'][$lid][$breakpointName][$groupId]['maxwidth_type'] = array(
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
          '#default_value' => isset($layouts[$lid]['data'][$breakpointName][$groupId]['maxwidth_type']) ? $layouts[$lid]['data'][$breakpointName][$groupId]['maxwidth_type'] : 'percent',
          '#group' => '',
          '#suffix' => '</div>',
        );
  
        // get columns for this region group
        $available_cols = array();
        for ($i = 0; $i <= $layouts[$lid]['data'][$breakpointName][$groupId]['row']; $i++) {
          $available_cols[$i] = $i . '';
        }
        //dsm($groupId);
        //dsm($layouts[$defaultLayout][$breakpointName][$groupId]['regions']);
        
        foreach($layouts[$lid]['data'][$breakpointName][$groupId]['regions'] as $rid => $data) {
          //dsm($rid);
          
          $thisRegion = $layouts[$lid]['data'][$breakpointName][$groupId]['regions'][$rid];
  
          $current_push = $thisRegion['push'];
          $current_prefix = $thisRegion['prefix'];
          $current_width = $thisRegion['width'];
          $current_suffix = $thisRegion['suffix'];
          $current_pull = $thisRegion['pull'];
          
          
          $regionTitle = isset($theme_regions[$rid]) ? $theme_regions[$rid] : $rid;
           
           $form['layouts'][$lid][$breakpointName][$groupId]['regions'][$rid] = array(
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
           $form['layouts'][$lid][$breakpointName][$groupId]['regions'][$rid]['push'] = array(
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
           $form['layouts'][$lid][$breakpointName][$groupId]['regions'][$rid]['prefix'] = array(
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
           $form['layouts'][$lid][$breakpointName][$groupId]['regions'][$rid]['width'] = array(
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
           $form['layouts'][$lid][$breakpointName][$groupId]['regions'][$rid]['suffix'] = array(
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
           $form['layouts'][$lid][$breakpointName][$groupId]['regions'][$rid]['pull'] = array(
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
  
  }
  
  
  
  
  
  
  
  
  
  
  
  $form['debug'] = array(
    '#type' => 'fieldset',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Debugging & Development'),
    '#weight' => -699,
    '#group' => 'omega',
    //'#open' => TRUE,
  );

  $blockdemo = isset($form_state['values']['block_demo_mode']) ? $form_state['values']['block_demo_mode'] : theme_get_setting('block_demo_mode', $theme);
  $form['debug']['block_demo_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable region demo mode <small>(global setting)</small>'),
    '#description' => t('Display demonstration blocks in each theme region to aid in theme development and configuration. When this setting is enabled, ALL site visitors will see the demo blocks. <br /><strong>This should never be enabled on a live site.</strong>'),
    '#default_value' => $blockdemo,
  );
  
  $indicator = isset($form_state['values']['screen_demo_indicator']) ? $form_state['values']['screen_demo_indicator'] : theme_get_setting('screen_demo_indicator', $theme);
  $form['debug']['screen_demo_indicator'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable screen size indicator <small>(global setting)</small>'),
    '#description' => t('Display data about the screen size, current media query, etc. When this setting is enabled, ALL site visitors will see the overlay data. <br /><strong>This should never be enabled on a live site.</strong>'),
    '#default_value' => $indicator,
  );
  
   /*
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
*/
  
  
  
  
  
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
  //dsm(debug_backtrace());
}

function omega_theme_settings_validate(&$form, &$form_state) {
  //$theme = $form_state['build_info']['args'][0];
}


/**
 * @todo
 * need to unset the $layout in theme settings variables and 
 * save ALL layout data to theme_$theme_layouts in variables table
*/

function omega_theme_settings_submit(&$form, &$form_state) {
  //dsm(debug_backtrace());
  //dsm('Running omega_theme_settings_submit(&$form, &$form_state)');
  //require_once('theme-settings.php');
  // Get the theme name.
  $theme = $form_state['build_info']['args'][0];
  
  $values = $form_state['values'];
  //dsm($values);
  
  //dsm($layout);
  // @todo
  // this will likely change as it is not currently in the theme settings form.
  // but only in the .info file settings. 
  $layoutName = isset($values['edit_this_layout']) ? $values['edit_this_layout'] : theme_get_setting('default_layout', $theme);
  $layout[$layoutName] = $values['layouts'][$layoutName];
  //dsm($layoutName);
  
  // Options for phpsass compiler. Defaults in SassParser.php
  $options = array(
    'style' => 'nested',
    'cache' => FALSE,
    'syntax' => 'scss',
    'debug' => FALSE,
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

  // create the SCSS file based on the layout configuration
  $scss   = _omega_compile_layout_sass($layout, $theme, $options);
  //dsm($scss);
  
  // create the CSS file based on the SCSS generated above
  $css    = _omega_compile_layout_css($scss, $options);
  //dsm($css);
  
  // create the JSON format of the layout array for later use
  $json   = _omega_compile_layout_json($layoutName, $layout);
  //dsm($json);
  
  // Save all the things to files
  _omega_save_layout_files($scss, $css, $json, $theme, $layoutName);
  
  variable_set('theme_' . $theme . '_layouts', $values['layouts']);

}

/**

 * Menu callback for AJAX additions. Render the new poll choices. FAIL

 */

function omega_update_layout_settings_form(&$form, &$form_state) {
  //require_once(drupal_get_path('theme', 'omega') . '/theme-settings.php');
  //$theme = $form_state['build_info']['args'][0];
  //require_once('theme-settings.php');
  //$values = $form_state['values'];
  // check for ajax update of default layout, or use default theme setting
  //$defaultLayout = isset($form_state['values']['default_layout']) ? $form_state['values']['default_layout'] : theme_get_setting('default_layout', $theme);
  //dd($form['layouts']);
  //$form['layouts']['default_layout']['#value'] = $defaultLayout;
  //$form_state['rebuild'] = TRUE;
  
  
  
  
  //form_load_include($form_state, 'inc', 'system', 'system.admin');
  
  
  
  return $form['layouts']['layout-config'];
  //return $form;
}