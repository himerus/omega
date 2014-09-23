<?php

require_once('lib/Drupal/omega/phpsass/SassParser.php');

/**
 * omega_clear_layout_cache
 *
 * Clears array data from stored JSON from database
 * and rebuilds the array from latest .json files
 *
 * @param (string) (theme) active theme
 * @return (array) returns array of available layouts
 */

function omega_clear_layout_cache($theme) {
  // delete layout data for $theme from database
  variable_del('theme_' . $theme . '_layouts');
  // rebuild the array from json files
  return _omega_get_layout_json_data($theme);
}

/**
 * omega_json_load_layout_file
 *
 * Load JSON file from $location and return layout array
 *
 * @param (string) (location) path to JSON file
 * @return (array) layout array data
 */

function omega_json_load_layout_file($location) {
  $json = file_get_contents($location);
  return json_decode($json, true);
}

function omega_json_load_settings_array($layouts) {
  //dsm($layouts);
  $settings = array();
  foreach ($layouts as $lid => $layoutData) {
    $settings[$lid] = $layoutData['data'];
  }
  
  drupal_add_js(array('availableLayouts' => $settings), 'setting');
}
/**
 * omega_json_get
 *
 * Converts layout array to JSON data ready to save
 *
 * @param (array) (var) layout array variable
 * @return (string) json data
 */
 
function omega_json_get($var) {

  $style = defined("JSON_PRETTY_PRINT") ? true : false;
  // PHP >= 5.4
  if ($style) {
    //drupal_set_message('JSON_PRETTY_PRINT exists..');
    $json = json_encode($var, JSON_PRETTY_PRINT);
  }
  // PHP < 5.4
  else {
    //drupal_set_message('JSON_PRETTY_PRINT does not exist..');
    $json = omega_pretty_json(json_encode($var));
  }
  //dsm($json);
  return $json;
}

function _omega_compile_layout_json($layout, $values) {
  return omega_json_get($values);
}





/** 
 * @function _omega_get_layout_json_data
 * @todo
 *
 * THIS STILL NEEDS TO BE CHANGED TO recognize the database
 * settings stored that may not have yet been written
 * Possibly compare the json array to the database one, and 
 * throw a warning that they are out of sync and to save
 * the layout to affect the changes in the DB that are not yet
 * active.
 */
function _omega_get_layout_json_data($theme) {
  //$theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  $json = array();
  // get a list of themes
  $themes = list_themes();
  // theme settings for current theme
  $themeSettings = $themes[$theme];
  //dsm($themeSettings);
  // We look for this and any base themes for layouts.
  //$layoutThemes = isset($themeSettings->base_themes) ? $themeSettings->base_themes : array();
  $layoutThemes = array();
  // add the current theme as well!
  $layoutThemes[$theme] = $themeSettings->info['name'];
  
  $layoutGroups = array();
  $layoutsAvailable = array();
  
  
  
  foreach ($layoutThemes as $t => $name) {
    $scanPath = drupal_get_path('theme', $t) . '/layouts';
    //dsm($theme);
    $layoutGroups[$t] = file_scan_directory($scanPath, '/.*\.json/', array('key' => 'name'));
  }
  //dsm($layoutGroups);
  
  
  foreach ($layoutGroups as $t => $layouts) {
    //dsm($layouts);
    foreach ($layouts as $layout) {
      $name = $layout->name;
      
      
      $layoutSettings = omega_json_load_layout_file($layout->uri);
      
      
            
      $usableLayout = array(
        'theme' => $t,
        'path' => $layout->uri,
        'file' => $layout->filename,
        'name' => $name,
        'data' => $layoutSettings[$name],
      );
      //dsm($layoutsAvailable);
      // if this layout already exists from another theme
      if (isset($layoutsAvailable[$name])) {
        $usableLayout['overrides'] = array(
          $layoutsAvailable[$name]['theme'] => $layoutsAvailable[$name],
        );
      }
      
      
      $layoutsAvailable[$name] = $usableLayout;
    }
  }
  
  
  // THIS GIVES ME THE VARIABLE STRUCTURE I WANT TO RECREATED IN _omega_save_database_layouts()
  //variable_set('theme_' . $theme . '_layouts', $layoutsAvailable);
  return $layoutsAvailable;
}


// returns select field options for the available layouts
function _omega_layout_json_options($layouts) {
  $options = array();
  foreach($layouts as $id => $info) {
    //$options[$id] = $info['theme'] . '--' . $info['name'];
    $options[$id] = $info['name'];
  }
  //dsm($options);
  return $options;
}




