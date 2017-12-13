<?php

/**
 * @file
 * Implements custom theme settings for Omega Five related Layout.
 */

use Drupal\omega\Layout\OmegaLayout;

// Get the theme name we are editing.
$theme = \Drupal::theme()->getActiveTheme()->getName();
$layouts = OmegaLayout::getAvaliableLayoutPluginLayouts([$theme], ['full']);
$themeLayouts = OmegaLayout::loadThemeLayouts($theme);
$breakpoint_options = OmegaLayout::getAvailableBreakpoints($theme);

$layoutService = \Drupal::service('omega_layout.layout');

// Create the container for settings.
$form['layouts'] = [
  '#type' => 'details',
  '#attributes' => ['class' => ['debug']],
  '#title' => t('Layout Builder'),
  '#weight' => -799,
  '#group' => 'omega',
  '#description' => '',
  '#tree' => TRUE,
];

$form['layouts']['#description'] .= '<p class="description">You are able to configure your layouts based on any breakpoint defined in your Omega subtheme, or any base theme that the theme uses. Each layout is assigned to a single breakpoint, and then will be able to be used in Layout Configuration when choosing a layout to use for default or particular page types.</p>';
$form['layouts']['#description'] .= '<p class="description">This works great in scenarios where a simple set of breakpoints will work well throughout 95% of your site, but the homepage and landing pages, or even image gallery pages requiring a more complex set of responsive adjustments.</p>';

// The active layout we are editing.
// This var will be unset during submit.
$form['layouts']['edit_this_layout'] = [
  '#prefix' => '<div id="layout-editor-select" class="layout-editor-select">',
  '#suffix' => '</div>',
  '#type' => 'radios',
  '#attributes' => [
    'class' => [
      'layout-select',
      'clearfix',
    ],
  ],
  '#title' => 'Select Layout to Edit',
  '#description' => t('<p class="description">You are able to edit only one layout at a time.</p><p class="description"> The amount of configurations passed through the form requires limiting this ability until Drupal core issue <a href="https://www.drupal.org/node/1565704" target="_blank"><strong>#1565704</strong></a> can be resolved. </p>'),
  '#options' => OmegaLayout::getAvailableLayoutFormOptions($themeLayouts),
  '#default_value' => isset($edit_this_layout) ? $edit_this_layout : theme_get_setting('default_layout', $theme),
  '#tree' => FALSE,
  '#states' => [
    'invisible' => [
      // Hidden when Omega.gs is turned off.
      OmegaLayout::$omegaGsDisabled,
    ],
  ],
  // Attempting possible jQuery intervention rather than ajax.
];

