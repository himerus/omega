<?php

// create the container for settings
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
  '#description' => t('Enable Omega.gs layout management system'),
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
  
  //kint($breakpoint->getBaseId());
  
  // create a 'clean' version of the id to use to match what we want in our yml structure
  $idtrim = str_replace($breakpoint->getGroup() . '.', "", $breakpoint->getBaseId());
  
  $form['layouts'][$defaultLayout]['region_groups'][$idtrim] = array(
    '#type' => 'details',
    '#attributes' => array('class' => array('layout-breakpoint')),
    '#title' => $breakpoint->getLabel() . ' -- ' . $breakpoint->getMediaQuery() . '',
    '#weight' => $breakpoint->getWeight(),
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
    //kint($gid);
    $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid] = array(
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
$form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['data'] = array(
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
    $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['row'] = array(
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
      '#default_value' => isset($layouts[$defaultLayout]['region_groups'][$idtrim][$gid]['row']) ? $layouts[$defaultLayout]['region_groups'][$idtrim][$gid]['row'] : '12',
      '#group' => '',
    );
    
    
    $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['visual_controls'] = array(
      '#prefix' => '<div class="omega-layout-controls form-item">',
      '#markup' => '<label>Show/Hide: </label><div class="clearfix"><a class="push-pull-toggle" href="#">Push/Pull</a> | <a class="prefix-suffix-toggle" href="#">Prefix/Suffix</a></div>',
      '#suffix' => '</div>',
    );
    
    $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['maxwidth'] = array(
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
      '#default_value' => isset($layouts[$defaultLayout]['region_groups'][$idtrim][$gid]['maxwidth']) ? $layouts[$defaultLayout]['region_groups'][$idtrim][$gid]['maxwidth'] : '100',
      '#group' => '',
    );
    
    $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['maxwidth_type'] = array(
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
      '#default_value' => isset($layouts[$defaultLayout]['region_groups'][$idtrim][$gid]['maxwidth_type']) ? $layouts[$defaultLayout]['region_groups'][$idtrim][$gid]['maxwidth_type'] : 'percent',
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
       
       

       $pattern = 'layouts.' . $defaultLayout . '.region_groups.' . $idtrim . '.' . $gid . '.regions.' . $rid . '.';
       //kint($pattern);
       $pattern_push = $pattern . 'push';
       $pattern_prefix = $pattern . 'prefix';
       $pattern_width = $pattern . 'width';
       $pattern_suffix = $pattern . 'suffix';
       $pattern_pull = $pattern . 'pull';
       //kint($pattern);
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
       
       $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['regions'][$rid] = array(
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
       $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['regions'][$rid]['push'] = array(
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
       $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['regions'][$rid]['prefix'] = array(
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
       $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['regions'][$rid]['width'] = array(
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
       $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['regions'][$rid]['suffix'] = array(
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
       $form['layouts'][$defaultLayout]['region_groups'][$idtrim][$gid]['regions'][$rid]['pull'] = array(
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