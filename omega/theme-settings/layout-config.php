<?php

/**
 * @file
 * Implements custom theme settings for Omega Five related Layout.
 */

use Drupal\omega\Layout\OmegaLayout;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

/*
 * Currently the functionality for default_layout isn't working as expected.
 * This needs to be rebuilt so the form item (default_layout) stores what we
 * expect the default layout to be, then the additional items that we choose
 * not to configure should no have default_layout as the default OPTION. Instead
 * those items should be set to inherit, then still allow pulling the value for
 * default_layout if it is not set.
 *
 * @todo: Refactor/configure default_layout variable storage and usage.
 */

// Create the container for settings.
$form['layout-config'] = [
  '#type' => 'details',
  '#attributes' => ['class' => ['debug']],
  '#title' => t('Layout Configuration'),
  '#description' => t('<p>The options here allow you to enable or disable the entire Omega.gs Layout Management system, as well as choose which layout to use as the default layout, and on various other site pages. </p>'),
  '#weight' => -800,
  '#group' => 'omega',
  '#tree' => TRUE,
];

// #states flag to indicate that Omega.gs has been enabled.
$omegaGSon = [
  'invisible' => [
    ':input[name="enable_omegags_layout"]' => ['checked' => FALSE],
  ],
];

// #states flag to indicate that Omega.gs has been disabled.
$omegaGSoff = [
  'invisible' => [
    ':input[name="enable_omegags_layout"]' => ['checked' => TRUE],
  ],
];

$enable_omegags_layout = theme_get_setting('enable_omegags_layout', $theme);
$form['enable_omegags_layout'] = [
  '#type' => 'checkbox',
  '#title' => t('Enable Omega.gs Layout Management'),
  '#description' => t('Turning on the Omega.gs layout management system will allow you to configure your site region layout in each breakpoint via a visual interface. <strong>#easybutton</strong>'),
  '#default_value' => isset($enable_omegags_layout) ? $enable_omegags_layout : TRUE,
  '#group' => 'layout-config',
  '#weight' => -999,
];

$form['layout-config']['non_omegags_info'] = [
  '#type' => 'item',
  '#prefix' => '',
  '#markup' => '<div class="messages messages--warning omega-styles-info"><p>Since you have "<strong><em>disabled the Awesome</em></strong>" above, now the Omega.gs layout is not being used in your theme/subtheme. This means that you will need to provide your own layout system. Easy huh?!? Although, I would really just use the awesome...</p></div>',
  '#suffix' => '',
  '#weight' => -99,
  '#states' => $omegaGSoff,
];

$availableLayouts = OmegaLayout::getAvailableLayoutPluginFormOptions();

$form['layout-config']['default-layouts'] = [
  '#type' => 'details',
  '#attributes' => ['class' => ['layout-selection']],
  '#title' => t('Default Layouts'),
  '#description' => t('<div class="messages messages--status omega-styles-info">The following section allows you to customize the layout used for various default pages like the homepage.</div>'),
  '#group' => 'layout-config',
  '#states' => $omegaGSon,
];

$form['layout-config']['default-layouts']['default_layout'] = [
  '#prefix' => '<div class="default-layout-select">',
  '#suffix' => '</div>',
  '#description' => t('<p class="description">The Default Layout is used on any/every page rendered by the <strong>@theme</strong> theme. Additional layouts can be used for other pages/sections as defined in the additional select options below.</p>', ['@theme' => $theme]),
  '#type' => 'select',
  '#attributes' => [
    'class' => [
      'layout-select',
      'clearfix',
    ],
  ],
  '#title' => 'Default: Select Layout',
  '#options' => $availableLayouts,
  '#default_value' => isset($defaultLayout) ? $defaultLayout : theme_get_setting('default_layout', $theme),
  '#tree' => FALSE,
  '#states' => [
    'invisible' => [
      // Hidden when Omega.gs is turned off.
      OmegaLayout::$omegaGsDisabled,
    ],
  ],
  // Attempting possible jQuery intervention rather than ajax.
];