function _omega_optional_css($theme) {
  $status = theme_get_setting('styles', $theme);
  //dsm($status);
  
  return array(
    'scss_html_elements' => array(
      'title' => 'Generic HTML Elements',
      'description' => 'Provides basic styles for generic tags like &lt;a&gt;, &lt;p&gt;, &lt;h2&gt;, etc.',
      'file' => 'html-elements.css',
      'status' => $status['scss_html_elements'],
    ),
    'scss_branding' => array(
      'title' => 'Branding Styles',
      'description' => 'Provides basic layout and styling for logo area.',
      'file' => 'site-branding.css',
      'status' => $status['scss_branding'],
    ),
    'scss_breadcrumbs' => array(
      'title' => 'Breadcrumbs',
      'description' => 'Basic breadcrumb styling.',
      'file' => 'breadcrumbs.css',
      'status' => $status['scss_breadcrumbs'],
    ),
    'scss_main_menus' => array(
      'title' => 'Main Menu Styling',
      'description' => 'Basic layout and styling for main menu elements.',
      'file' => 'main-menus.css',
      'status' => $status['scss_main_menus'],
    ),
    'scss_messages' => array(
      'title' => 'Messages',
      'description' => 'Custom styles for Drupal system messages.',
      'file' => 'messages.css',
      'status' => $status['scss_messages'],
    ),
    'scss_pagers' => array(
      'title' => 'Pagers',
      'description' => 'Custom styles for Drupal pagers.',
      'file' => 'pagers.css',
      'status' => $status['scss_pagers'],
    ),
    'scss_tabs' => array(
      'title' => 'Local Task Tabs',
      'description' => 'Custom styles for Drupal tabs.',
      'file' => 'tabs.css',
      'status' => $status['scss_tabs'],
    ),
  );
}

/*
function _omega_getBreakpointId($theme) {
  // get the appropriate id based on theme name
  if (entity_load('breakpoint_group', 'theme.'.$theme.'.'.$theme)) {
    // custom theme breakpoints
    return 'theme.'.$theme.'.'.$theme;
  }
  else {
    // default omega breakpoints
    return 'theme.omega.omega';
  }
}
*/



function _omega_compile_layout_css($scss, $options) {
  $parser = new SassParser($options);
  
  // create CSS from SCSS
  $css = $parser->toCss($scss, false);
  //dsm($css);
  return $css;
}







function _omega_save_layout_files($scss, $css, $json, $theme, $layout) {
  global $base_path;
  // going to overwrite some stuff
  $layoutscss = realpath(".") . $base_path . drupal_get_path('theme', $theme) . '/style/scss/layout/'.$layout.'.scss';
  $layoutcss = realpath(".") . $base_path . drupal_get_path('theme', $theme) . '/style/css/layout/'.$layout.'.css';
  $layoutjson = realpath(".") . $base_path . drupal_get_path('theme', $theme) . '/layouts/'.$layout.'.json';
  //dsm($base_path);
  $scssfile = file_unmanaged_save_data($scss, $layoutscss, FILE_EXISTS_REPLACE);
  if ($scssfile) {
    drupal_set_message(t('SCSS file saved: <strong>'. str_replace(realpath(".") . $base_path, "", $scssfile) .'</strong>'));
  }
  else {
    drupal_set_message(t('WTF001: SCSS save error... : function _omega_save_layout_files()'), 'error');
  }
  
  $cssfile = file_unmanaged_save_data($css, $layoutcss, FILE_EXISTS_REPLACE);
  if ($cssfile) {
    drupal_set_message(t('CSS file saved: <strong>'.str_replace(realpath(".") . $base_path, "", $cssfile).'</strong>'));
  }
  else {
    drupal_set_message(t('WTF002: CSS save error... : function _omega_save_layout_files()'), 'error');
  }
  
  $jsonfile = file_unmanaged_save_data($json, $layoutjson, FILE_EXISTS_REPLACE); 
  if ($jsonfile) {
    drupal_set_message(t('JSON file saved: <strong>'.str_replace(realpath(".") . $base_path, "", $jsonfile).'</strong>'));
  }
  else {
    drupal_set_message(t('WTF003: JSON save error... : function _omega_save_layout_files()'), 'error');
  }
}


function _omega_save_database_layouts($layout, $name, $theme) {
  
  // Save all the things to the database
  $dbLayouts = is_array(variable_get('theme_' . $theme . '_layouts')) ? variable_get('theme_' . $theme . '_layouts') : array();
  
  $updatedLayout = array(
    $name => array(
    'data' => $layout[$name],
    ),
  );
  
  if (!isset($dbLayouts[$name])) {
    $dbLayouts[$name] = array();
  }
  
  // create a var with the merged values
  $newLayout = array_replace_recursive($dbLayouts[$name], $updatedLayout[$name]);
  
  // assign the variable back to the array from the DB.
  $dbLayouts[$name] = $newLayout;
  
  // set/override the variable
  variable_set('theme_' . $theme . '_layouts', $dbLayouts);
  
  // should do some testing here prior to returning true.
  return true;
}

