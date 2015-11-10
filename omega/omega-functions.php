<?php

use Drupal\omega\phpsass\SassParser;
use Drupal\omega\phpsass\SassFile;
// Include Breakpoint Functionality
use Drupal\breakpoint;

/**
 * Custom function to return the active layout to be used for the active page.
 */
function omega_return_active_layout() {
  $theme = \Drupal::theme()->getActiveTheme()->getName();
  
  //$front = drupal_is_front_page();
  //$node = menu_get_object();

  // setup default layout
  $defaultLayout = theme_get_setting('default_layout', $theme);
  $layout = $defaultLayout;
  
  /*
  // if it is a node, check for an alternate layout
  if ($node) {
    $type = $node->type;
    $nodeLayout = theme_get_setting($type . '_layout', $theme);
    $layout = $nodeLayout ? $nodeLayout : $defaultLayout;
  }
  // if it is the front page, check for an alternate layout
  if ($front) {
    $homeLayout = theme_get_setting('home_layout', $theme);
    $layout = $homeLayout ? $homeLayout : $defaultLayout;
  }
  */
  
  return $layout;
}

/** 
 *  Returns array of optional Libraries that can be enabled/disabled in theme settings
 *  for Omega, and Omega sub-themes. The listings here are tied to entries in omega.libraries.yml.
 */
function _omega_optional_css($theme) {
  $status = theme_get_setting('styles', $theme);
  
  return array(
    'scss_html_elements' => array(
      'title' => 'Generic HTML Elements',
      'description' => 'Provides basic styles for generic tags like &lt;a&gt;, &lt;p&gt;, &lt;h2&gt;, etc.',
      'library' => 'omega/omega_html_elements',
      'status' => $status['scss_html_elements'],
    ),
    
    'scss_branding' => array(
      'title' => 'Branding Styles',
      'description' => 'Provides basic layout and styling for logo area.',
      'library' => 'omega/omega_branding',
      'status' => $status['scss_branding'],
    ),
    
    'scss_breadcrumbs' => array(
      'title' => 'Breadcrumbs',
      'description' => 'Basic breadcrumb styling.',
      'library' => 'omega/omega_breadcrumbs',
      'status' => $status['scss_breadcrumbs'],
    ),
    
    'scss_main_menus' => array(
      'title' => 'Main Menu Styling',
      'description' => 'Basic layout and styling for main menu elements.',
      'library' => 'omega/omega_main_menus',
      'status' => $status['scss_main_menus'],
    ),
    'scss_messages' => array(
      'title' => 'Messages',
      'description' => 'Custom styles for Drupal system messages.',
      'library' => 'omega/omega_messages',
      'status' => $status['scss_messages'],
    ),
    'scss_pagers' => array(
      'title' => 'Pagers',
      'description' => 'Custom styles for Drupal pagers.',
      'library' => 'omega/omega_pagers',
      'status' => $status['scss_pagers'],
    ),
    'scss_tabs' => array(
      'title' => 'Local Task Tabs',
      'description' => 'Custom styles for Drupal tabs.',
      'library' => 'omega/omega_tabs',
      'status' => $status['scss_tabs'],
    ),
  );
}

function _omega_getActiveBreakpoints($theme) {
  // get the default layout and convert to name for breakpoint group
  $breakpointGroupId = str_replace("_", ".", theme_get_setting('default_layout', $theme));
  $breakpointGroup = \Drupal::service('breakpoint.manager')->getBreakpointsByGroup($breakpointGroupId);
  if ($breakpointGroup) {
    // custom theme breakpoints
    return $breakpointGroup;
  }
  else {
    // default omega breakpoints
    return \Drupal::service('breakpoint.manager')->getBreakpointsByGroup('omega.standard');
  }
}

function _omega_compile_layout_css($scss, $options) {
  $parser = new SassParser($options);
  // create CSS from SCSS
  $css = $parser->toCss($scss, false);
  return $css;
}

