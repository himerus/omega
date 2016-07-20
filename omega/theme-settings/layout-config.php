<?php

// create the container for settings
$form['layout-config'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('debug')),
  '#title' => t('Layout Configuration'),
  '#description' => t('<p>The options here allow you to enable or disable the entire Omega.gs Layout Management system, as well as choose which layout to use as the default layout, and on various other site pages. </p>'),
  '#weight' => -800,
  '#group' => 'omega',
  //'#open' => TRUE,
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

$enable_omegags_layout = theme_get_setting('enable_omegags_layout', $theme);
$form['enable_omegags_layout'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable Omega.gs Layout Management'),
  '#description' => t('Turning on the Omega.gs layout management system will allow you to configure your site region layout in each breakpoint via a visual interface. <strong>#easybutton</strong>'),
  '#default_value' => isset($enable_omegags_layout) ? $enable_omegags_layout : TRUE,
  '#group' => 'layout-config',
  '#weight' => -999,
);

$form['layout-config']['non_omegags_info'] = array(
  '#type' => 'item',
  '#prefix' => '',
  '#markup' => '<div class="messages messages--warning omega-styles-info"><p>Since you have "<strong><em>disabled the Awesome</em></strong>" above, now the Omega.gs layout is not being used in your theme/subtheme. This means that you will need to provide your own layout system. Easy huh?!? Although, I would really just use the awesome...</p></div>',
  '#suffix' => '',
  '#weight' => -99,
  '#states' => $omegaGSoff,
);

$availableLayouts = _omega_layout_select_options($layouts);

$form['layout-config']['default-layouts'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('layout-selection')),
  '#title' => 'Default Layouts',
  '#group' => 'layout-config',
  '#states' => $omegaGSon,
);

$form['layout-config']['default-layouts']['default_layout'] = array(
  '#prefix' => '<div class="default-layout-select">',
  '#suffix' => '</div>',
  '#description' => '<p class="description">The Default Layout is used on any/every page rendered by the <strong>' . $theme . '</strong> theme. Additional layouts can be used for other pages/sections as defined in the additional select options below.</p>',
  '#type' => 'select',
  '#attributes' => array(
    'class' => array(
      'layout-select', 
      'clearfix'
    ),
  ),
  '#title' => 'Default: Select Layout',
  '#options' => $availableLayouts,
  '#default_value' => isset($defaultLayout) ? $defaultLayout : theme_get_setting('default_layout', $theme),
  '#tree' => FALSE,
  '#states' => $omegaGSon,
  // attempting possible jQuery intervention rather than ajax 
);

$homeLayout = isset($form_state->values['home_layout']) ? $form_state->values['home_layout'] : theme_get_setting('home_layout', $theme);
$form['layout-config']['default-layouts']['home_layout'] = array(
  '#prefix' => '<div class="home-layout-select">',
  '#suffix' => '</div>',
  '#description' => '<p class="description">The Homepage Layout is used only on the home page rendered by the <strong>' . $theme . '</strong> theme.</p>',
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
$form['layout-config']['node-layouts'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('layout-selection')),
  '#title' => 'Node Type Layouts',
  '#group' => 'layout-config',
  '#states' => $omegaGSon,
);

$types = node_type_get_types();
foreach ($types AS $ctype => $ctypeData) {

  $layout_name = 'node_type_' . $ctype . '_layout';
  $ctypeLayout = theme_get_setting($layout_name, $theme);
  
  $form['layout-config']['node-layouts'][$layout_name] = array(
    '#prefix' => '<div class="' . $ctype . '-layout-select">',
    '#suffix' => '</div>',
    '#type' => 'select',
    '#attributes' => array(
      'class' => array(
        'layout-select', 
        'clearfix'
      ),
    ),
    '#title' => $ctypeData->label() . ': Select Layout',
    '#description' => '<p class="description">The <strong>'. $ctypeData->label() .'</strong> Layout is used only on pages rendering a full node page of the type "<strong>'.$ctypeData->id().'</strong>" using the <strong>' . $theme . '</strong> theme.</p>',
    '#options' => $availableLayouts,
    '#default_value' => isset($ctypeLayout) ? $ctypeLayout : theme_get_setting('default_layout', $theme),
    '#tree' => FALSE,
    '#states' => $omegaGSon,
    // attempting possible jQuery intervention rather than ajax 
  );  
}

// create layout switching options for taxonomy term pages
$form['layout-config']['taxonomy-layouts'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('layout-selection')),
  '#title' => 'Taxonomy Term Page Layouts',
  '#group' => 'layout-config',
  '#states' => $omegaGSon,
);

$vocabs = taxonomy_vocabulary_get_names();

foreach ($vocabs AS $vocab_id) {
  $vocab = \Drupal\taxonomy\Entity\Vocabulary::load($vocab_id);
  $layout_name = 'taxonomy_' . $vocab_id . '_layout';
  $ttypeLayout = theme_get_setting($layout_name, $theme);
  
  $form['layout-config']['taxonomy-layouts'][$layout_name] = array(
    '#prefix' => '<div class="' . $layout_name . '-select">',
    '#suffix' => '</div>',
    '#type' => 'select',
    '#attributes' => array(
      'class' => array(
        'layout-select', 
        'clearfix'
      ),
    ),
    '#title' => $vocab->get('name') . ' Vocabulary: Select Layout',
    '#description' => '<p class="description">The <strong>'. $vocab->get('name') .'</strong> Layout is used only on pages rendering a full taxonomy term listing page of the type "<strong>'.$vocab_id.'</strong>" using the <strong>' . $theme . '</strong> theme.</p>',
    '#options' => $availableLayouts,
    '#default_value' => isset($ttypeLayout) ? $ttypeLayout : theme_get_setting('default_layout', $theme),
    '#tree' => FALSE,
    '#states' => $omegaGSon,
  ); 
}




$form['layout-config']['views-layouts'] = array(
  '#type' => 'details',
  '#description' => '<div class="messages messages--warning omega-styles-info">Currently, views layout switches are not available. This is a feature yet to be developed</div>',
  '#attributes' => array('class' => array('layout-selection')),
  '#title' => 'Views Page Layouts',
  '#group' => 'layout-config',
  '#states' => $omegaGSon,
);

/*
$view = \Drupal::routeMatch()->getParameter('view_id');
$viewData = Views::getView($view);

$allRouteViews = Views::getApplicableViews('uses_route');
dsm($allRouteViews);

$result = array();
$entity_ids = \Drupal::service('entity.query')->get('view')
    ->condition('status', TRUE)
    ->condition("display.*.display_plugin", array('page' => 'page'), 'IN')
    ->execute();
//dsm($entity_ids);
foreach (\Drupal::entityManager()->getStorage('view')->loadMultiple($entity_ids) as $view) {
  foreach ($view->get('display') as $id => $display) {
    //dsm($view);
    //dsm($id);
    //dsm($display);
    
    $enabled = !empty($display['display_options']['enabled']) || !array_key_exists('enabled', $display['display_options']);

    if ($enabled && in_array($display['display_plugin'], array('page'))) {
      $result[] = [$view->id(), $id];
    }
  }
}   
dsm($result);
*/