// @todo: Make this save via AJAX the layouts when edited so multiple can edited.
foreach ($themeLayouts as $lid => $info) {

  // Grab the configuration for the requested layout.
  $layout_config_object = \Drupal::config($theme . '.layout.' . $lid);
  $layoutData = $layout_config_object->get();

  $form['layouts'][$lid] = [
    '#type' => 'fieldset',
    '#title' => $info['label'],
    '#prefix' => '<div id="layout-' . $lid . '-config">',
    '#suffix' => '</div>',
    '#group' => 'layouts',
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    '#states' => [
      'visible' => [
        ':input[name="edit_this_layout"]' => ['value' => $lid],
      ],
    ],
  ];

  $active_breakpoint_group = theme_get_setting('breakpoint_group_' . $lid, $theme);
  $current_breakpoint_group = isset($active_breakpoint_group) ? $active_breakpoint_group : 'omega.standard';

  $form['layouts'][$lid]['breakpoint_group_updated'] = [
    '#type' => 'item',
    '#prefix' => '',
    '#markup' => '<div class="messages messages--warning omega-styles-info">By changing the breakpoint group for the  "<strong>' . $lid . '</strong>" layout, You will need to save the form in order to then configure the theme regions based on the new breakpoint group.</div>',
    '#suffix' => '',
    '#states' => [
      'invisible' => [
        ':input[name="breakpoint_group_' . $lid . '"]' => ['value' => $current_breakpoint_group],
      ],
    ],
  ];

  $form['layouts'][$lid]['breakpoint_group_' . $lid] = [
    '#type' => 'select',
    '#options' => $breakpoint_options,
    '#title' => t('Breakpoint group'),
    '#description' => t('<p class="description">This breakpoint group will apply to this layout any time it is used. This allows you to use a different breakpoint group for different layouts.</p>'),
    '#default_value' => $current_breakpoint_group,
    '#tree' => FALSE,
    '#states' => [
      'invisible' => [
        // Hidden when Omega.gs is turned off.
        OmegaLayout::$omegaGsDisabled,
      ],
    ],
  ];

  $form['layouts'][$lid]['region_assignment'] = [
    '#type' => 'details',
    '#attributes' => ['class' => ['layout-breakpoint']],
    '#title' => t('Region Assignment'),
    '#group' => 'layout',
    '#states' => [
      'invisible' => [
        ':input[name="enable_omegags_layout"]' => ['checked' => FALSE],
      ],
    ],
    '#open' => FALSE,
  ];

  $form['layouts'][$lid]['region_assignment']['region_assignment_info'] = [
    '#type' => 'item',
    '#prefix' => '',
    '#markup' => '<div class="messages messages--status omega-styles-info">The <strong>Region Assignment</strong> section allows you to configure the theme regions provided in <em>' . $theme . '.info.yml</em> and mapping those to the regions in the <em>' . $lid . '</em> layout.</div>',
    '#suffix' => '',
    '#group' => 'region_assignment',
  ];
  // @todo: Cycle theme regions, and allow them to be assigned to a layout region.
  $themeRegions = OmegaLayout::getThemeRegions($theme);

  // @see \Drupal\block\BlockListBuilder::buildBlocksForm()
  $form['layouts'][$lid]['region_assignment']['theme-region-assignment'] = [
    '#type' => 'table',
    '#header' => [
      t('Label'),
      t('Machine name'),
      t('Weight'),
      t('Move to...'),
    ],
    '#empty' => t('There are no items yet.'),
    // TableSelect: Injects a first column containing the selection widget into
    // each table row.
    // Note that you also need to set #tableselect on each form submit button
    // that relies on non-empty selection values (see below).
    '#tableselect' => FALSE,
    // TableDrag: Each array value is a list of callback arguments for
    // drupal_add_tabledrag(). The #id of the table is automatically prepended;
    // if there is none, an HTML ID is auto-generated.
    '#tabledrag' => [],
  ];

  // Weights range from -delta to +delta, so delta should be at least half
  // of the amount of blocks present. This makes sure all blocks in the same
  // region get an unique weight.
  $weight_delta = round(count($themeRegions) / 2);

  $assignmentRegions = $info['regions'];
  $assignmentRegions['unassigned'] = [
    'label' => t('Unassigned Regions'),
  ];
  $layoutRegionOptions = [];

  // Needs to loop over these to create the 'options' prior to the first row
  // being built.
  // @todo: Investigate a cleaner method to reduce the foreach looping.
  foreach ($assignmentRegions as $layoutRegionId => $layoutRegionInfo) {
    $layoutRegionOptions[$layoutRegionId] = $layoutRegionInfo['label'];
  }

  foreach ($assignmentRegions as $layoutRegionId => $layoutRegionInfo) {

    $form['layouts'][$lid]['region_assignment']['theme-region-assignment']['#tabledrag'][] = [
      'action' => 'match',
      'relationship' => 'sibling',
      'group' => 'layout-region-select',
      'subgroup' => 'layout-region--' . $layoutRegionId,
      'hidden' => FALSE,
    ];
    $form['layouts'][$lid]['region_assignment']['theme-region-assignment']['#tabledrag'][] = [
      'action' => 'order',
      'relationship' => 'sibling',
      'group' => 'layout-weight',
      'subgroup' => 'layout-weight--' . $layoutRegionId,
    ];

    $form['layouts'][$lid]['region_assignment']['theme-region-assignment']['layout-' . $layoutRegionId] = [
      '#attributes' => [
        'class' => ['layout-region--title', 'layout-region-title--' . $layoutRegionId],
        'no_striping' => TRUE,
      ],
    ];

    $form['layouts'][$lid]['region_assignment']['theme-region-assignment']['layout-' . $layoutRegionId]['title'] = [
      '#markup' => t('<h5>@layoutLabel <small>(@layoutId)</small></h5>', ['@layoutLabel' => $layoutRegionInfo['label'], '@layoutId' => $layoutRegionId]),
      '#wrapper_attributes' => [
        'colspan' => 4,
      ],
    ];

    $form['layouts'][$lid]['region_assignment']['theme-region-assignment']['layout--' . $layoutRegionId . '--message'] = [
      '#attributes' => [
        'class' => [
          'layout-region--message',
          'layout-region--' . $layoutRegionId . '--message',
          empty($layoutData['region_assignment']) ? 'layout-region--empty' : 'layout-region--populated',
        ],
      ],
    ];

    if ($layoutRegionId != 'unassigned') {
      $defaultText = '<em>' . t('No theme regions currently assigned to this layout region...') . '</em>';
    }
    else {
      $defaultText = '<em>' . t('All available regions are currently assigned! Good Job!') . '</em>';
    }
    $form['layouts'][$lid]['region_assignment']['theme-region-assignment']['layout--' . $layoutRegionId . '--message']['message'] = [
      '#markup' => $defaultText,
      '#wrapper_attributes' => [
        'colspan' => 4,
      ],
    ];

    // Build the table rows and columns.
    // The first nested level in the render array forms the table row,
    // on which you likely want to set #attributes and #weight.
    // Each child element on the second level represents a table column cell
    // in the respective table row, which are render elements on their own.
    // For single output elements, use the table cell itself for the
    // render element. If a cell should contain multiple elements, simply use
    // nested sub-keys to build the render element structure for drupal_render()
    // as you would everywhere else.
    $ignored_regions = ['page_top', 'page_bottom'];

    $regionAssignment = isset($layoutData['region_assignment'][$layoutRegionId]) ? $layoutData['region_assignment'][$layoutRegionId] : [];
    foreach ($regionAssignment as $themeRegion => $themeRegionData) {

      // Skip any regions we don't want to be able to place.
      // Primarily system regiosn like page_top or page_bottom.
      if (!in_array($themeRegion, $ignored_regions)) {
        // @todo: Make this work and be dynamic.
        $assignedRegion = isset($regionAssignment[$themeRegion]['region']) ? $regionAssignment[$themeRegion]['region'] : 'unassigned';
        $assignedWeight = isset($regionAssignment[$themeRegion]['weight']) ? $regionAssignment[$themeRegion]['weight'] : 0;
        $assignedLabel = isset($themeRegions[$themeRegion]) ? $themeRegions[$themeRegion] : 'ERROR: Missing Region Label';

        $form['layouts'][$lid]['region_assignment']['theme-region-assignment'][$themeRegion] = [
          '#attributes' => [
            'class' => ['draggable', 'theme-region-row'],
          ],
        ];

        // Label.
        $form['layouts'][$lid]['region_assignment']['theme-region-assignment'][$themeRegion]['label'] = [
          '#markup' => '<h5>' . t('@assignedLabel', ['@assignedLabel' => $assignedLabel]) . '</h5>',
          '#wrapper_attributes' => [
            'class' => ['region-label'],
          ],
        ];

        // Machine name.
        $form['layouts'][$lid]['region_assignment']['theme-region-assignment'][$themeRegion]['machine'] = [
          '#markup' => t('@themeRegion', ['@themeRegion' => $themeRegion]),
          '#wrapper_attributes' => [
            'class' => ['machine-name'],
          ],
        ];

        // Weight Field.
        $form['layouts'][$lid]['region_assignment']['theme-region-assignment'][$themeRegion]['weight'] = [
          '#type' => 'weight',
          '#default_value' => $assignedWeight,
          '#delta' => $weight_delta,
          '#title' => t('Weight for @block block', ['@block' => $assignedLabel]),
          '#title_display' => 'invisible',
          '#attributes' => [
            'class' => ['layout-weight', 'layout-weight--' . $assignedRegion],
          ],
        ];

        // Region select field.
        $form['layouts'][$lid]['region_assignment']['theme-region-assignment'][$themeRegion]['region'] = [
          '#type' => 'select',
          '#default_value' => $assignedRegion,
          '#required' => TRUE,
          '#title' => t('Region for @region region', ['@block' => $assignedLabel]),
          '#title_display' => 'invisible',
          '#options' => $layoutRegionOptions,
          '#attributes' => [
            'class' => ['layout-region-select', 'layout-region--' . $assignedRegion],
          ],
        ];
      }
    }
  }

  // @todo: Create form element for the region and allow it to be assigned to one of the layout regions
  $breakpoints = OmegaLayout::getActiveBreakpoints($lid, $theme);
  // Foreach breakpoint we have, we will create a form element group and
  // appropriate settings for region layouts per breakpoint.
  /* @var Drupal\breakpoint\Breakpoint $breakpoint */
  foreach ($breakpoints as $breakpoint) {

    // Create a 'clean' version of the id to use to match what we want
    // in our yml structure.
    $idtrim = OmegaLayout::cleanBreakpointId($breakpoint);

    $form['layouts'][$lid]['groups'][$idtrim] = [
      '#type' => 'details',
      '#attributes' => ['class' => ['layout-breakpoint']],
      '#title' => $breakpoint->getLabel() . ' -- ' . $breakpoint->getMediaQuery() . '',
      '#weight' => $breakpoint->getWeight(),
      '#group' => 'layout',
      '#states' => [
        'invisible' => [
          ':input[name="enable_omegags_layout"]' => ['checked' => FALSE],
        ],
        'visible' => [
          ':input[name="breakpoint_group_' . $lid . '"]' => ['value' => $current_breakpoint_group],
        ],
      ],
    ];

    foreach ($info['groups'] as $gid => $groupInfo) {

      // Determine if config says region group should be collapsed or not.
      $open = TRUE;
      $collapseVal = $groupInfo['collapsed'];
      if (isset($collapseVal) && $collapseVal == 'TRUE') {
        $open = FALSE;
      }
      if (isset($collapseVal) && $collapseVal == 'FALSE') {
        $open = TRUE;
      }
      $form['layouts'][$lid]['groups'][$idtrim][$gid] = [
        '#type' => 'details',
        '#attributes' => [
          'class' => [
            'layout-breakpoint-regions',
            'clearfix',
          ],
        ],
        '#title' => 'Region Group: ' . $groupInfo['label'],
        '#open' => $open,
      ];

      $possible_cols = [];

      for ($i = 12; $i <= 12; $i++) {
        $possible_cols[$i] = $i . '';
      }

      $regions = ['0' => '-- None'];
      foreach ($info['regions'] as $region_id => $region_info) {
        $regions[$region_id] = isset($theme_regions[$region_id]) ? $theme_regions[$region_id] : $region_id;;
      }

      // @todo: Fix $rcount.
      $rcount = count($layoutData['groups'][$idtrim][$gid]['regions']);
      if ($rcount > 3 || $rcount <= 1) {
        $primary_access = FALSE;
      }
      else {
        $primary_access = TRUE;
      }

      $form['layouts'][$lid]['groups'][$idtrim][$gid]['row'] = [
        '#prefix' => '<div class="region-group-layout-settings">',
        '#type' => 'select',
        '#attributes' => [
          'class' => [
            'row-column-count',
            'clearfix',
          ],
        ],
        '#title' => 'Columns',
        '#options' => $possible_cols,
        '#default_value' => isset($layoutData['groups'][$idtrim][$gid]['row']) ? $layoutData['groups'][$idtrim][$gid]['row'] : '12',
        '#group' => '',
      ];

      $form['layouts'][$lid]['groups'][$idtrim][$gid]['primary_region'] = [
        '#type' => 'select',
        '#attributes' => [
          'class' => [
            'row-primary-region',
            'clearfix',
          ],
        ],
        '#title' => 'Primary Region',
        '#options' => $regions,
        '#default_value' => isset($layoutData['groups'][$idtrim][$gid]['primary_region']) ? $layoutData['groups'][$idtrim][$gid]['primary_region'] : '',
        '#group' => '',
        '#access' => $primary_access,
      ];

      $form['layouts'][$lid]['groups'][$idtrim][$gid]['visual_controls'] = [
        '#prefix' => '<div class="omega-layout-controls form-item">',
        '#markup' => '<div class="control-label">Show/Hide: </div><div class="clearfix"><a class="push-pull-toggle" href="#">Push/Pull</a> | <a class="prefix-suffix-toggle" href="#">Prefix/Suffix</a></div>',
        '#suffix' => '</div>',
      ];

      $form['layouts'][$lid]['groups'][$idtrim][$gid]['maxwidth'] = [
        '#type' => 'textfield',
        '#size' => 3,
        '#maxlength' => 4,
        '#attributes' => [
          'class' => [
            'row-max-width',
            'clearfix',
          ],
        ],
        '#title' => 'Max-width: ',
        '#default_value' => isset($layoutData['groups'][$idtrim][$gid]['maxwidth']) ? $layoutData['groups'][$idtrim][$gid]['maxwidth'] : '100',
        '#group' => '',
      ];

      $form['layouts'][$lid]['groups'][$idtrim][$gid]['maxwidth_type'] = [
        '#type' => 'radios',
        '#attributes' => [
          'class' => [
            'row-maxwidth-type',
            'clearfix',
          ],
        ],
        '#options' => [
          'percent' => '%',
          'pixel' => 'px',
        ],
        '#title' => 'Max-width type',
        '#default_value' => isset($layoutData['groups'][$idtrim][$gid]['maxwidth_type']) ? $layoutData['groups'][$idtrim][$gid]['maxwidth_type'] : 'percent',
        '#group' => '',
      ];

      $form['layouts'][$lid]['groups'][$idtrim][$gid]['collapsed'] = [
        '#type' => 'radios',
        '#attributes' => [
          'class' => [
            'row-collapsed',
            'clearfix',
          ],
        ],
        '#options' => [
          'TRUE' => 'Y',
          'FALSE' => 'N',
        ],
        '#title' => 'Collapsed',
        '#default_value' => isset($layoutData['groups'][$idtrim][$gid]['collapsed']) ? $layoutData['groups'][$idtrim][$gid]['collapsed'] : 'FALSE',
        '#group' => '',
      ];

      $form['layouts'][$lid]['groups'][$idtrim][$gid]['wrapper'] = [
        '#type' => 'radios',
        '#attributes' => [
          'class' => [
            'row-full-width-wrapper',
            'clearfix',
          ],
        ],
        '#options' => [
          'TRUE' => 'Y',
          'FALSE' => 'N',
        ],
        '#title' => 'Full Width Wrapper',
        '#default_value' => isset($layoutData['groups'][$idtrim][$gid]['wrapper']) ? $layoutData['groups'][$idtrim][$gid]['wrapper'] : 'TRUE',
        '#group' => '',
        '#suffix' => '</div>',
      ];


      // Get columns for this region group.
      $available_cols = [];
      // @todo Fix where the row config data comes from???
      for ($i = 0; $i <= 12; $i++) {
        $available_cols[$i] = $i . '';
      }

      // This should be changed in order to not pull the regions from the
      // layout data. This would ensure that a new theme being configured
      // potentially even with an empty $theme.layout.$layout.yml would
      // still be configurable.
      foreach ($groupInfo['regions'] as $rid) {

        $current_push = isset($layoutData['groups'][$idtrim][$gid]['regions'][$rid]['push']) ? $layoutData['groups'][$idtrim][$gid]['regions'][$rid]['push'] : 0;
        $current_prefix = isset($layoutData['groups'][$idtrim][$gid]['regions'][$rid]['prefix']) ? $layoutData['groups'][$idtrim][$gid]['regions'][$rid]['prefix'] : 0;
        $current_width = isset($layoutData['groups'][$idtrim][$gid]['regions'][$rid]['width']) ? $layoutData['groups'][$idtrim][$gid]['regions'][$rid]['width'] : 12;
        $current_suffix = isset($layoutData['groups'][$idtrim][$gid]['regions'][$rid]['suffix']) ? $layoutData['groups'][$idtrim][$gid]['regions'][$rid]['suffix'] : 0;
        $current_pull = isset($layoutData['groups'][$idtrim][$gid]['regions'][$rid]['pull']) ? $layoutData['groups'][$idtrim][$gid]['regions'][$rid]['pull'] : 0;

        $regionTitle = isset($info['regions'][$rid]['label']) ? $info['regions'][$rid]['label'] : $rid;

        $form['layouts'][$lid]['groups'][$idtrim][$gid]['regions'][$rid] = [
          '#type' => 'details',
          '#title' => t('@regionTitle', ['@regionTitle' => $regionTitle]),
          '#attributes' => [
            'class' => [
              'region-settings',
              'clearfix',
            ],
            'data-omega-push' => $current_push,
            'data-omega-prefix' => $current_prefix,
            'data-omega-width' => $current_width,
            'data-omega-suffix' => $current_suffix,
            'data-omega-pull' => $current_pull,
          ],
          '#open' => TRUE,
        ];

        // Push (in columns).
        $form['layouts'][$lid]['groups'][$idtrim][$gid]['regions'][$rid]['push'] = [
          '#type' => 'select',
          '#attributes' => [
            'class' => [
              'push-controller',
            ],
          ],
          '#title' => 'Push',
          '#options' => $available_cols,
          '#default_value' => $current_push,
        ];

        // Prefix (in columns).
        $form['layouts'][$lid]['groups'][$idtrim][$gid]['regions'][$rid]['prefix'] = [
          '#type' => 'select',
          '#attributes' => [
            'class' => [
              'prefix-controller',
            ],
          ],
          '#title' => 'Prefix',
          '#options' => $available_cols,
          '#default_value' => $current_prefix,
        ];

        // Width (in columns).
        $form['layouts'][$lid]['groups'][$idtrim][$gid]['regions'][$rid]['width'] = [
          '#type' => 'select',
          '#attributes' => [
            'class' => [
              'width-controller',
            ],
          ],
          '#title' => 'Width',
          '#options' => $available_cols,
          '#default_value' => $current_width,
        ];

        // Suffix (in columns).
        $form['layouts'][$lid]['groups'][$idtrim][$gid]['regions'][$rid]['suffix'] = [
          '#type' => 'select',
          '#attributes' => [
            'class' => [
              'suffix-controller',
            ],
          ],
          '#title' => 'Suffix',
          '#options' => $available_cols,
          '#default_value' => $current_suffix,
        ];

        // Pull (in columns).
        $form['layouts'][$lid]['groups'][$idtrim][$gid]['regions'][$rid]['pull'] = [
          '#type' => 'select',
          '#attributes' => [
            'class' => [
              'pull-controller',
            ],
          ],
          '#title' => 'Pull',
          '#options' => $available_cols,
          '#default_value' => $current_pull,
        ];

      }
    }
  }
}