function _omega_compile_layout_sass($layout, $theme = 'omega', $options) {
  //dsm($layout);
  // get a list of themes
  $themes = \Drupal::service('theme_handler')->listInfo();
  // get the current settings/info for the theme
  $themeSettings = $themes[$theme];
  // get the default layout/breakpoint group
  $defaultLayout = theme_get_setting('default_layout', $theme);
  // get all the active breakpoints we'll be editing
  $breakpoints = _omega_getActiveBreakpoints($theme);
  // get the stored layout data
  $layouts = theme_get_setting('layouts', $theme);
  // pull an array of "region groups" based on the "all" media query that should always be present
  $region_groups = $layouts[$defaultLayout]['region_groups']['all'];
  //dsm($region_groups);
  //dsm($layouts);
  $theme_regions = $themeSettings->info['regions'];
  // create variable to hold all SCSS we need
  $scss = '';
 
  $parser = new SassParser($options);
  
  // get the variables for the theme
  $vars = realpath(".") . base_path() . drupal_get_path('theme', 'omega') . '/style/scss/vars.scss';
  $omegavars = new SassFile;
  $varscss = $omegavars->get_file_contents($vars, $parser);
  // set the grid to fluid
  $varscss .= '$twidth: 100%;';
  
  // get the SCSS for the grid system
  $gs = realpath(".") . base_path() . drupal_get_path('theme', 'omega') . '/style/scss/grids/omega.scss';
  $omegags = new SassFile;
  $gsscss = $omegags->get_file_contents($gs, $parser);

  $scss = $varscss . $gsscss;  
  //$scss .= '#content { @include column(8); } #sidebar-first { @include column(2); } #sidebar-second { @include column(2); }';

    // loop over the media queries
  foreach($breakpoints as $breakpoint) {
    // create a clean var for the scss for this breakpoint
    $breakpoint_scss = '';
    //dsm($breakpoint);
    
    // loop over the region groups
    foreach ($region_groups as $gid => $info ) {
      // add row mixin

      $rowname = str_replace("_", "-", $gid) . '-layout';
      $rowval = $layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['row'];
      $maxwidth = $layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['maxwidth'];
      if ($layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['maxwidth_type'] == 'pixel') {
        $unit = 'px';
      }
      else {
        $unit = '%';
      }
      
      $breakpoint_scss .= '#' . $rowname . ' { 
  @include row(' . $rowval . ');
  max-width: '. $maxwidth . $unit .';         
';
  
      // loop over regions
      foreach($info['regions'] as $rid => $data) {
        $regionname = str_replace("_", "-", $rid);
        $breakpoint_scss .= '  #' . $regionname . ' { 
    @include column(' . $layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['width'] . ', ' . $info['row'] . '); ';
        
        if ($layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['prefix'] > 0) {
          $breakpoint_scss .= '  
    @include prefix(' . $layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['prefix'] . '); ';  
        }
        
        if ($layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['suffix'] > 0) {
        $breakpoint_scss .= '  
    @include suffix(' . $layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['suffix'] . '); ';
        }
        
        if ($layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['push'] > 0) {
        $breakpoint_scss .= '  
    @include push(' . $layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['push'] . '); ';
        }
        
        if ($layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['pull'] > 0) {
        $breakpoint_scss .= '  
    @include pull(' . $layout[$defaultLayout]['region_groups'][$breakpoint->name][$gid]['regions'][$rid]['pull'] . '); ';
        }
        
        $breakpoint_scss .= '
    margin-bottom: $regionSpacing;
  } 
';
        // apply all functions 
      }
      
      $breakpoint_scss .= '
}
';
    }
    
    // if not the defualt media query that should apply to all screens
    // we will wrap the scss we've generated in the appropriate media query.
    if ($breakpoint->name != 'all') {
      $breakpoint_scss = '@media ' . $breakpoint->mediaQuery . ' { 
' . $breakpoint_scss . '
}
';
    }
    
    // add in the SCSS from this breakpoint and add to our SCSS
    $scss .= $breakpoint_scss; 
  }

  return $scss;
}



function _omega_save_layout_files($scss, $css, $theme) {
  global $base_path;
  // going to overwrite some stuff
  $layoutscss = realpath(".") . $base_path . drupal_get_path('theme', $theme) . '/style/scss/omega-layout.scss';
  $layoutcss = realpath(".") . $base_path . drupal_get_path('theme', $theme) . '/style/css/omega-layout.css';
  
  $scssfile = file_unmanaged_save_data($scss, $layoutscss, TRUE);
  $cssfile = file_unmanaged_save_data($css, $layoutcss, TRUE);

  //return $file;
}



/**
 * Helper function to calculate the new width/push/pull/prefix/suffix of a primary region 
 * $main is the primary region for a group which will actually be the one we are adjusting
 * $empty_regions is an array of region data for regions that would be empty
 * $cols is the total number of columns assigned using row(); for the region group
 * 
 * @return array()
 * array contains width, push, pull, prefix and suffix of adjusted primary region
 */
function _omega_layout_generation_adjust($main, $empty_regions = array(), $cols) {
  // assign values from $main region's data
  $original_prefix = $prefix = $main['prefix'];
  $original_pull = $pull = $main['pull'];
  $original_width = $width = $main['width'];
  $original_push = $push = $main['push'];
  $original_suffix = $suffix = $main['suffix'];
  
  foreach($empty_regions as $rid => $data) {
    
    
    /* Calculate the width */
    
    // add the width, prefix & suffix of the regions we are combining
    // this creates the "true" width of the primary regions
    $newActualWidth = $data['width'] + $data['prefix'] + $data['suffix'] + $width;
    // reassign the $width variable
    $width = $newActualWidth;
    // this ensures if the primary region has a prefix/suffix, they are calculated too
    // when ensuring that the region doesn't have more columns than the container.
    $newTotalWidth = $newActualWidth + $prefix + $suffix;
    
    /* END EARLY IF WIDTH IS TOO WIDE */
    
    // if the columns combine to be wider than the row, set the max columns
    // and remove all push/pull/prefix/suffix values
    if ($newTotalWidth > $cols) {
      return array(
        'width' => $cols,
        'prefix' => 0,
        'suffix' => 0,
        'push' => 0,
        'pull' => 0,
      );
    }
    
    
    
    /* Calculate updates for the push/pull */
    if ($data['push'] >= 1) {
      
      // appears these regions were swapped, compensate by removing the push/pull
      if ($data['push'] == $original_width && $data['width'] == $original_pull) {
        $pull = 0;
      }
      
      // assume now that BOTH other regions were pushed
      if ($original_pull > $data['width']) {
        $pull = $cols - $width;
      }
      
    }
    
    if ($data['pull'] >= 1) {
      // appears these regions were swapped, compensate by removing the push/pull
      if ($data['pull'] == $original_width && $data['width'] == $original_push) {
        $push = 0;
      }
      
      // assume now that BOTH other regions were pushed
      if ($original_push > $data['width']) {
        $push = $cols - $width;
      }
    }
    
    /* Calculate the prefix/suffix */
    // we don't actually need to do this as the prefix/suffix is added to the actual 
    // width of the primary region rather than adding/subtracting additional margings.
    
    
  }
  
  return array(
    'width' => $width,
    'prefix' => $prefix,
    'suffix' => $suffix,
    'push' => $push,
    'pull' => $pull,
  );
}