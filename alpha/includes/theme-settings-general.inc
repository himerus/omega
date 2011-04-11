<?php

/*
 * @todo
 */
function alpha_theme_settings_general(&$form, &$form_state) {
  $theme = $form_state['build_info']['args'][0];
  $scales = alpha_scale_options(1, 10, 0.5);  
  
  $form['alpha_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => -10,
    '#prefix' => t('<h3>Layout configuration</h3>'),
  );  
  
  $form['alpha_settings']['layout'] = array(
    '#type' => 'fieldset',
    '#title' => t('Grid settings'),
  );

  $form['alpha_settings']['layout']['alpha_grid'] = array(
    '#type' => 'select',
    '#description' => t('Select the grid system that you want to use for this layout.'),
    '#title' => t('Grid system'),
    '#default_value' => alpha_theme_get_setting('alpha_grid', 'default'),
    '#options' => alpha_grid_options($theme),
  );
  
  $form['alpha_settings']['layout']['alpha_responsive_grid'] = array(
    '#type' => 'radios',
    '#description' => t('<p>The responsive layout allows the grid to resize based on the size of the users device. This incorporates mobile first design, so the default behavior is to show the simplest (stacked) version of the layout, and for devices that meet certain display criteria (width), new CSS and grid sizing will be applied.</p><p class="marker">Enabling this feature will ignore the fixed / fluid setting as it currently is only available for the fixed size grid.</p>'),
    '#title' => t('Responsive grid layout'),
    '#default_value' => alpha_theme_get_setting('alpha_responsive_grid', 'disabled'),
    '#options' => array(
      'enabled' => t('Enable'),
      'disabled' => t('Disable'),
    ),
  );
  
  $form['alpha_settings']['layout']['responsive_settings'] = array(
    '#type' => 'container',
    '#states' => array(
      'visible' => array(
        ':input[name="alpha_responsive_grid"]' => array('value' => t('enabled')),
      ),
    ),
  ); 
  
  $form['alpha_settings']['layout']['responsive_settings']['alpha_responsive_mobile'] = array(
    '#type' => 'checkbox',
    '#title' => t('Mobile sizing'),
    '#description' => t('This option cannot be disabled as Omega, and all subthemes designed in a responsive manner are mobile first, then add other sizes for browsers that can support @media queries.'),
    '#default_value' => TRUE,
    '#disabled' => TRUE,
  );

  $form['alpha_settings']['layout']['responsive_settings']['alpha_responsive_narrow'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable narrow grid'),
    '#description' => t('The narrow grid is designed to work well for tablets in a portrait viewing mode.'),
    '#default_value' => alpha_theme_get_setting('alpha_responsive_narrow', FALSE),
  );
  
  $form['alpha_settings']['layout']['responsive_settings']['alpha_responsive_default'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable normal grid'),
    '#description' => t('The normal grid is designed to work well for tablets in a landscape viewing mode and most web browsers.'),
    '#default_value' => alpha_theme_get_setting('alpha_responsive_default', TRUE),
  );

  $form['alpha_settings']['layout']['responsive_settings']['alpha_responsive_wide'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable wide grid'),
    '#description' => t('The wide grid is designed to work well for users with a lot of screen real estate. However, this may not look well in a single sidebar layout. Design considerations must be made when using this layout.'),
    '#default_value' => alpha_theme_get_setting('alpha_responsive_wide', FALSE),
  );
     
  $form['alpha_settings']['layout']['responsive_settings']['alpha_customize_viewport'] = array(
    '#type' => 'radios',
    '#title' => t('Customize Viewport settings on iOS and Android devices'),
    '#description' => t('By default, most mobile browsers choose their own way to handle page rendering by using the viewport meta tag. iOS and Android are "capable" of displaying full size websites by simply scaling down the view. However, when using a truly mobile version of your theme, you will / may want to customize these settings. You can find more information about this in the <a href="http://alpha.developmentgeeks.com">Omega documentation</a>.'),
    '#default_value' => alpha_theme_get_setting('alpha_customize_viewport', 'on'),
    '#options' => array(
      'on' => t('Allow customizing viewport meta properties'),
      'off' => t('Do not modify viewport meta properties (let device choose)'),
    ),
  );
  
  $form['alpha_settings']['layout']['viewport_settings'] = array(
    '#type' => 'container',
    '#states' => array(
      'visible' => array(
        ':input[name="alpha_customize_viewport"]' => array('value' => t('on')),
        ':input[name="alpha_responsive_grid"]' => array('value' => t('enabled')),
      ),
    ),
  );
  
  $form['alpha_settings']['layout']['viewport_settings']['alpha_viewport_initial_scale'] = array(
    '#type' => 'select',
    '#title' => t('Initial scale'),
    '#default_value' => alpha_theme_get_setting('alpha_viewport_initial_scale', 1),
    '#options' => $scales,
    '#description' => t('The initial scaling of the page. This should almost always be set to 1.0.'),
  );

  $form['alpha_settings']['layout']['viewport_settings']['alpha_viewport_min_scale'] = array(
    '#type' => 'select',
    '#title' => t('Minimum scale'),
    '#default_value' => alpha_theme_get_setting('alpha_viewport_min_scale', 1),
    '#options' => $scales,
    '#description' => t('The minimum scaling of the site. This should usually be the same as your <em>Initial scale</em> setting.'),
  );

  $form['alpha_settings']['layout']['viewport_settings']['alpha_viewport_max_scale'] = array(
    '#type' => 'select',
    '#title' => t('Maximum scale'),
    '#default_value' => alpha_theme_get_setting('alpha_viewport_max_scale', 1),
    '#options' => $scales,
    '#description' => t('The maximum scaling of the site. This can be any value between 1 and 10, but should not be too big if you want to preserve your mobile look and feel.'),
  );

  $form['alpha_settings']['layout']['viewport_settings']['alpha_viewport_user_scale'] = array(
    '#type' => 'checkbox',
    '#title' => t('Scalable by user'),
    '#description' => t('<p>Determine if a user can resize the screen. This is usually accomplished via multi-touch gestures on iOS and Android devices. If your mobile theme is very customized and presented with good typography and graphics for a reduced mobile size, it is recommended to leave this setting unchecked. If it is left unchecked, the min-scale and max-scale properties will be ignored.</p><p class="marker">HTC Android devices do NOT (currently) respect the viewport meta tag for <em>user-scalable</em>. It will render at the appropriate <em>initial-scale</em> set above, but a user can still zoom in/out.</p>'),
    '#default_value' => alpha_theme_get_setting('alpha_viewport_user_scale', FALSE),
  );
  
  $form['alpha_settings']['layout']['alpha_fixed_fluid'] = array(
    '#type' => 'radios',
    '#description' => t('You may select a fluid layout, or the default fixed width layout.'),
    '#title' => t('Fixed or fluid layout'),
    '#default_value' => alpha_theme_get_setting('alpha_fixed_fluid', 'fixed'),
    '#options' => array(
      'fixed' => t('Fixed width'),
      'fluid' => t('Fluid width'),
    ),
    '#states' => array(
      'visible' => array(
        ':input[name="alpha_responsive_grid"]' => array('value' => t('disabled')),
      ),
    ),
  );

  $form['alpha_settings']['styles'] = array(
    '#type' => 'fieldset',
    '#title' => t('Toggle styles'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  
  $form['alpha_settings']['styles']['alpha_optional_css'] = array(
    '#type' => 'checkboxes',
    '#options' => alpha_css_options($theme),
    '#default_value' => array_keys(alpha_theme_get_setting('alpha_optional_css', array())),
    '#element_validate' => array('alpha_theme_settings_css_validate'),
  );
  
  $form['alpha_settings']['overlay'] = array(
    '#type' => 'fieldset',
    '#title' => t('Debugging'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  
  $form['alpha_settings']['overlay']['alpha_debug_block_toggle'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable the debugging (placeholder) blocks for theme development.'),
    '#default_value' => alpha_theme_get_setting('alpha_debug_block_toggle'),
  );
  
  $form['alpha_settings']['overlay']['alpha_debug_grid_toggle'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable grid overlay for selected roles.'),
    '#default_value' => alpha_theme_get_setting('alpha_debug_grid_toggle'),
  );
  
  $form['alpha_settings']['overlay']['alpha_debug_grid_toggle_state'] = array(
    '#type' => 'checkbox',
    '#title' => t('Turn on grid overlay on page load by default.'),
    '#default_value' => alpha_theme_get_setting('alpha_debug_grid_toggle_state'),
  );
 
  $form['alpha_settings']['overlay']['alpha_debug_grid_roles'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Roles that may use the grid overlay / debugging tool.'),
    '#default_value' => alpha_theme_get_setting('alpha_debug_grid_roles', array()),
    '#options' => array_map('check_plain', user_roles()),
  );
  
  foreach (array('theme_settings', 'logo', 'favicon') as $element) {
    $form['alpha_settings'][$element] = $form[$element];
    unset($form[$element], $form['alpha_settings'][$element]['#description'], $form['alpha_settings'][$element]['#attributes']);
  }  
}

/*
 * @todo
 */
function alpha_theme_settings_css_validate(&$element, &$form_state) {
  $theme = $form_state['build_info']['args'][0];

  $value = array();
  $info = alpha_css_info($theme);
  
  foreach ($element['#value'] as $item) {
    if (isset($info[$item])) {
      $value[$item] = $info[$item];
      
      unset($value[$item]['description']);
    }
  }

  form_set_value($element, $value, $form_state);
}