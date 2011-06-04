<?php

/**
 * @file
 * General theme settings elements.
 */

/**
 * @todo
 */
function alpha_theme_settings_general(&$form, &$form_state) {
  $theme = $form_state['build_info']['args'][0];
  $settings = $form_state['alpha_settings'];
  $scales = alpha_scale_options(1, 10, 0.5);  
  $optional = alpha_css_options($theme);
  $libraries = alpha_library_options($theme);
  $exclude = alpha_exclude_options($theme);
  $toggle = alpha_toggle();
  
  $form['alpha_settings']['layout'] = array(
    '#type' => 'fieldset',
    '#weight' => -10,
    '#title' => t('Grid settings'),
  );

  $form['alpha_settings']['layout']['alpha_grid'] = array(
    '#type' => 'select',
    '#description' => t('Select the grid system that you want to use for this layout.'),
    '#title' => t('Grid system'),
    '#default_value' => $settings['grid'],
    '#options' => alpha_grid_options($theme),
  );
  
  $form['alpha_settings']['layout']['responsive_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Responsive settings'),
  );
  
  $form['alpha_settings']['layout']['responsive_settings']['alpha_responsive'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable the responsive grid'),
    '#description' => t('Enabling this will unleash the responsive layout features that come with Alpha & Omega. It also helps you to clean your appartment and wash your dishes.'),
    '#default_value' => $settings['responsive'],
  );
  
  $form['alpha_settings']['layout']['responsive_settings']['alpha_viewport'] = array(
    '#type' => 'checkbox',
    '#title' => t('Allow customizing viewport meta properties on iOS and Android devices'),
    '#description' => t('By default, most mobile browsers choose their own way to handle page rendering by using the viewport meta tag. iOS and Android are "capable" of displaying full size websites by simply scaling down the view. However, when using a truly mobile version of your theme, you will / may want to customize these settings. You can find more information about this in the <a href="http://alpha.developmentgeeks.com">Omega documentation</a>.'),
    '#default_value' => $settings['viewport']['enabled'],
    '#states' => array(
      'visible' => array(
        ':input[name="alpha_responsive"]' => array('checked' => TRUE),
      ),
    ),
  );
  
  $form['alpha_settings']['layout']['responsive_settings']['viewport_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Viewport settings'),
    '#states' => array(
      'visible' => array(
        ':input[name="alpha_viewport"]' => array('checked' => TRUE),
        ':input[name="alpha_responsive"]' => array('checked' => TRUE),
      ),
    ),
  );
  
  $form['alpha_settings']['layout']['responsive_settings']['viewport_settings']['alpha_viewport_initial_scale'] = array(
    '#type' => 'select',
    '#title' => t('Initial scale'),
    '#default_value' => $settings['viewport']['initial'],
    '#options' => $scales,
    '#description' => t('The initial scaling of the page. This should almost always be set to 1.0.'),
  );

  $form['alpha_settings']['layout']['responsive_settings']['viewport_settings']['alpha_viewport_min_scale'] = array(
    '#type' => 'select',
    '#title' => t('Minimum scale'),
    '#default_value' => $settings['viewport']['min'],
    '#options' => $scales,
    '#description' => t('The minimum scaling of the site. This should usually be the same as your <em>Initial scale</em> setting.'),
  );

  $form['alpha_settings']['layout']['responsive_settings']['viewport_settings']['alpha_viewport_max_scale'] = array(
    '#type' => 'select',
    '#title' => t('Maximum scale'),
    '#default_value' => $settings['viewport']['max'],
    '#options' => $scales,
    '#description' => t('The maximum scaling of the site. This can be any value between 1 and 10, but should not be too big if you want to preserve your mobile look and feel.'),
  );

  $form['alpha_settings']['layout']['responsive_settings']['viewport_settings']['alpha_viewport_user_scaleable'] = array(
    '#type' => 'checkbox',
    '#title' => t('Scalable by user'),
    '#description' => t('<p>Determine if a user can resize the screen. This is usually accomplished via multi-touch gestures on iOS and Android devices. If your mobile theme is very customized and presented with good typography and graphics for a reduced mobile size, it is recommended to leave this setting unchecked. If it is left unchecked, the min-scale and max-scale properties will be ignored.</p><p class="marker">HTC Android devices do NOT (currently) respect the viewport meta tag for <em>user-scalable</em>. It will render at the appropriate <em>initial-scale</em> set above, but a user can still zoom in/out.</p>'),
    '#default_value' => $settings['viewport']['user'],
  );
    
  foreach (alpha_grids(NULL, $theme) as $grid => $info) {
    $form['alpha_settings']['layout']['grid_layouts'][$grid] = array(
      '#type' => 'fieldset',
      '#title' => t('@grid layout settings', array('@grid' => $info['name'])),
      '#states' => array(
        'visible' => array(
          ':input[name="alpha_grid"]' => array('value' => $grid),
        ),
      ),
    );
    
    $form['alpha_settings']['layout']['grid_layouts'][$grid]['alpha_primary_' . $grid] = array(
      '#type' => 'select',
      '#title' => t('Primary layout'),
      '#default_value' => $info['primary'],
      '#options' => alpha_grid_layouts_options($grid, $theme),
      '#description' => t('The primary layout will be used in case you disable the "responsive grid" option aswell as for older versions of Internet Explorer.'),
    );
    
    foreach ($info['layouts'] as $layout => $item) {
      $form['alpha_settings']['layout']['grid_layouts'][$grid][$layout] = array(
        '#type' => 'fieldset',
        '#title' => t('@layout layout', array('@layout' => $item['name'])),
        '#states' => array(
          'visible' => array(
            ':input[name="alpha_responsive"]' => array('checked' => TRUE),
          ),
        ),
      );

      $form['alpha_settings']['layout']['grid_layouts'][$grid][$layout]['layouts_' . $grid . '_' . $layout . '_responsive'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use this layout with the responsive grid'),
        '#default_value' => $item['enabled'],
      );
      
      $form['alpha_settings']['layout']['grid_layouts'][$grid][$layout]['layouts_' . $grid . '_' . $layout . '_weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight'),
        '#description' => t('The weight of this layout.'),
        '#default_value' => $item['weight'],
        '#states' => array(
          'visible' => array(
            ':input[name="layouts_' . $grid . '_' . $layout . '_responsive"]' => array('checked' => TRUE),
          ),
        ), 
      );
      
      $form['alpha_settings']['layout']['grid_layouts'][$grid][$layout]['layouts_' . $grid . '_' . $layout . '_media'] = array(
        '#type' => 'textfield',
        '#title' => t('Media query'),
        '#description' => t('The @media query that the @layout layout should respond to.', array('@layout' => $item['name'])),
        '#default_value' => $item['media'],
        '#maxlength' => 255,
        '#size' => 140,
        '#states' => array(
          'visible' => array(
            ':input[name="layouts_' . $grid . '_' . $layout . '_responsive"]' => array('checked' => TRUE),
          ),
        ),   
      );
    }
  }  
  
  if (!empty($libraries)) {    
    $form['alpha_settings']['alpha_libraries'] = array(
      '#type' => 'fieldset',
      '#title' => t('Toggle libraries'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
 
    $form['alpha_settings']['alpha_libraries']['alpha_libraries'] = array(
      '#title' => t('Enable optional libraries'),
      '#description' => t('You can choose from this list to enable optional libraries.'),
      '#type' => 'checkboxes',
      '#options' => $libraries,
      '#default_value' => $settings['libraries'],
    );
  }  
  
  $form['alpha_settings']['alpha_styles'] = array(
    '#type' => 'fieldset',
    '#title' => t('Toggle styles'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  
  if (!empty($optional)) {    
    $form['alpha_settings']['alpha_styles']['alpha_css'] = array(
      '#title' => t('Enable optional stylesheets'),
      '#description' => t('You can choose from this list to enable optional stylesheets.'),
      '#type' => 'checkboxes',
      '#options' => $optional,
      '#default_value' => $settings['css'],
    );
  }
  
  if (!empty($exclude)) {
    $form['alpha_settings']['alpha_styles']['alpha_exclude'] = array(
      '#title' => t('Disable module and theme stylesheets'),
      '#description' => t('Alpha will remove the selected stylesheets from your pages.'),
      '#type' => 'checkboxes',
      '#options' => $exclude,
      '#default_value' => $settings['exclude'],
    );
  }
 
  $form['alpha_settings']['alpha_debug'] = array(
    '#type' => 'fieldset',
    '#weight' => -6,
    '#title' => t('Debugging'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );

  if (module_exists('delta_ui') && arg(2) == 'delta') {
    $form['alpha_settings']['alpha_debug']['#description'] = t('<div class="messages warning">The debugging features are only available in the <a href="@link">actual theme settings</a> and are global.</div>', array('@link' => url('admin/appearance/settings/' . $theme)));
  }
  else {
    $form['alpha_settings']['alpha_debug']['alpha_debug_block_toggle'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable the debugging (placeholder) blocks for the selected roles.'),
      '#default_value' => $settings['debug']['block'],
    );
    
    $form['alpha_settings']['alpha_debug']['alpha_debug_grid_toggle'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable grid overlay for the selected roles.'),
      '#default_value' => $settings['debug']['grid'],
    );
  
    $form['alpha_settings']['alpha_debug']['alpha_debug_grid_roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Roles that may use the grid overlay and debugging blocks.'),
      '#default_value' => $settings['debug']['roles'],
      '#options' => array_map('check_plain', user_roles()),
    );
  }
  
  $form['alpha_settings']['alpha_toggle'] = array(
    '#type' => 'fieldset',
    '#title' => t('Toggle advanced elements'),
    '#description' => t('Enable or disable the display of certain page elements.'),
  );
  
  foreach ($toggle as $item => $title) {
    $form['alpha_settings']['alpha_toggle']['alpha_toggle_' . $item] = array(
      '#type' => 'checkbox',
      '#title' => $title,
      '#default_value' => $settings['toggle'][$item],
    );
  }

  $form['alpha_settings']['alpha_toggle']['alpha_hidden'] = array(
    '#type' => 'item',
    '#title' => t('Choose from the elements below to hide them via CSS'),
    '#description' => t('This will make the element invisible to normal users while not removing it from the HTML (e.g. for screenreaders).'),
  );
  
  foreach (alpha_visibility() as $item => $title) {
    $form['alpha_settings']['alpha_toggle']['alpha_hidden']['alpha_hidden_' . $item] = array(
      '#type' => 'checkbox',
      '#title' => $title,
      '#default_value' => $settings['hidden'][$item],
    );
  }
}