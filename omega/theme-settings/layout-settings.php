<?php

// create the container for settings
$form['layouts'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('debug')),
  '#title' => t('Layout Builder'),
  '#description' => t(''),
  '#weight' => -799,
  '#group' => 'omega',
  //'#open' => TRUE,
  '#tree' => TRUE,
);

$form['layouts']['#description'] .= '<p class="description">You are able to configure your layouts based on any breakpoint defined in your Omega subtheme, or any base theme that the theme uses. Each layout is assigned to a single breakpoint, and then will be able to be used in Layout Configuration when choosing a layout to use for default or particular page types.</p>';
$form['layouts']['#description'] .= '<p class="description">This works great in scenarios where a simple set of breakpoints will work well throughout 95% of your site, but the homepage and landing pages, or even image gallery pages requiring a more complex set of responsive adjustments.</p>';

// the active layout we are editing.
// this var will be unset during submit
$form['layouts']['edit_this_layout'] = array(
  '#prefix' => '<div id="layout-editor-select">',
  '#suffix' => '</div>',
  '#type' => 'radios',
  '#attributes' => array(
    'class' => array(
      'layout-select', 
      'clearfix'
    ),
  ),
  '#title' => 'Select Layout to Edit',
  '#description' => t('<p class="description">You are able to edit only one layout at a time.</p><p class="description"> The amount of configurations passed through the form requires limiting this ability until Drupal core issue <a href="https://www.drupal.org/node/1565704" target="_blank"><strong>#1565704</strong></a> can be resolved. </p>'),
  '#options' => $availableLayouts,
  '#default_value' => isset($edit_this_layout) ? $edit_this_layout : theme_get_setting('default_layout', $theme),
  '#tree' => FALSE,
  '#states' => $omegaGSon,
  // attempting possible jQuery intervention rather than ajax 
);

$breakpoint_options = _omega_getAvailableBreakpoints($theme);