function omega_pretty_json($json) {

  $result      = '';
  $pos         = 0;
  $strLen      = strlen($json);
  $indentStr   = '    ';
  $newLine     = "\n";
  $prevChar    = '';
  $outOfQuotes = true;

  for ($i=0; $i<=$strLen; $i++) {

    // Grab the next character in the string.
    $char = substr($json, $i, 1);

    // Are we inside a quoted string?
    if ($char == '"' && $prevChar != '\\') {
      $outOfQuotes = !$outOfQuotes;
    
    // If this character is the end of an element, 
    // output a new line and indent the next line.
    } else if(($char == '}' || $char == ']') && $outOfQuotes) {
      $result .= $newLine;
      $pos --;
      for ($j=0; $j<$pos; $j++) {
        $result .= $indentStr;
      }
    }
    
    // Add the character to the result string.
    $result .= $char;

    // If the last character was the beginning of an element, 
    // output a new line and indent the next line.
    if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
      $result .= $newLine;
      if ($char == '{' || $char == '[') {
        $pos ++;
      }
        
      for ($j = 0; $j < $pos; $j++) {
        $result .= $indentStr;
      }
    }
    
    $prevChar = $char;
  }

  return $result;
}






















































function _omega_compile_layout_sass($layout, $layoutName, $theme = 'omega', $options) {
  global $base_path;
  // get a list of themes
  $themes = list_themes();
  
  
  $themeSettings = $themes[$theme];
  $breakpoints = $themeSettings->info['breakpoints'];
  $regionGroups = $themeSettings->info['region_groups'];
  
  //$defaultLayout = theme_get_setting('default_layout', $theme);
  $defaultLayout = $layoutName;
  $layouts = theme_get_setting('layouts', $theme);

  $theme_regions = $themeSettings->info['regions'];
  
  // create variable to hold all SCSS we need
  $scss = '';
  
  
  $parser = new SassParser($options);
  
  // get the variables for the theme
  $vars = realpath(".") . $base_path . drupal_get_path('theme', 'omega') . '/style/scss/vars.scss';
  $omegavars = new SassFile;
  $varscss = $omegavars->get_file_contents($vars, $parser);
  // set the grid to fluid
  $varscss .= '$twidth: 100%;';
  
  // get the SCSS for the grid system
  $gs = realpath(".") . $base_path . drupal_get_path('theme', 'omega') . '/style/scss/grids/omega.scss';
  $omegags = new SassFile;
  $gsscss = $omegags->get_file_contents($gs, $parser);
  $scss = $varscss . $gsscss;  

  // loop over the media queries
  foreach($breakpoints as $breakpointName => $breakpointMedia) {
    // create a clean var for the scss for this breakpoint
    $breakpoint_scss = '';
    //dsm($breakpointMedia);
    
    // loop over the region groups
    foreach ($regionGroups as $groupId => $groupName ) {
    //dsm($groupId);
      // add row mixin

      $rowname = str_replace("_", "-", $groupId) . '-layout';
      $rowval = $layout[$defaultLayout][$breakpointName][$groupId]['row'];
      $primary_region = $layout[$defaultLayout][$breakpointName][$groupId]['primary_region'];
      $total_regions = count($layout[$defaultLayout][$breakpointName][$groupId]['regions']);
      $maxwidth = $layout[$defaultLayout][$breakpointName][$groupId]['maxwidth'];
      if ($layout[$defaultLayout][$breakpointName][$groupId]['maxwidth_type'] == 'pixel') {
        $unit = 'px';
      }
      else {
        $unit = '%';
      }
// FORMATTED INTENTIONALLY
      $breakpoint_scss .= '
// Breakpoint: ' . $breakpointName . '; Region Group: ' . $groupId . ';
.' . $rowname . ' { 
  @include row(' . $rowval . ');
  max-width: '. $maxwidth . $unit .';
';
// END FORMATTED INTENTIONALLY
      // loop over regions for basic responsive configuration
      foreach($layout[$defaultLayout][$breakpointName][$groupId]['regions'] as $rid => $data) {
        $regionname = str_replace("_", "-", $rid);
// FORMATTED INTENTIONALLY        
        $breakpoint_scss .= '
  // Breakpoint: ' . $breakpointName . '; Region Group: ' . $groupId . '; Region: ' . $rid . ';
  .region--' . $regionname . ' { 
    @include column(' . $layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['width'] . ', ' . $layout[$defaultLayout][$breakpointName][$groupId]['row'] . '); ';
        
        if ($layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['prefix'] > 0) {
          $breakpoint_scss .= '  
    @include prefix(' . $layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['prefix'] . '); ';  
        }
        
        if ($layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['suffix'] > 0) {
        $breakpoint_scss .= '  
    @include suffix(' . $layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['suffix'] . '); ';
        }
        
        if ($layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['push'] > 0) {
        $breakpoint_scss .= '  
    @include push(' . $layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['push'] . '); ';
        }
        
        if ($layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['pull'] > 0) {
        $breakpoint_scss .= '  
    @include pull(' . $layout[$defaultLayout][$breakpointName][$groupId]['regions'][$rid]['pull'] . '); ';
        }
        
        $breakpoint_scss .= '
    margin-bottom: $regionSpacing;
  } 
'; // end of initial region configuration
// END FORMATTED INTENTIONALLY        
      }
      // check to see if primary region is set
      if ($primary_region && $total_regions <= 3) {
// FORMATTED INTENTIONALLY        
        $breakpoint_scss .= '
  // A primary region exists for the '. $groupId .' region group.
  // so we are going to iterate over combinations of available/missing
  // regions to change the layout for this group based on those scenarios.
  
  // 1 missing region
';
// END FORMATTED INTENTIONALLY
        // loop over the regions that are not the primary one again
        $mainRegion = $layout[$defaultLayout][$breakpointName][$groupId]['regions'][$primary_region];
        $otherRegions = $layout[$defaultLayout][$breakpointName][$groupId]['regions'];
        unset($otherRegions[$primary_region]);
        $num_otherRegions = count($otherRegions);
        $cols = $layout[$defaultLayout][$breakpointName][$groupId]['row'];
        $classMatch = array();
        // in order to ensure the primary region we want to assign extra empty space to
        // exists, we use the .with--region_name class so it would only apply if the
        // primary region is present.
        $classCreate = array(
          '.with--'. $primary_region
        );
        
        foreach($otherRegions as $orid => $odata) {
          
          $classCreate[] = '.without--' . $regionname;
          $regionname = str_replace("_", "-", $orid);
          // combine the region widths
          
          
          
          $adjust = _omega_layout_generation_adjust($mainRegion, array($otherRegions[$orid]), $cols);
          
          
// FORMATTED INTENTIONALLY          
          $breakpoint_scss .= '
  &.with--'. $primary_region . '.without--' . $regionname .' {
    .region--' . $primary_region . ' {
      @include column-reset();
      @include column(' . $adjust['width'] . ', ' . $cols . ');';
// END FORMATTED INTENTIONALLY          
          
      // @todo need to adjust for push/pull here
      
      
      // ACK!!! .sidebar-first would need push/pull adjusted if 
      // the sidebar-second is gone
      // this might be IMPOSSIBLE
      
      $pushPullAltered = FALSE;
      
      if ($adjust['pull'] >= 1) {
// FORMATTED INTENTIONALLY          
          $pushPullAltered = TRUE;
          $breakpoint_scss .= '
      @include pull(' . $adjust['pull'] . ');';
// END FORMATTED INTENTIONALLY        
      }
      
      if ($adjust['push'] >= 1) {
// FORMATTED INTENTIONALLY          
          $pushPullAltered = TRUE;
          $breakpoint_scss .= '
      @include push(' . $adjust['push'] . ');';
// END FORMATTED INTENTIONALLY        
      }
      
// FORMATTED INTENTIONALLY          
          $breakpoint_scss .= '
    }'; // end of iteration of condition missing one region
// END FORMATTED INTENTIONALLY
        
        
          // now what if we adjusted the push/pull of the main region, or the 
          // remaining region had a push/pull, we need to re-evaluate the layout for that region
          
          if ($pushPullAltered) {
            // find that other remaining region.
            
            $region_other = $otherRegions;
            unset($region_other[$orid]);
            $region_other_keys = array_keys($region_other);
            $region_other_id = $region_other_keys[0];
            $regionname_other = str_replace("_", "-", $region_other_id);
            $otherRegionWidth = $region_other[$region_other_id]['width'];
            
            
            $breakpoint_scss .= '
    .region--' . $regionname_other . ' {
      @include column-reset();
      @include column(' . $region_other[$region_other_id]['width'] . ', ' . $cols . ');';
// END FORMATTED INTENTIONALLY
            
            
            
            
            // APPEARS to position the remaining (not primary) region
            // BUT the primary region is positioned wrong with push/pull
            
            
            
            // if there is a pull on the primary region, we adjust the push on the remaining one
            if ($adjust['pull'] >= 1) {
// FORMATTED INTENTIONALLY          
              $pushPullAltered = TRUE;
              $breakpoint_scss .= '
      @include push(' . $adjust['width'] . ');';
// END FORMATTED INTENTIONALLY        
            }
            // if there is a push on the primary region, we adjust the pull on the remaining one
            if ($adjust['push'] >= 1) {
// FORMATTED INTENTIONALLY          
              $pushPullAltered = TRUE;
              $breakpoint_scss .= '
      @include pull(' . $adjust['width'] . ');';
// END FORMATTED INTENTIONALLY        
            }
            
            
// FORMATTED INTENTIONALLY          
          $breakpoint_scss .= '
    }'; // end of iteration of condition missing one region
// END FORMATTED INTENTIONALLY
          }
          
        
// FORMATTED INTENTIONALLY
        $breakpoint_scss .= '
  }
'; // end of intial loop of regions to assign individual cases of missing regions first in the scss/css
// END FORMATTED INTENTIONALLY
        
        
        
        } // end foreach loop
        
// FORMATTED INTENTIONALLY
        // throw a comment in the scss
        $breakpoint_scss .= '
  // 2 missing regions
';
// END FORMATTED INTENTIONALLY





          // here we are beginning to loop again, assuming more than just 
          // one region might be missing and to assign to the primary_region accordingly
          
          $classMatch = array();
          //$classCreate = array();
          
          // loop the "other" regions that aren't the primary one again
          foreach($otherRegions as $orid => $odata) {
            $regionname = str_replace("_", "-", $orid);
            
            //$classCreate[] = '.with--'. $primary_region . '.without--' . $regionname;
            
            // now that we are looping, we will loop again to then create
            // .without--sidebar-first.without--sidebar-second.without--sidebar-second
            foreach($otherRegions as $orid2 => $odata2) {
              $regionname2 = str_replace("_", "-", $orid2);
              $notYetMatched = TRUE;
              
              
              if ($regionname != $regionname2) {
                $attemptedTest = array(
                  '.with--'. $primary_region,
                  '.without--' . $regionname,
                  '.without--' . $regionname2,
                );
                asort($attemptedTest);
                //dsm($attemptedTest);
                $attemptedMatch = implode('', $attemptedTest);
                //asort()
                
                if (in_array($attemptedMatch, $classMatch)) {
                  $notYetMatched = FALSE;  
                }
                
                
                
                
                $adjust = _omega_layout_generation_adjust($mainRegion, array($otherRegions[$orid], $otherRegions[$orid2]), $cols);
                
                if ($notYetMatched) {
                  $classCreate = '.with--'. $primary_region . '.without--' . $regionname . '.without--' . $regionname2;
                  
                  
                  $classMatch[] = $attemptedMatch;
                  
                  if (count($classMatch) >= 1) {
                    //dsm($classMatch);  
                  }

            
// FORMATTED INTENTIONALLY          
                  $breakpoint_scss .= '
  &' . $classCreate . ' {
    .region--' . $primary_region . ' {
      @include column-reset();
      @include column(' . $adjust['width'] . ', ' . $cols . ');
';
// END FORMATTED INTENTIONALLY          
          
      // @todo need to adjust for push/pull here

// FORMATTED INTENTIONALLY          
          $breakpoint_scss .= '
    }'; 
// END FORMATTED INTENTIONALLY

// FORMATTED INTENTIONALLY
              $breakpoint_scss .= '
  }
'; 
// END FORMATTED INTENTIONALLY
                } // end if ($notYetMatched)
              } // end if ($regionname != $regionname2)
            
            
              
              
            
            
            
            } // end foreach $otherRegions (2nd loop)
          }  // end foreach $otherRegions (1st loop)
          
          
          
        }  // end if($primary_region)
// FORMATTED INTENTIONALLY      
      $breakpoint_scss .= '
}
'; // end of region group
// END FORMATTED INTENTIONALLY
      
    }
    
    // if not the defualt media query that should apply to all screens
    // we will wrap the scss we've generated in the appropriate media query.
    if ($breakpointName != 'all') {
      $breakpoint_scss = '@media ' . $breakpointMedia . ' { ' . $breakpoint_scss . '
}
';
    }
    
    // add in the SCSS from this breakpoint and add to our SCSS
    $scss .= $breakpoint_scss; 
  }
  //dsm($scss);
  
  return $scss;
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