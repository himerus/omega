<?php

/*
 * @todo
 */
function alpha_theme_settings_general(&$form, &$form_state) {
  $theme = $form_state['build_info']['args'][0];
  $scales = alpha_scale_options(1, 10, 0.5);  
  $settings = alpha_settings($theme);
  $optional = alpha_css_options($theme);
  $responsive = alpha_css_options($theme, TRUE);
  $exclude = alpha_exclude_options($theme);
  $toggle = alpha_toggle();
  
  $form['alpha_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => -10,
    '#prefix' => t('<h3>Layout configuration</h3>'),
  );  
  
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
  
  $form['alpha_settings']['layout']['alpha_responsive'] = array(
    '#type' => 'checkbox',
    '#description' => t('<p>The responsive layout allows the grid to resize based on the size of the users device. This incorporates mobile first design, so the default behavior is to show the simplest (stacked) version of the layout, and for devices that meet certain display criteria (width), new CSS and grid sizing will be applied.</p><p class="marker">The fluid grid doesn\'t support this feature.</p>'),
    '#title' => t('Responsive grid layout'),
    '#default_value' => $settings['responsive']['enabled'],
  );
  
  $form['alpha_settings']['layout']['responsive_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Responsive grid settings'),
    '#states' => array(
      'visible' => array(
        ':input[name="alpha_responsive"]' => array('checked' => TRUE),
      ),
    ),
  ); 
  
  $options = array(
    'narrow' => array(
      'description' => t('The narrow grid is designed to work well for tablets in a portrait viewing mode.'),
      'media' => 'all',
    ),  
    'normal' => array(
      'description' => t('The normal grid is designed to work well for tablets in a landscape viewing mode and most web browsers.'),
      'media' => 'all',
    ),
    'wide' => array(
      'description' => t('The wide grid is designed to work well for users with a lot of screen real estate. However, this may not look well in a single sidebar layout. Design considerations must be made when using this layout.'),
      'media' => 'all',
    ),
  );
  
  foreach ($options as $type => $item) {  
    $form['alpha_settings']['layout']['responsive_settings']['alpha_responsive_' . $type] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable @type grid', array('@type' => $type)),
      '#description' => $item['description'],
      '#default_value' => $settings['responsive'][$type]['enabled'],
    );
    
    $form['alpha_settings']['layout']['responsive_settings']['alpha_responsive_' . $type . '_media'] = array(
      '#type' => 'textfield',
      '#title' => t('Media'),
      '#description' => t('The @media tag that you want to use for the @type grid.', array('@type' => $type)),
      '#default_value' => $settings['responsive'][$type]['media'],
      '#maxlength' => 255,
      '#attributes' => array(
        'class' => array('alpha-media-query'),
      ),
      '#states' => array(
        'visible' => array(
          ':input[name="alpha_responsive_' . $type . '"]' => array('checked' => TRUE),
        ),
      ),   
    );

    if (!empty($responsive)) {
      $form['alpha_settings']['layout']['responsive_settings']['styles_' . $type] = array(
        '#type' => 'fieldset',
        '#title' => t('Optional styles'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#states' => array(
          'visible' => array(
            ':input[name="alpha_responsive_' . $type . '"]' => array('checked' => TRUE),
          ),
        ), 
      );
      
      $form['alpha_settings']['layout']['responsive_settings']['styles_' . $type]['alpha_responsive_' . $type . '_css'] = array(
        '#type' => 'checkboxes',
        '#options' => $responsive,
        '#responsive' => TRUE,
        '#default_value' => $settings['responsive'][$type]['css'],
      );
    }
  }
  
  $form['alpha_settings']['layout']['responsive_settings']['alpha_viewport'] = array(
    '#type' => 'checkbox',
    '#title' => t('Allow customizing viewport meta properties on iOS and Android devices'),
    '#description' => t('By default, most mobile browsers choose their own way to handle page rendering by using the viewport meta tag. iOS and Android are "capable" of displaying full size websites by simply scaling down the view. However, when using a truly mobile version of your theme, you will / may want to customize these settings. You can find more information about this in the <a href="http://alpha.developmentgeeks.com">Omega documentation</a>.'),
    '#default_value' => $settings['viewport']['enabled'],
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
  
  if (!empty($optional) || !empty($exclude)) {  
    $form['alpha_settings']['styles'] = array(
      '#type' => 'fieldset',
      '#title' => t('Toggle styles'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    
    if (!empty($optional)) {
      $form['alpha_settings']['styles']['alpha_css'] = array(
        '#title' => t('Enable optional stylesheets'),
        '#description' => t('You can choose from this list to enable optional stylesheets.'),
        '#type' => 'checkboxes',
        '#options' => $optional,
        '#default_value' => $settings['css'],
      );
    }
    
    if (!empty($exclude)) {
      $form['alpha_settings']['styles']['alpha_exclude'] = array(
        '#title' => t('Disable module and theme stylesheets'),
        '#description' => t('Alpha will remove the selected stylesheets from your pages.'),
        '#type' => 'checkboxes',
        '#options' => $exclude,
        '#default_value' => $settings['exclude'],
      );
    }
  }

  $form['alpha_settings']['overlay'] = array(
    '#type' => 'fieldset',
    '#weight' => -6,
    '#title' => t('Debugging'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  
  $form['alpha_settings']['overlay']['alpha_debug_block_toggle'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable the debugging (placeholder) blocks for theme development.'),
    '#default_value' => $settings['debug']['block'],
  );
  
  $form['alpha_settings']['overlay']['alpha_debug_block_unintrusive'] = array(
    '#type' => 'checkbox',
    '#title' => t("Only place a debugging block in regions that wouldn't get rendered otherwise (e.g. empty regions that are not set to enforce rendering)."),
    '#default_value' => $settings['debug']['unintrusive'],
  );
  
  $form['alpha_settings']['overlay']['alpha_debug_grid_toggle'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable grid overlay and debugging blocks for selected roles.'),
    '#default_value' => $settings['debug']['grid'],
  );

  $form['alpha_settings']['overlay']['alpha_debug_grid_roles'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Roles that may use the grid overlay / debugging tool.'),
    '#default_value' => $settings['debug']['roles'],
    '#options' => array_map('check_plain', user_roles()),
    '#states' => array(
      'visible' => array(
        ':input[name="alpha_debug_grid_toggle"]' => array('checked' => TRUE),
      ),
    ), 
  );

  foreach (array('theme_settings', 'logo', 'favicon') as $element) {
    $form['alpha_settings'][$element] = $form[$element];
    unset($form[$element], $form['alpha_settings'][$element]['#description'], $form['alpha_settings'][$element]['#attributes']);
  }
  
  $form['alpha_settings']['theme_settings']['alpha_toggle'] = array(
    '#type' => 'item',
    '#title' => t('Additional toggleable elements provided by Alpha'),
  );
  
  foreach ($toggle as $item => $title) {
    $form['alpha_settings']['theme_settings']['alpha_toggle']['alpha_toggle_' . $item] = array(
      '#type' => 'checkbox',
      '#title' => $title,
      '#default_value' => $settings['toggle'][$item],
    );
  }

  $form['alpha_settings']['theme_settings']['alpha_hidden'] = array(
    '#type' => 'item',
    '#title' => t('Choose from the elements below to hide them via CSS'),
    '#description' => t('This will make the element invisible to normal users while not removing it from the HTML (e.g. for screenreaders).'),
  );
  
  foreach (alpha_visibility() as $item => $title) {
    $form['alpha_settings']['theme_settings']['alpha_hidden']['alpha_hidden_' . $item] = array(
      '#type' => 'checkbox',
      '#title' => $title,
      '#default_value' => $settings['hidden'][$item],
    );
  }
}