foreach ($layouts as $lid => $ldata) {
  
  $form['layouts'][$lid] = array(
    '#type' => 'fieldset',
    '#title' => $lid,
    '#prefix' => '<div id="layout-'. $lid . '-config">',
    '#suffix' => '</div>',
    //'#weight' => $breakpoint->weight,
    '#group' => 'layouts',
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    '#states' => array(
      'visible' => array(
        ':input[name="edit_this_layout"]' => array('value' => $lid),
      ),
    ),
  );
  
  $active_breakpoint_group = theme_get_setting('breakpoint_group_' . $lid, $theme);
  //dsm($active_breakpoint_group);
  $current_breakpoint_group = isset($active_breakpoint_group) ? $active_breakpoint_group : 'omega.standard';
  $form['layouts'][$lid]['breakpoint_group_' . $lid] = array(
    '#type' => 'select',
    '#options' => $breakpoint_options,
    '#title' => t('Breakpoint group'),
    '#description' => t('<p class="description">This breakpoint group will apply to this layout any time it is used. This allows you to use a different breakpoint group for different layouts.</p>'),
    '#default_value' => $current_breakpoint_group,
    '#tree' => FALSE,
    '#states' => $omegaGSon,
  );
  
  $form['layouts'][$lid]['breakpoint_group_updated'] = array(
    '#type' => 'item',
    '#prefix' => '',
    '#markup' => '<div class="messages messages--warning omega-styles-info">By changing the breakpoint group for the  "<strong>' . $lid . '</strong>" layout, You will need to save the form in order to then configure the theme regions based on the new breakpoint group.</div>',
    '#suffix' => '',
    '#states' => array(
      'invisible' => array(
        ':input[name="breakpoint_group_' . $lid . '"]' => array('value' => $current_breakpoint_group),
      ),
    ),
);
  
  $breakpoints = _omega_getActiveBreakpoints($lid, $theme);
  // foreach breakpoint we have, we will create a form element group and appropriate settings for region layouts per breakpoint.
  foreach($breakpoints as $breakpoint) {
    
    //kint($breakpoint->getBaseId());
    
    // create a 'clean' version of the id to use to match what we want in our yml structure
    $idtrim = omega_return_clean_breakpoint_id($breakpoint);
    
    $form['layouts'][$lid]['region_groups'][$idtrim] = array(
      '#type' => 'details',
      '#attributes' => array('class' => array('layout-breakpoint')),
      '#title' => $breakpoint->getLabel() . ' -- ' . $breakpoint->getMediaQuery() . '',
      '#weight' => $breakpoint->getWeight(),
      '#group' => 'layout',
      '#states' => array(
        'invisible' => array(
          ':input[name="enable_omegags_layout"]' => array('checked' => FALSE),
        ),
        'visible' => array(
          ':input[name="breakpoint_group_' . $lid . '"]' => array('value' => $current_breakpoint_group),
        ),
      ),
      //'#open' => TRUE,
    );
    if (isset($region_groups['_core'])) {
      unset($region_groups['_core']);
    }
    foreach ($region_groups as $gid => $info ) {
      // determine if configuration says region group should be collapsed or not    
      $open = TRUE;
      $collapseVal = $layouts[$lid]['region_groups'][$idtrim][$gid]['collapsed'];
      if (isset($collapseVal) && $collapseVal == 'TRUE') {
        $open = FALSE;
      }
      if (isset($collapseVal) && $collapseVal == 'FALSE') {
        $open = TRUE;
      }
      $form['layouts'][$lid]['region_groups'][$idtrim][$gid] = array(
        '#type' => 'details',
        '#attributes' => array(          'class' => array(
            'layout-breakpoint-regions', 
            'clearfix'
          ),
        ),
        '#title' => 'Region Group: ' . $gid,
        '#open' => $open,
      );
          
      $possible_cols = array();
      
      for ($i = 12; $i <= 12; $i++) {
        $possible_cols[$i] = $i . '';
      }
      
      $regions = array('0' => '-- None');
      foreach($layouts[$lid]['region_groups'][$idtrim][$gid]['regions'] as $region_id => $region_info) {
        $regions[$region_id] = isset($theme_regions[$region_id]) ? $theme_regions[$region_id] : $region_id;;
      }
      $rcount = count($layouts[$lid]['region_groups'][$idtrim][$gid]);
      if ($rcount > 3 || $rcount <= 1) {
        $primary_access = FALSE;
      }
      else {
        $primary_access = TRUE;
      }
      
      
      
      //dsm($info);
      $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['row'] = array(
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
        '#default_value' => isset($layouts[$lid]['region_groups'][$idtrim][$gid]['row']) ? $layouts[$lid]['region_groups'][$idtrim][$gid]['row'] : '12',
        '#group' => '',
      );
      
      $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['primary_region'] = array(
        '#type' => 'select',
        '#attributes' => array(
          'class' => array(
            'row-primary-region', 
            'clearfix'
          ),
        ),
        '#title' => 'Primary Region',
        '#options' => $regions,
        '#default_value' => isset($layouts[$lid]['region_groups'][$idtrim][$gid]['primary_region']) ? $layouts[$lid]['region_groups'][$idtrim][$gid]['primary_region'] : '',
        '#group' => '',
        '#access' => $primary_access,
      );
      
      $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['visual_controls'] = array(
        '#prefix' => '<div class="omega-layout-controls form-item">',
        '#markup' => '<div class="control-label">Show/Hide: </div><div class="clearfix"><a class="push-pull-toggle" href="#">Push/Pull</a> | <a class="prefix-suffix-toggle" href="#">Prefix/Suffix</a></div>',
        '#suffix' => '</div>',
      );
      
      $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['maxwidth'] = array(
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
        '#default_value' => isset($layouts[$lid]['region_groups'][$idtrim][$gid]['maxwidth']) ? $layouts[$lid]['region_groups'][$idtrim][$gid]['maxwidth'] : '100',
        '#group' => '',
      );
      
      $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['maxwidth_type'] = array(
        '#type' => 'radios',
        '#attributes' => array(
          'class' => array(
            'row-maxwidth-type', 
            'clearfix'
          ),
        ),
        '#options' => array(
          'percent' => '%',
          'pixel' => 'px'
        ),
        '#title' => 'Max-width type',
        '#default_value' => isset($layouts[$lid]['region_groups'][$idtrim][$gid]['maxwidth_type']) ? $layouts[$lid]['region_groups'][$idtrim][$gid]['maxwidth_type'] : 'percent',
        '#group' => '',
      );
      
      $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['collapsed'] = array(
        '#type' => 'radios',
        '#attributes' => array(
          'class' => array(
            'row-collapsed', 
            'clearfix'
          ),
        ),
        '#options' => array(
          'TRUE' => 'Y',
          'FALSE' => 'N',
        ),
        '#title' => 'Collapsed',
        '#default_value' => isset($layouts[$lid]['region_groups'][$idtrim][$gid]['collapsed']) ? $layouts[$lid]['region_groups'][$idtrim][$gid]['collapsed'] : 'FALSE',
        '#group' => '',
        '#suffix' => '</div>',
      );
      
      
      
      
      // get columns for this region group
      $available_cols = array();
      for ($i = 0; $i <= $layouts[$lid]['region_groups'][$idtrim][$gid]['row']; $i++) {
        $available_cols[$i] = $i . '';
      }
      
      
      // This should be changed in order to not pull the regions from the layout data
      // This would ensure that a new theme being configured potentially even with an empty
      // $theme.layout.$layout.yml would still be configurable.
      foreach($layouts[$lid]['region_groups'][$idtrim][$gid]['regions'] as $rid => $data) {
         
         $current_push = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['push'];
         $current_prefix = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['prefix'];
         $current_width = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['width'];
         $current_suffix = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['suffix'];
         $current_pull = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['pull'];
         
         $regionTitle = isset($theme_regions[$rid]) ? $theme_regions[$rid] : $rid;
         
         $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['regions'][$rid] = array(
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
         $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['push'] = array(
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
         $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['prefix'] = array(
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
         $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['width'] = array(
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
         $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['suffix'] = array(
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
         $form['layouts'][$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['pull'] = array(
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