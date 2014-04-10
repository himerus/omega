<?php
require_once('omega-functions.php');

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;

// Include Breakpoint Functionality
use Drupal\breakpoint\Entity\Breakpoint;
use Drupal\breakpoint\Entity\BreakpointGroup;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\system\Form\ThemeSettingsForm;
use Drupal\omega\savelayout\SaveLayout;

use Drupal\responsive_image\Entity\ResponsiveImageMapping;

use Drupal\omega\phpsass\SassParser;
use Drupal\omega\phpsass\SassFile;


/**
 * Implementation of hook_form_system_theme_settings_alter()
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 *
 * @param $form_state
 *   A keyed array containing the current state of the form.
 */
function omega_form_system_theme_settings_alter(&$form, &$form_state) {
  
  // Get the theme name.
  $theme = $form_state['build_info']['args'][0];
  // get a list of themes
  $themes = list_themes();
  // get the default BreakpointGroupID
  $breakpointGroupId = _omega_getBreakpointId($theme);
  // Load the BreakpointGroup and it's Breakpoints
  $breakpointGroup = entity_load('breakpoint_group', $breakpointGroupId);
  $breakpoints = $breakpointGroup->getBreakpoints();

  $themeSettings = $themes[$theme];
  $defaultLayout = theme_get_setting('default_layout', $theme);
  $layouts = theme_get_setting('layouts', $theme);

  // pull an array of "region groups" based on the "all" media query that should always be present
  $region_groups = $layouts[$defaultLayout]['region_groups']['all'];
  //dsm($region_groups);
  $theme_regions = $themeSettings->info['regions'];
  
  $css_path = drupal_get_path('theme', 'omega') . '/style/css/omega_admin.css';
  $form['#attached']['css'][$css_path] = array(
    
  );
  
  $js_path = drupal_get_path('theme', 'omega') . '/js/omega_admin.js';
  $form['#attached']['js'][$js_path] = array(
    
  );
  
  
  // Custom settings in Vertical Tabs container
  $form['omega'] = array(
    '#type' => 'vertical_tabs',
    '#attributes' => array('class' => array('entity-meta')),
    '#weight' => -999,
    '#default_tab' => 'edit-layouts',
  );
  
  // move the default theme settings to our custom vertical tabs for core theme settings
  $form['core'] = array(
    '#type' => 'vertical_tabs',
    '#attributes' => array('class' => array('entity-meta')),
    '#weight' => -899,
  );
  $form['theme_settings']['#group'] = 'core';
  $form['logo']['#group'] = 'core';
  $form['favicon']['#group'] = 'core';
  
  if ($theme == 'omega') {
    //unset($form['core'], $form['theme_settings'], $form['logo'], $form['favicon']);
    $form['core']['#access'] = FALSE;
    $form['theme_settings']['#access'] = FALSE;
    $form['logo']['#access'] = FALSE;
    $form['favicon']['#access'] = FALSE;
  }
  
  // Vertical tab sections
  $form['default'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Default Settings'),
    '#weight' => -999,
    '#group' => 'omega',
    '#open' => TRUE,
  );  
  
  $form['styles'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('styles')),
    '#title' => t('Style Settings'),
    '#description' => t('By selecting or unselecting styles in this section, you can greatly alter the visual appearance of your site.'),
    '#weight' => -899,
    '#group' => 'omega',
    '#open' => TRUE,
  );
  
  $toggleCSS = array(
    'scss_breadcrumbs' => array(
      'title' => 'Breadcrumbs',
      'description' => 'Basic breadcrumb styling.',
      'file' => '_breadcrumbs.scss',
      'status' => theme_get_setting('scss_breadcrumbs', $theme),
    ),
    'scss_html_elements' => array(
      'title' => 'Generic HTML Elements',
      'description' => 'Provides basic styles for generic tags like &lt;a&gt;, &lt;p&gt;, &lt;h2&gt;, etc.',
      'file' => '_html-elements.scss',
      'status' => theme_get_setting('scss_html_elements', $theme),
    ),
  );
  
  foreach($toggleCSS as $id => $data) {
    $form[$id] = array(
      '#type' => 'checkbox',
      '#title' => t($data['title'] . ' <small>(' . $data['file'] . ')</small>'),
      '#description' => t($data['description']),
      '#default_value' => $data['status'],
      '#group' => 'styles',
    );
  }
  
  $form['debug'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Debugging & Development'),
    '#weight' => -699,
    '#group' => 'omega',
    //'#open' => TRUE,
  );
  
  $form['debug_region'] = array(
    '#type' => 'container',
    //'#attributes' => array('class' => array('debug')),
    //'#title' => t('Region Debugging'),
    //'#description' => t('Options in this section will help you in defining and customizing your regions during development.'),
    '#weight' => -699,
    '#group' => 'debug',
    //'#open' => TRUE,
  );
  
  
  
  $form['block_demo_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable region demo mode <small>(global setting)</small>'),
    '#description' => t('Display demonstration blocks in each theme region to aid in theme development and configuration. When this setting is enabled, ALL site visitors will see the demo blocks. <strong>This should never be enabled on a live site.</strong>'),
    '#default_value' => theme_get_setting('block_demo_mode', $theme),
    '#group' => 'debug_region',
  );
  
  $form['screen_demo_indicator'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable screen size indicator <small>(global setting)</small>'),
    '#description' => t('Display data about the screen size, current media query, etc. When this setting is enabled, ALL site visitors will see the overlay data. <strong>This should never be enabled on a live site.</strong>'),
    '#default_value' => theme_get_setting('screen_demo_indicator', $theme),
    '#group' => 'debug_region',
  );
  
  
  
  
  //dsm($breakpointGroupId);
  //dsm($breakpointGroup);
  //dsm($breakpoints);
  //dsm($form);
  
  
  $form['layouts'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Layout Configuration'),
    '#description' => t('<p class="description">You are able to configure your layout based on the breakpoints defined in <strong>(' . $breakpointGroupId . ')</strong></p>'),
    '#weight' => -799,
    '#group' => 'omega',
    //'#open' => TRUE,
    '#tree' => TRUE,
  );
  
  
  // foreach breakpoint we have, we will create a form element group and appropriate settings for region layouts per breakpoint.
  foreach($breakpoints as $breakpoint) {
    $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name] = array(
      '#type' => 'details',
      '#attributes' => array('class' => array('layout-breakpoint')),
      '#title' => $breakpoint->name . ' -- <small>' . $breakpoint->mediaQuery . '</small>',
      '#weight' => $breakpoint->weight,
      '#group' => 'layout',
      //'#open' => TRUE,
    );
    //dsm($breakpoints);
    foreach ($region_groups as $gid => $info ) {
      $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid] = array(
        '#type' => 'details',
        '#attributes' => array(
          'class' => array(
            'layout-breakpoint-regions', 
            'clearfix'
          ),
        ),
        '#title' => 'Region Group: ' . $gid,
        //'#open' => TRUE,
      );
      
      
      /*
      $possible_cols = array();
      for ($i = 0; $i <= 32; $i++) {
        $possible_cols[$i] = $i . '';
      }
      
      $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['row'] = array(
        '#type' => 'select',
        '#attributes' => array(
          'class' => array(
            'row-column-count', 
            'clearfix'
          ),
        ),
        '#title' => 'Region Group: ' . $gid,
        '#options' => $possible_cols,
        '#default_value' => $info['row'],
        //'#open' => TRUE,
      );
      */
      
      
      
      
      
      // get columns for this region group
      $available_cols = array();
      for ($i = 0; $i <= $info['row']; $i++) {
        $available_cols[$i] = $i . '';
      }
      
      
            //dsm($gid);
            //dsm($info);
      foreach($info['regions'] as $rid => $data) {
         
         // $data contains defaults from omega.info.yml
         //dsm($gid);
         //dsm($rid);
         // w/underscores
         //$pattern = 'layouts_' . $defaultLayout . '_region_groups_' . $breakpoint->name . '_' . $gid . '_regions_' . $rid . '_';
         // w/periods
         $pattern = 'layouts.' . $defaultLayout . '.region_groups.' . $breakpoint->name . '.' . $gid . '.regions.' . $rid . '.';
         $pattern_push = $pattern . 'push';
         $pattern_prefix = $pattern . 'prefix';
         $pattern_width = $pattern . 'width';
         $pattern_suffix = $pattern . 'suffix';
         $pattern_pull = $pattern . 'pull';
         
         $current_push = theme_get_setting($pattern_push, $theme) ? theme_get_setting($pattern_push, $theme) : $data['push'];
         $current_prefix = theme_get_setting($pattern_prefix, $theme) ? theme_get_setting($pattern_prefix, $theme) : $data['prefix'];
         $current_width = theme_get_setting($pattern_width, $theme) ? theme_get_setting($pattern_width, $theme) : $data['width'];
         $current_suffix = theme_get_setting($pattern_suffix, $theme) ? theme_get_setting($pattern_suffix, $theme) : $data['suffix'];
         $current_pull = theme_get_setting($pattern_pull, $theme) ? theme_get_setting($pattern_pull, $theme) : $data['pull'];
         
         //dsm($breakpoint->name);
         //dsm(theme_get_setting($pattern_width));
         //dsm(theme_get_setting('layouts.' . $defaultLayout . '.region_groups.' . $breakpoint->name));
         //dsm($pattern_width);
         //dsm(theme_get_setting($pattern_width, $theme));
         
         $regionTitle = isset($theme_regions[$rid]) ? $theme_regions[$rid] : $rid;
         
         $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid] = array(
           '#type' => 'details',
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
            '#open' => TRUE,
            //'#group' => $gid . '-wrapper',
         );
         
         
         
         // push (in columns)
         $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['push'] = array(
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
         $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['prefix'] = array(
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
         $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['width'] = array(
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
         $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['suffix'] = array(
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
         $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['pull'] = array(
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
  

  // push in the default functionality of theme settings form
  $defaultSubmit = $form_state['build_info']['callback_object'];
  $form['#submit'][] = array($defaultSubmit, 'submitForm');
  // add in custom submit handler
  $form['#submit'][] = 'omega_theme_settings_submit';
  
  
  
  
  
  
  
  
  
  
  
  
  
  
}

function omega_theme_settings_validate(&$form, &$form_state) {
  //dsm($form);
  //dsm($form_state);
  dsm('omega_form_system_theme_settings_validate');
}

function omega_theme_settings_submit(&$form, &$form_state) {
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
  $parser = new SassParser($options);
  // create CSS from SCSS
  $scss = _omega_compile_layout_sass($layout, $theme, $options);
  //dsm($scss);

  $css = _omega_render_layout_css($scss, $options);
  //dsm($css);
  
  _omega_save_layout_files($scss, $css, $theme);
    
}


