<?php

// create the container for settings
$form['layouts'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('debug')),
  '#title' => t('Layout Builder'),
  '#description' => t('<p class="description">You are able to configure your layout based on the breakpoints defined in <strong>(' . $theme . '.breakpoints.yml)</strong></p>'),
  '#weight' => -799,
  '#group' => 'omega',
  //'#open' => TRUE,
  '#tree' => TRUE,
);

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

//kint($layouts);
foreach ($layouts as $lid => $ldata) {
  
  $form['layouts'][$lid] = array(
    '#type' => 'fieldset',
    '#title' => $lid,
    '#prefix' => '<div id="layout-'. $lid . '-config">',
    '#suffix' => '</div>',
    //'#weight' => $breakpoint->weight,
    '#group' => 'layouts',
    '#states' => $omegaGSon,
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    '#states' => array(
      'visible' => array(
        ':input[name="edit_this_layout"]' => array('value' => $lid),
      ),
    ),
  );
  
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
      '#states' => $omegaGSon,
      //'#open' => TRUE,
    );
    //dsm($breakpoints);
    foreach ($region_groups as $gid => $info ) {
      
      // determine if configuration says region group should be collapsed or not    
      $open = TRUE;
      $collapseVal = $layouts[$lid]['region_groups'][$idtrim][$gid]['collapsed'];
      //krumo($info);
      if (isset($collapseVal) && $collapseVal == 'TRUE') {
        $open = FALSE;
      }
      if (isset($collapseVal) && $collapseVal == 'FALSE') {
        $open = TRUE;
      }
      
      $form['layouts'][$lid]['region_groups'][$idtrim][$gid] = array(
        '#type' => 'details',
        '#attributes' => array(
          'class' => array(
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
      foreach($info['regions'] as $region_id => $region_info) {
        $regions[$region_id] = isset($theme_regions[$region_id]) ? $theme_regions[$region_id] : $region_id;;
      }
      $rcount = count($info['regions']);
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
         
         
  
         $pattern = 'layouts.' . $lid . '.region_groups.' . $idtrim . '.' . $gid . '.regions.' . $rid . '.';
         //kint($pattern);
         $pattern_push = $pattern . 'push';
         $pattern_prefix = $pattern . 'prefix';
         $pattern_width = $pattern . 'width';
         $pattern_suffix = $pattern . 'suffix';
         $pattern_pull = $pattern . 'pull';
         //kint($pattern);
/*
         $current_push = theme_get_setting($pattern_push, $theme) ? theme_get_setting($pattern_push, $theme) : $data['push'];
         $current_prefix = theme_get_setting($pattern_prefix, $theme) ? theme_get_setting($pattern_prefix, $theme) : $data['prefix'];
         $current_width = theme_get_setting($pattern_width, $theme) ? theme_get_setting($pattern_width, $theme) : $data['width'];
         $current_suffix = theme_get_setting($pattern_suffix, $theme) ? theme_get_setting($pattern_suffix, $theme) : $data['suffix'];
         $current_pull = theme_get_setting($pattern_pull, $theme) ? theme_get_setting($pattern_pull, $theme) : $data['pull'];
*/
         
         
         $current_push = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['push'];
         $current_prefix = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['prefix'];
         $current_width = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['width'];
         $current_suffix = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['suffix'];
         $current_pull = $layouts[$lid]['region_groups'][$idtrim][$gid]['regions'][$rid]['pull'];
         
         //dsm($breakpoint->name);
         //dsm(theme_get_setting($pattern_width));
         //dsm(theme_get_setting('layouts.' . $defaultLayout . '.region_groups.' . $breakpoint->name));
         //dsm($pattern_width);
         //dsm(theme_get_setting($pattern_width, $theme));
         
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