$homeLayout = isset($form_state->values['home_layout']) ? $form_state->values['home_layout'] : theme_get_setting('home_layout', $theme);
$form['layout-config']['default-layouts']['home_layout'] = [
  '#prefix' => '<div class="home-layout-select">',
  '#suffix' => '</div>',
  '#description' => t('<p class="description">The Homepage Layout is used only on the home page rendered by the <strong>@theme</strong> theme.</p>', ['@theme' => $theme]),
  '#type' => 'select',
  '#attributes' => [
    'class' => [
      'layout-select',
      'clearfix',
    ],
  ],
  '#title' => 'Homepage: Select Layout',
  '#options' => $availableLayouts,
  '#default_value' => isset($homeLayout) ? $homeLayout : theme_get_setting('default_layout', $theme),
  '#tree' => FALSE,
  '#states' => [
    'invisible' => [
      // Hidden when Omega.gs is turned off.
      OmegaLayout::$omegaGsDisabled,
    ],
  ],
  // Attempting possible jQuery intervention rather than ajax.
];

// Show a select menu for each node type, allowing the selection
// of an alternate layout per node type.
$form['layout-config']['node-layouts'] = [
  '#type' => 'details',
  '#attributes' => ['class' => ['layout-selection']],
  '#title' => 'Node Type Layouts',
  '#description' => t('<div class="messages messages--status omega-styles-info">The following section allows you to customize the layout used for a specific node type.</div>'),
  '#group' => 'layout-config',
  '#states' => [
    'invisible' => [
      // Hidden when Omega.gs is turned off.
      OmegaLayout::$omegaGsDisabled,
    ],
  ],
];

$types = NodeType::loadMultiple();
foreach ($types as $ctype => $ctypeData) {

  $layout_name = 'node_type_' . $ctype . '_layout';
  $ctypeLayout = theme_get_setting($layout_name, $theme);

  $form['layout-config']['node-layouts'][$layout_name] = [
    '#prefix' => '<div class="' . $ctype . '-layout-select">',
    '#suffix' => '</div>',
    '#type' => 'select',
    '#attributes' => [
      'class' => [
        'layout-select',
        'clearfix',
      ],
    ],
    '#title' => $ctypeData->label() . ': Select Layout',
    '#description' => t('<p class="description">The <strong>@label</strong> Layout is used only on pages rendering a full node page of the type "<strong>@id</strong>" using the <strong>@theme</strong> theme.</p>', [
      '@label' => $ctypeData->label(),
      '@id' => $ctypeData->id(),
      '@theme' => $theme,
    ]),
    '#options' => $availableLayouts,
    '#default_value' => isset($ctypeLayout) ? $ctypeLayout : theme_get_setting('default_layout', $theme),
    '#tree' => FALSE,
    '#states' => [
      'invisible' => [
        // Hidden when Omega.gs is turned off.
        OmegaLayout::$omegaGsDisabled,
      ],
    ],
    // Attempting possible jQuery intervention rather than ajax.
  ];
}

// Create layout switching options for taxonomy term pages.
$form['layout-config']['taxonomy-layouts'] = [
  '#type' => 'details',
  '#attributes' => ['class' => ['layout-selection']],
  '#title' => 'Taxonomy Term Page Layouts',
  '#description' => t('<div class="messages messages--status omega-styles-info">The following section allows you to customize the layout used for a Taxonomy Term page.</div>'),
  '#group' => 'layout-config',
  '#states' => [
    'invisible' => [
      // Hidden when Omega.gs is turned off.
      OmegaLayout::$omegaGsDisabled,
    ],
  ],
];

$vocabs = taxonomy_vocabulary_get_names();

