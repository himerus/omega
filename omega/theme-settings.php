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

//use Drupal\responsive_image\Entity\ResponsiveImageMapping;

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
  global $base_path;
  // Get the theme name.
  $theme = $form_state['build_info']['args'][0];
  // get a list of themes
  $themes = \Drupal::service('theme_handler')->listInfo();
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
  
  // add in custom JS for Omega administration
  $form['#attached']['library'][] = 'omega/omega_admin';
  
  
  $toggle_omega_intro = theme_get_setting('omega_toggle_intro', $theme);
  //dsm($toggle_omega_intro);
  
  $welcome_status = $toggle_omega_intro ? $toggle_omega_intro : FALSE;
  
  
  // form a tree of variables that represent SCSS variables in vars.scss to be altered
  $form['welcome'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('welcome', 'omega-help')),
    '#title' => t('Welcome to Omega Five'),
    '#weight' => -1000,
    '#open' => $welcome_status,
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
  
  
  //krumo($toggle_omega_intro);
  $form['omega_toggle_intro'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show this message/introduction by default'),
    '#description' => t(''),
    '#default_value' => $welcome_status,
    '#group' => 'welcome',
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
  
  $form['theme_settings']['#open'] = FALSE;
  $form['logo']['#open'] = FALSE;
  $form['favicon']['#open'] = FALSE;
  //dsm($form);
  if ($theme == 'omega') {
    //unset($form['core'], $form['theme_settings'], $form['logo'], $form['favicon']);
    $form['core']['#access'] = FALSE;
    $form['theme_settings']['#access'] = FALSE;
    $form['logo']['#access'] = FALSE;
    $form['favicon']['#access'] = FALSE;
  }
  
  // Vertical tab sections
  $form['options'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Default Options'),
    '#weight' => -999,
    '#group' => 'omega',
    '#open' => FALSE,
  );
  
  /*
$enable_advanced_layout_controls = theme_get_setting('enable_advanced_layout_controls', $theme);
  $form['enable_advanced_layout_controls'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Advanced Layout Controls (BETA)'),
    '#description' => t('This feature will enable various advanced features in the theme settings form like jQuery UI Sliders for adjusting width, prefix, suffix on regions, etc. The advanced features can be problematic on mobile or smaller screens. Turning this feature off will revert to the simplest form.'),
    '#default_value' => isset($enable_advanced_layout_controls) ? $enable_advanced_layout_controls : TRUE,
    '#group' => 'options',
  );
*/
  
  $enable_backups = theme_get_setting('enable_backups', $theme);
  $form['enable_backups'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Backups (BETA)'),
    '#description' => t('Since this form has the ability to regenerate SCSS and CSS files on the fly, turning on this backup feature will create a copy of layout.scss, layout.css, and THEME.settings.yml file before overwriting any data. These backups will be stored in the default files directory under <em>public://omega/layout/backups</em>.'),
    '#default_value' => isset($enable_backups) ? $enable_backups : TRUE,
    '#group' => 'options',
  );
  $force_subtheme_creation = theme_get_setting('force_subtheme_creation', $theme);
  $form['force_subtheme_creation'] = array(
    '#type' => 'checkbox',
    '#title' => t('Force Subtheme Creation (ALPHA)'),
    '#description' => t('Enabling this feature will "lock" this form from being saved and force the user to download a subtheme instead. The idea here is that essentially everything besides the layout options/changes should still be saved. Things like options on this section, or debugging options should still be saved and edited as expected. The layout changes are the portion that should be forced to another subtheme by this feature. (This seems like a good idea, but I dunno)'),
    '#default_value' => isset($force_subtheme_creation) ? $force_subtheme_creation : FALSE,
    '#group' => 'options',
  );
  
  $enable_omega_badge = theme_get_setting('enable_omega_badge', $theme);
  $form['enable_omega_badge'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable the "I Heart Omega 5" link'),
    '#description' => t('This feature will add an awesome little link that proudly shows your support for <a href="http://drupal.org/project/omega">Omega</a> and links to the project page. It will look for common locations like "Footer Links" or the "Powered by Drupal" block to place a link/graphic.'),
    '#default_value' => isset($enable_omega_badge) ? $enable_omega_badge : TRUE,
    '#group' => 'options',
  );
  
  /*
$silence_omegaui_warning = theme_get_setting('silence_omegaui_warning', $theme);
  $form['silence_omegaui_warning'] = array(
    '#type' => 'checkbox',
    '#title' => t('Silence the Omega UI Warning'),
    '#description' => t('This will turn off the drupal_set_message() warning about not having Omega UI installed.'),
    '#default_value' => $silence_omegaui_warning ? $silence_omegaui_warning : FALSE,
    '#group' => 'options',
  );

  
  if (!module_exists('omega_ui') && !$silence_omegaui_warning) {
    drupal_set_message('The <a href="http://drupal.org/project/omega_ui" target="_blank"><strong>Omega UI</strong></a> module is not installed. This module would make your life much cooler. You can ignore this warning in <strong>Default Options</strong>', 'warning');
  }
  
*/  
  $form['styles'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('styles')),
    '#title' => t('Optional CSS/SCSS Includes'),
    '#weight' => -899,
    '#group' => 'omega',
    '#open' => FALSE,
    '#tree' => TRUE,
  );
  $form['styles']['styles_info'] = array(
    '#prefix' => '<div class="messages messages--warning omega-styles-info">',
    '#markup' => '',
    '#suffix' => '</div>',
    '#weight' => -9999,
  );
  
  $form['styles']['styles_info']['#markup'] .= '<p>By selecting or unselecting styles in this section, you can greatly alter the visual appearance of your site.</p>';
  $toggleCSS = _omega_optional_css($theme);
  //dsm($toggleCSS);
  $form['styles']['styles_toggle'] = array(
    //'#prefix' => '<div class="messages messages--warning omega-styles-info">',
    '#markup' => '<p><a href="#" class="toggle-styles-on">Select All</a> | <a href="#" class="toggle-styles-off">Select None</a></p>',
    //'#suffix' => '</div>',
    '#weight' => -999,
  );
  
  foreach($toggleCSS as $id => $data) {
    $form['styles'][$id] = array(
      '#type' => 'checkbox',
      '#title' => t($data['title'] . ' <small>(' . $data['file'] . ')</small>'),
      '#description' => t($data['description']),
      '#default_value' => $data['status'],
      '#group' => 'styles',
      
    );
  }
  
  // form a tree of variables that represent SCSS variables in vars.scss to be altered
  $form['variables'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('variables')),
    '#title' => t('SCSS Variables'),
    '#weight' => -898,
    '#group' => 'omega',
    '#open' => FALSE,
    '#tree' => TRUE,
  );
  
  $form['variables']['variables_status'] = array(
    '#prefix' => '<div class="messages messages--error omega-variables-info">',
    '#markup' => '',
    '#suffix' => '</div>',
    '#weight' => -9999,
  );
  
  $form['variables']['variables_status']['#markup'] .= '<p><strong>NOTICE:</strong>While the settings in this form are saved, the functionality is not currently tied into regenerating the SCSS and CSS files. This will be completed soon.</p>';
  
  
  $form['variables']['variables_info'] = array(
    '#prefix' => '<div class="messages messages--warning omega-variables-info">',
    '#markup' => '',
    '#suffix' => '</div>',
    '#weight' => -9999,
  );
  
  $form['variables']['variables_info']['#markup'] .= '<p><strong>The variables represented here are SCSS variables</strong>. When they are changed, the theme must be recompiled and appropriate SCSS and CSS files overwritten.</p>';
  $form['variables']['variables_info']['#markup'] .= '<p class="description"><em>While this is ridiculously cool, use at your own risk as it IS modifying files in your theme directly. I can only be responsible for so much... <strong>WHEN IN DOUBT, MAKE A BACKUP!!!</strong></em> <strong>\o/</strong></p>';
  
  $form['variables']['colors'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('variables colors')),
    '#title' => t('Color Variables'),
    '#weight' => -999,
    //'#group' => 'omega',
    '#open' => TRUE,
  );
  
  $variables = theme_get_setting('variables', $theme);
  //dsm($variables);
  $primaryColor1 = isset($variables['colors']['primaryColor1']) ? $variables['colors']['primaryColor1'] : '3a3a3a';
  $form['variables']['colors']['primaryColor1'] = array(
    '#type' => 'textfield',
    '#size' => 5, 
    '#maxlength' => 6,
    '#attributes' => array(
      'class' => array(
        'primaryColor1',
        'primary-color',
        'color-slider',
        'clearfix'
      ),
      'data-original-color-value' => $primaryColor1,
    ),
    '#title' => t('Primary Color 1'),
    '#default_value' => $primaryColor1,
  );
  
  $primaryColor2 = isset($variables['colors']['primaryColor2']) ? $variables['colors']['primaryColor2'] : '5a5a5a';
  $form['variables']['colors']['primaryColor2'] = array(
    '#type' => 'textfield',
    '#size' => 5, 
    '#maxlength' => 6,
    '#attributes' => array(
      'class' => array(
        'primaryColor2',
        'primary-color',
        'color-slider',
        'clearfix'
      ),
      'data-original-color-value' => $primaryColor2,
    ),
    '#title' => t('Primary Color 2'),
    '#default_value' => $primaryColor2,
  );
  
  $primaryColor3 = isset($variables['colors']['primaryColor3']) ? $variables['colors']['primaryColor3'] : 'CCCCCC';
  $form['variables']['colors']['primaryColor3'] = array(
    '#type' => 'textfield',
    '#size' => 5, 
    '#maxlength' => 6,
    '#attributes' => array(
      'class' => array(
        'primaryColor3',
        'primary-color',
        'color-slider',
        'clearfix'
      ),
      'data-original-color-value' => $primaryColor3,
    ),
    '#title' => t('Primary Color 3'),
    '#default_value' => $primaryColor3,
  );
  
  $primaryColor4 = isset($variables['colors']['primaryColor4']) ? $variables['colors']['primaryColor4'] : '1a1a1a';
  $form['variables']['colors']['primaryColor4'] = array(
    '#type' => 'textfield',
    '#size' => 5, 
    '#maxlength' => 6,
    '#attributes' => array(
      'class' => array(
        'primaryColor4',
        'primary-color',
        'color-slider',
        'clearfix'
      ),
      'data-original-color-value' => $primaryColor4,
    ),
    '#title' => t('Primary Color 4'),
    '#default_value' => $primaryColor4,
  );
  
  $primaryColor5 = isset($variables['colors']['primaryColor5']) ? $variables['colors']['primaryColor5'] : '9a9a9a';
  $form['variables']['colors']['primaryColor5'] = array(
    '#type' => 'textfield',
    '#size' => 5, 
    '#maxlength' => 6,
    '#attributes' => array(
      'class' => array(
        'primaryColor5',
        'primary-color',
        'color-slider',
        'clearfix'
      ),
      'data-original-color-value' => $primaryColor5,
    ),
    '#title' => t('Primary Color 5'),
    '#default_value' => $primaryColor5,
  );
  
  
  
  $form['variables']['fonts'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('variables', 'fonts', 'clearfix')),
    '#title' => t('Font Variables'),
    '#weight' => -899,
    //'#group' => 'omega',
    '#open' => TRUE,
  );
  
  $form['variables']['fonts']['fontParagraph'] = array(
    '#prefix' => '<div class="sample-font-content clearfix">',
    '#markup' => '<h2>Bacon ipsum dolor sit amet  </h2>',
    '#suffix' => '</div>',
    '#weight' => 999,
  );
  
  $form['variables']['fonts']['fontParagraph']['#markup'] .= '<p>Drumstick ham hock tri-tip meatloaf tongue, ball tip pork chop tenderloin. Meatball prosciutto ham hock, flank pork chop swine turducken boudin tenderloin. Leberkas spare ribs tenderloin, sirloin beef ham hock short ribs tri-tip corned beef shoulder ham biltong doner. Chicken turkey short loin hamburger, doner strip steak t-bone salami tongue frankfurter. Pork fatback jowl tri-tip pastrami spare ribs. Pork loin brisket prosciutto short loin swine bresaola leberkas meatball t-bone strip steak porchetta pork belly ball tip. Beef spare ribs short loin pig ground round corned beef.</p>';
  $form['variables']['fonts']['fontParagraph']['#markup'] .= '<p>Tri-tip ribeye flank biltong strip steak. Doner leberkas meatball short ribs salami. Shank pancetta pork belly ground round cow meatloaf short loin t-bone. Shankle turducken strip steak corned beef prosciutto. Hamburger pancetta beef ribs jerky flank fatback short ribs bacon drumstick porchetta.</p>';
  
  $fontStyles = array(
    'georgia' => 'Georgia',
    'times' => 'Times',
    'palatino' => 'Palatino',
    'arial' => 'Arial',
    'helvetica' => 'Helvetica Neue',
    'arialBlack' => 'Arial Black',
    'comicSans' => 'Comic Sans (Woot!!)',
    'impact' => 'Impact',
    'lucidaSans' => 'Lucida Sans',
    'tahoma' => 'Tahoma',
    'trebuchet' => 'Trebuchet MS',
    'verdana' => 'Verdana',
    'courier' => 'Courier New',
    'lucidaConsole' => 'Lucida Console',
  );
  
  $fontStyleValues = array(
    'georgia' => 'Georgia, serif',
    'times' => '"Times New Roman", Times, serif',
    'palatino' => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
    'arial' => 'Arial, Helvetica, sans-serif',
    'helvetica' => '"Helvetica Neue", Helvetica, Arial, sans-serif',
    'arialBlack' => '"Arial Black", Gadget, sans-serif',
    'comicSans' => '"Comic Sans MS", cursive, sans-serif',
    'impact' => 'Impact, Charcoal, sans-serif',
    'lucidaSans' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
    'tahoma' => 'Tahoma, Geneva, sans-serif',
    'trebuchet' => '"Trebuchet MS", Helvetica, sans-serif',
    'verdana' => 'Verdana, Geneva, sans-serif',
    'courier' => '"Courier New", Courier, monospace',
    'lucidaConsole' => '"Lucida Console", Monaco, monospace',
  );
  
  $defaultHeaderFont = isset($variables['fonts']['defaultHeaderFont']) ? $variables['fonts']['defaultHeaderFont'] : 'georgia';
  $form['variables']['fonts']['defaultHeaderFont'] = array(
    '#type' => 'select',
    '#attributes' => array(
      'class' => array(
        'font-variable', 
        'clearfix'
      ),
    ),
    '#title' => 'Default Header Font',
    '#options' => $fontStyles,
    '#default_value' => $defaultHeaderFont,
  );
  
  $defaultBodyFont = isset($variables['fonts']['defaultBodyFont']) ? $variables['fonts']['defaultBodyFont'] : 'helvetica';
  $form['variables']['fonts']['defaultBodyFont'] = array(
    '#type' => 'select',
    '#attributes' => array(
      'class' => array(
        'font-variable', 
        'clearfix'
      ),
    ),
    '#title' => 'Default Body Font',
    '#options' => $fontStyles,
    '#default_value' => $defaultBodyFont,
  );
  
  $form['variables']['breakpoints'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('variables breakpoints')),
    '#title' => t('Breakpoint Variables'),
    '#weight' => -898,
    //'#group' => 'omega',
    '#open' => TRUE,
  );
  
  $form['variables']['breakpoints']['breakpointInfo'] = array(
    '#prefix' => '<div class="messages messages--warning breakpoint-info clearfix">',
    '#markup' => '',
    '#suffix' => '</div>',
    '#weight' => -9999,
  );
  $form['variables']['breakpoints']['breakpointInfo']['#markup'] .= t('<h3>Information about SCSS Breakpoints</h3>');
  $form['variables']['breakpoints']['breakpointInfo']['#markup'] .= t('<p>The SCSS/CSS Breakpoints are setup to be the same as the breakpoints defined in breakpoint.breakpoint_group.theme.THEME.THEME.yml. <em>However</em>, it is possible to configure these to be different if a need arises.</p>');
  $form['variables']['breakpoints']['breakpointInfo']['#markup'] .= t('<p>Essentially, the Drupal defined breakpoints are used in the layout.scss/css and the layout switching. The SCSS Breakpoint variables defined here, will apply to the way CSS is applied throughout pre-configured style rules. Any changes to these breakpoints that do not match with your layout breakpoints could cause unexpected behavior. <strong>Edit these at your own risk...</strong>.</p>');
  
  $form['variables']['elements'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('variables elements')),
    '#title' => t('Various Element Variables'),
    '#weight' => -799,
    //'#group' => 'omega',
    '#open' => TRUE,
  );
  
  
  $form['debug'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Debugging & Development'),
    '#weight' => -699,
    '#group' => 'omega',
    //'#open' => TRUE,
  );

  $form['block_demo_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable region demo mode <small>(global setting)</small>'),
    '#description' => t('Display demonstration blocks in each theme region to aid in theme development and configuration. When this setting is enabled, ALL site visitors will see the demo blocks. <br /><strong>This should never be enabled on a live site.</strong>'),
    '#default_value' => theme_get_setting('block_demo_mode', $theme),
    '#group' => 'debug',
  );
  
  $form['screen_demo_indicator'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable screen size indicator <small>(global setting)</small>'),
    '#description' => t('Display data about the screen size, current media query, etc. When this setting is enabled, ALL site visitors will see the overlay data. <br /><strong>This should never be enabled on a live site.</strong>'),
    '#default_value' => theme_get_setting('screen_demo_indicator', $theme),
    '#group' => 'debug',
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
  
  $enable_omegags_layout = theme_get_setting('enable_omegags_layout', $theme);
  $form['enable_omegags_layout'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable the Awesome'),
    '#description' => t('Turn on the awesome Omega.gs layout management system'),
    '#default_value' => isset($enable_omegags_layout) ? $enable_omegags_layout : TRUE,
    '#group' => 'layouts',
    '#weight' => -999,
  );
  
  $form['layouts']['non_omegags_info'] = array(
    '#type' => 'item',
    '#prefix' => '',
    '#markup' => '<div class="messages messages--warning omega-styles-info"><p>Since you have "<strong><em>disabled the Awesome</em></strong>" above, now the Omega.gs layout is not being used in your theme/subtheme. This means that you will need to provide your own layout system. Easy huh?!? Although, I would really just use the awesome...</p></div>',
    '#suffix' => '',
    '#weight' => -99,
    '#states' => array(
      
      'visible' => array(
       ':input[name="enable_omegags_layout"]' => array('checked' => FALSE),
      ),
    ),
  );
  
  // foreach breakpoint we have, we will create a form element group and appropriate settings for region layouts per breakpoint.
  foreach($breakpoints as $breakpoint) {
    $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name] = array(
      '#type' => 'details',
      '#attributes' => array('class' => array('layout-breakpoint')),
      '#title' => $breakpoint->name . ' -- <small>' . $breakpoint->mediaQuery . '</small>',
      '#weight' => $breakpoint->weight,
      '#group' => 'layout',
      '#states' => array(
        'invisible' => array(
         ':input[name="enable_omegags_layout"]' => array('checked' => FALSE),
        ),
      ),
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
        '#open' => TRUE,
      );
      
      
      $possible_cols = array();
      
      for ($i = 12; $i <= 12; $i++) {
        $possible_cols[$i] = $i . '';
      }
      
      /*
$form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['data'] = array(
        '#type' => 'details',
        '#attributes' => array(
          'class' => array(
            'layout-breakpoint-settings', 
            'clearfix',
          ),
        ),
        '#title' => 'Settings for: ' . $gid,
        '#open' => TRUE,
        
      );
      
      
*/
      //dsm($info);
      $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['row'] = array(
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
        '#default_value' => isset($layouts[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['row']) ? $layouts[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['row'] : '12',
        '#group' => '',
      );
      
      
      $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['visual_controls'] = array(
        '#prefix' => '<div class="omega-layout-controls form-item">',
        '#markup' => '<label>Show/Hide: </label><div class="clearfix"><a class="push-pull-toggle" href="#">Push/Pull</a> | <a class="prefix-suffix-toggle" href="#">Prefix/Suffix</a></div>',
        '#suffix' => '</div>',
      );
      
      $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['maxwidth'] = array(
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
        '#default_value' => isset($layouts[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['maxwidth']) ? $layouts[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['maxwidth'] : '100',
        '#group' => '',
      );
      
      $form['layouts'][$defaultLayout]['region_groups'][$breakpoint->name][$gid]['maxwidth_type'] = array(
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
        '#default_value' => isset($layouts[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['maxwidth_type']) ? $layouts[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['maxwidth_type'] : 'percent',
        '#group' => '',
        '#suffix' => '</div>',
      );
      
      
      
      
      
      
      
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
  
  $form['export'] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('debug')),
    '#title' => t('Export & Subtheme Generator'),
    '#weight' => 999,
    '#group' => 'omega',
    //'#open' => TRUE,
  );
  $form['export']['export_info'] = array(
    '#prefix' => '<div class="messages messages--error omega-export-info">',
    '#markup' => '',
    '#suffix' => '</div>',
    '#weight' => -9999,
  );
  
  $form['export']['export_info']['#markup'] .= '<p><strong>WARNING:</strong> The export settings for this form are only currently placeholder fields. This functionality will be completed soon.</p>';
  
  $form['export']['export_new_subtheme'] = array(
    '#type' => 'checkbox',
    '#title' => t('Export settings changes as a new subtheme'),
    '#description' => t('This will not save changes to this current theme, rather export the updated settings as a new subtheme to be used and customized.'),
    '#default_value' => 0,
  );
  
  
  /*
$js_settings = array(
    'type' => 'setting',
    'data' => array(
      'machineName' => array(
        '#' . $source['#id'] => $element['#machine_name'],
      ),
      'langcode' => $language->id,
    ),
  );
  $form['#attached']['library'][] = 'core/drupal.machine-name';
  $form['#attached']['js'][] = $js_settings;
*/
  
  
  
  $form['export']['export_name'] = array(
    '#type' => 'machine_name',
    '#maxlength' => 55,
    '#title' => t('Theme Name'),
    '#description' => t(''),
    '#default_value' => '',
    '#required' => false,
    
    '#machine_name' => array(

      'exists' => 'omega_theme_exists',

      'source' => array('title'),

      'label' => t('Theme Machine Name'),

      'replace_pattern' => '[^a-z0-9-]+',

      'replace' => '-',

    ),
    
    '#states' => array(
      'invisible' => array(
       ':input[name="export_new_subtheme"]' => array('checked' => FALSE),
      ),
    ),
  );
  
  $form['export']['export_description'] = array(
    '#type' => 'textfield',
    '#title' => t('Description'),
    '#description' => t('Enter a short description to describe the newly exported subtheme. This appears only in the administrative interface.'),
    '#default_value' => '',
    '#states' => array(
      'invisible' => array(
       ':input[name="export_new_subtheme"]' => array('checked' => FALSE),
      ),
    ),
  );
  
  $form['export']['export_version'] = array(
    '#type' => 'textfield',
    '#title' => t('Version'),
    '#description' => t(''),
    '#default_value' => '8.x-5.x',
    '#states' => array(
      'invisible' => array(
       ':input[name="export_new_subtheme"]' => array('checked' => FALSE),
      ),
    ),
  );
  
  
  // push in the default functionality of theme settings form
  $form['actions']['submit']['#value'] = t('Save Settings');
  $form['actions']['submit']['#states'] = array(
    // Hide the submit buttons appropriately
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => TRUE),
    ),
  );
  $defaultSubmit = $form_state['build_info']['callback_object'];
  $form['actions']['submit_layout'] = $form['actions']['submit'];
  $form['actions']['submit_layout']['#value'] = t('Save Settings & Layout');
  $form['actions']['submit_layout']['#submit'][] = array($defaultSubmit, 'submitForm');
  // add in custom submit handler
  $form['actions']['submit_layout']['#submit'][] = 'omega_theme_settings_submit';
  
  
  $form['actions']['submit_layout']['#states'] = array(
    'visible' => array(
     ':input[name="enable_omegags_layout"]' => array('checked' => TRUE),
     ':input[name="export_new_subtheme"]' => array('checked' => FALSE),     
    ),
  );
  
  
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
  
  //dsm($form);
  //dsm($form_state);
  
  
  
  
  
  
  
}
function omega_theme_exists($machine) {
  return true;
}
function omega_theme_settings_validate(&$form, &$form_state) {
  //dsm($form);
  //dsm($form_state);
  dsm('omega_form_system_theme_settings_validate');

  //return false;
}

function omega_theme_settings_submit(&$form, &$form_state) {
  // Get the theme name.
  $theme = $form_state['build_info']['args'][0];
  
  $values = $form_state['values'];
  $layout = $values['layouts'];
  //dsm($values);
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
  //dsm($form_state['values']);
}


function omega_theme_generate_validate(&$form, &$form_state) {
  dsm('function omega_theme_generate_validate() {}');
  //dsm($form);
  //dsm($form_state['values']);
}
function omega_theme_generate_submit(&$form, &$form_state) {
  dsm('function omega_theme_generate_submit() {}');
  //dsm($form);
  //dsm($form_state['values']);
}