foreach ($vocabs as $vocab_id) {
  $vocab = Vocabulary::load($vocab_id);
  $layout_name = 'taxonomy_' . $vocab_id . '_layout';
  $ttypeLayout = theme_get_setting($layout_name, $theme);

  $form['layout-config']['taxonomy-layouts'][$layout_name] = [
    '#prefix' => '<div class="' . $layout_name . '-select">',
    '#suffix' => '</div>',
    '#type' => 'select',
    '#attributes' => [
      'class' => [
        'layout-select',
        'clearfix',
      ],
    ],
    '#title' => $vocab->get('name') . ' Vocabulary: Select Layout',
    '#description' => t('<p class="description">The <strong>@label</strong> Layout is used only on pages rendering a full taxonomy term listing page of the type "<strong>@id</strong>" using the <strong>@theme</strong> theme.</p>', [
      '@label' => $vocab->get('name'),
      '@id' => $vocab_id,
      '@theme' => $theme,
    ]),
    '#options' => $availableLayouts,
    '#default_value' => isset($ttypeLayout) ? $ttypeLayout : theme_get_setting('default_layout', $theme),
    '#tree' => FALSE,
    '#states' => [
      'invisible' => [
        // Hidden when Omega.gs is turned off.
        OmegaLayout::$omegaGsDisabled,
      ],
    ],
  ];
}

$form['layout-config']['views-layouts'] = [
  '#type' => 'details',
  '#description' => t('<div class="messages messages--status omega-styles-info">The following section allows you to customize the layout used for a Views page.</div>'),
  '#attributes' => ['class' => ['layout-selection']],
  '#title' => 'Views Page Layouts',
  '#group' => 'layout-config',
  '#states' => [
    'invisible' => [
      // Hidden when Omega.gs is turned off.
      OmegaLayout::$omegaGsDisabled,
    ],
  ],
];

// $result attempts to get only page views.
$results = [];
$entity_ids = \Drupal::service('entity.query')->get('view')
  ->condition('status', TRUE)
  ->condition("display.*.display_plugin", ['page' => 'page'], 'IN')
  ->execute();
foreach (\Drupal::entityTypeManager()
  ->getStorage('view')
  ->loadMultiple($entity_ids) as $view) {

  foreach ($view->get('display') as $id => $display) {

    $enabled = !empty($display['display_options']['enabled']) || !array_key_exists('enabled', $display['display_options']);

    if ($enabled && in_array($display['display_plugin'], ['page'])) {

      // We haven't come across the view id we are looking at yet.
      if (empty($results[$view->id()])) {
        $results[$view->id()] = [
          'view_id' => $view->id(),
          'displays' => [$id],
        ];
      }
      // $result[$view-id()] already exists, so just add the display to it.
      else {
        $results[$view->id()]['displays'][] = $id;
      }
    }
  }
}

foreach ($results as $result) {
  $view_id = $result['view_id'];
  // Create a container for each view.
  $form['layout-config']['views-layouts'][$view_id] = [
    '#type' => 'details',
    '#description' => '',
    '#attributes' => ['class' => ['views-display-group']],
    '#title' => 'View Name: ' . $view_id,
    '#group' => 'views-layouts',
    '#states' => [
      'invisible' => [
        // Hidden when Omega.gs is turned off.
        OmegaLayout::$omegaGsDisabled,
      ],
    ],
  ];

  // Create a form element for each display.
  foreach ($result['displays'] as $display) {
    $layout_name = 'views_view_' . $view_id . '_' . $display . '_layout';
    $vtypeLayout = theme_get_setting($layout_name, $theme);

    $form['layout-config']['views-layouts'][$view_id][$layout_name] = [
      '#prefix' => '<div class="' . $layout_name . '-select">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#attributes' => [
        'class' => [
          'layout-select',
          'clearfix',
        ],
      ],
      '#title' => 'Display: ' . $display . ' Select Layout: ',
      '#description' => t('<p class="description">The <strong>@view</strong> Layout is used only on the X page on the Y display using the <strong>@theme</strong> theme.</p>', ['@view' => $view_id, '@theme' => $theme]),
      '#options' => $availableLayouts,
      '#default_value' => isset($vtypeLayout) ? $vtypeLayout : theme_get_setting('default_layout', $theme),
      '#tree' => FALSE,
      '#states' => [
        'invisible' => [
          // Hidden when Omega.gs is turned off.
          OmegaLayout::$omegaGsDisabled,
        ],
      ],
    ];
  }
}
