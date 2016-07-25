<?php

/**
 * @file
 * Functions to support administrative actions in the Omega theme.
 */

require_once('src/phpsass/SassParser.php');
require_once('src/phpsass/SassFile.php');

function _omega_update_style_scss($styles, $theme) {
  // get a list of themes
  $themes = \Drupal::service('theme_handler')->listInfo();
  // get the current settings/info for the theme
  $themeSettings = $themes[$theme];
  
  
  //$styleVariables = new SassFile;
  // create full paths to the scss and css files we will be rendering.
  $styleFile = realpath(".") . base_path() . drupal_get_path('theme', $theme) . '/style/scss/_omega-style-vars.scss';
  $styleData = '@import "omega_mixins";
  
// Basic Color Variables 
';
  
  foreach($styles['colors'] AS $variableName => $colorValue) {
    $styleData .= "$$variableName: #$colorValue;
";
  }
  
  // these are copied from the form api in scss-settings.php. needs to be pulled out 
  // to a reusable variable that can be edited in one place
  $fontStyleValues = array(
    'georgia' => 'Georgia, serif',
    'times' => '"Times New Roman", Times, serif',
    'palatino' => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
    'arial' => 'Arial, Helvetica, sans-serif',
    'helvetica' => '"Helvetica Neue", Helvetica, Arial, sans-serif',
    'arialBlack' => '"Arial Black", Gadget, sans-serif',
    'comicSans' => '"Comic Sans MS", cursive, sans-serif',
    'impact' => 'Impact, Charcoal, sans-serif',
    'lucidaSans' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
    'tahoma' => 'Tahoma, Geneva, sans-serif',
    'trebuchet' => '"Trebuchet MS", Helvetica, sans-serif',
    'verdana' => 'Verdana, Geneva, sans-serif',
    'courier' => '"Courier New", Courier, monospace',
    'lucidaConsole' => '"Lucida Console", Monaco, monospace',
  );
  
  $styleData .= '
// Basic Font Variables
';
  foreach($styles['fonts'] AS $variableName => $fontValue) {
    $styleData .= "$$variableName: ". $fontStyleValues[$fontValue] . ";
";
  }
  
  // save the scss file
  $stylefile = file_unmanaged_save_data($styleData, $styleFile, FILE_EXISTS_REPLACE);
  // check for errors
  if ($stylefile) {
    drupal_set_message(t('SCSS file saved: <strong>'. str_replace(realpath(".") . base_path(), "", $styleFile) .'</strong>'));
  }
  else {
    drupal_set_message(t('WTF004: SCSS save error... : function _omega_update_style_scss()'), 'error');
  }
  
  // If compile is turned off, we'll only be writing the new variables file above.
  // Compass could also handle this process once the variables file is updated.
  // we will only convert them to css should we have the "Compile SCSS" enabled.
  $compile_scss = theme_get_setting('compile_scss', $theme);
  $compile = isset($compile_scss) ? $compile_scss : FALSE;
  if ($compile) {
    // find all our scss files and open/save them as they should include the _omega-style-vars.scss that we've already updated
    $source = realpath(".") . base_path() . drupal_get_path('theme', $theme) . '/style/scss';
    scssDirectoryScan($source, $theme, 'scss');
    
  }
}

function scssDirectoryScan($source, $theme, $filetype = 'scss', $ignore = '/^(\.(\.)?|CVS|_omega-style-vars\.scss|layout|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
  $dir = opendir($source);
  
  while($file = readdir($dir)) {
    if (!preg_match($ignore, $file)) {
      // directory found, call function again on this directory to scan deeper
      if (is_dir($source . '/' . $file)) {
        scssDirectoryScan($source . '/' . $file, $theme, $filetype, $ignore);
      }
      else {
        if (pathinfo($file, PATHINFO_EXTENSION) == $filetype) {
          
          $omegaPath = realpath(".") . base_path() . drupal_get_path('theme', 'omega');
          $themePath = realpath(".") . base_path() . drupal_get_path('theme', $theme);
          
          
          $relativeSource = str_replace(realpath(".") . base_path() . drupal_get_path('theme', $theme), '', $source);
          
          
          // Options for phpsass compiler. Defaults in SassParser.php
          $options = array(
            'style' => 'expanded',
            'cache' => FALSE,
            'debug' => FALSE,
            'filename' => array(
              'dirname' => $relativeSource, 
              'basename' => $file
            ),
            'debug_info' => FALSE,
            'line_numbers' => TRUE,
            'load_paths' => array(
              $themePath . '/style/scss',
              $omegaPath . '/style/scss',
            ),
            //'extensions'     =>  array('compass'=>array()),
            'syntax' => 'scss',
          );

          $parser = new SassParser($options);

          $omegaMixins = $omegaPath . '/style/scss/mixins.scss';
          $omegaVars = $omegaPath . '/style/scss/_omega-default-style-vars.scss';
          $styleVars = $themePath . '/style/scss/_omega-style-vars.scss';
          $fileLocation = $source . '/' . $file;
          $variableFile = new SassFile;
          $variableScss = '';
          $variableScss .= $variableFile->get_file_contents($fileLocation, $parser);
          $css = _omega_compile_css($variableScss, $options);
          
          // path to CSS file we're overriding
          $newCssFile = str_replace('scss', 'css', $fileLocation);
          // save the css file
          $cssfile = file_unmanaged_save_data($css, $newCssFile, FILE_EXISTS_REPLACE);
          // check for errors
          if ($cssfile) {
            drupal_set_message(t('CSS file saved: <strong>'.str_replace(realpath(".") . base_path(), "", $cssfile).'</strong>'));
          }
          else {
            drupal_set_message(t('WTF005: CSS save error... : function scssDirectoryScan()'), 'error');
          }
        }
      }
    }
  }
  closedir($dir);
}

/**
 * Custom function to save the layout changes to appropriate config variables
 * Currently performs the following operations:
 *  - Compares layout submitted to function with the original in database
 *  - If they do not match, performs save() with updated values sent from function call
 *  - Check if $generated flag was passed as TRUE and save updated data to $theme.layout.$layout_id.generated
 *    which signifies that the layout variables HAVE been converted to SCSS/CSS
 *
 *  Difference between $theme.layout.$layout_id and $theme.layout.$layout_id.generated
 *  - $theme.layout.$layout_id = latest layout configuration changes saved to database
 *    - saved to 'config' table on theme install through $theme.layout.$layout_id.yml
 *  - $theme.layout.$layout_id.generated = latest layout configuration changes to be generated into SCSS/CSS
 *    - saved/updated to 'config' table after "Save & Generate Layout" is called
 * @param $layout
 * @param $layout_id
 * @param $theme
 * @param bool $generate
 * @return bool
 */
function _omega_save_database_layout($layout, $layout_id, $theme, $generate = FALSE) {
  // Grab the editable configuration objects
  $layoutConfig = \Drupal::service('config.factory')->getEditable($theme . '.layout.' . $layout_id);
  $layoutConfigGenerated = \Drupal::service('config.factory')->getEditable($theme . '.layout.' . $layout_id . '.generated');
  
  // unset some junk that was passed in the form's $layout array
  // this includes some informational messages, etc.
  unset($layout['breakpoint_group_updated']);
  
  // Check for differences in the $layoutConfig (current stored DB version) and the $layout (passed form values)
  // If and only if there are differences will we continue with saving the layout, otherwise, we'll skip it
  if ($layoutConfig->getOriginal() == $layout) {
    // no updates, throw message (to be removed likely)
    // drupal_set_message(t('The layout <strong>' . $layout_id . '</strong> matches the version already stored at <strong>' . $theme . '.layout.' . $layout_id . '</strong>. No save on this layout was performed.'));
  }
  else {
    /* updates found, proceed */
    
    // Set the value to $layout
    $layoutConfig->setData($layout);
    
    // Save it
    $saved = $layoutConfig->save();
    
    // check for errors
    if ($saved) {
      drupal_set_message(t('Layout <em>' . $layout_id . '</em> updated: <strong>'.$theme . '.layout.' . $layout_id.'</strong>'));
    }
    else {
      drupal_set_message(t('WTF002: Layout configuration error... : function _omega_save_database_layout()'), 'error');
    }
  }
  
  // $theme.layout.$layout_id.generated - We should save current values to .generated
  if ($generate) {
    if ($layoutConfigGenerated->getOriginal() != $layout) {
      $layoutConfigGenerated->setData($layout);
      $saved = $layoutConfigGenerated->save();
      
      if ($saved) {
        drupal_set_message(t('Layout <em>' . $layout_id . '</em> updated: <strong>'.$theme . '.layout.' . $layout_id.'.generated</strong>'));
      }
      else {
        drupal_set_message(t('WTF003: Layout configuration error... : function _omega_save_database_layout()'), 'error');
      }
      return true;
    }
    else {
      //drupal_set_message(t('The layout <strong>' . $layout_id . '</strong> matches the version already stored at <strong>' . $theme . '.layout.' . $layout_id . '.generated</strong>. No save on this layout was performed.'));
      return false;
    }
  }
}


function _omega_compile_layout($layout, $layout_id, $theme) {
  // Options for phpsass compiler. Defaults in SassParser.php
  $options = array(
    'style' => 'nested',
    'cache' => FALSE,
    'syntax' => 'scss',
    'debug' => TRUE,
  );
  
  $scss = _omega_compile_layout_sass($layout, $layout_id, $theme, $options);
  // save the SCSS and CSS files to the theme's filesystem
  _omega_save_layout_files($scss, $theme, $layout_id);
}
/**
 * Custom function to generate layout CSS from SCSS
 * Currently performs the following operations:
 *  - Takes SCSS generated from _omega_compile_layout_sass and returns CSS
 */
function _omega_compile_css($scss, $options) {
  $parser = new SassParser($options);
  // create CSS from SCSS
  $css = $parser->toCss($scss);
  return $css;
}

/**
 * Custom function to generate layout SCSS from layout variables
 * Currently performs the following operations:
 *  - Cycles a given layout for breakpoints
 *  - Cycles a breakpoint for region groups
 *  - Cycles a region group for regions
 *  - Cycles a region for various settings to apply to the region
 *  - Returns SCSS designed to be passed to _omega_compile_css
 */
function _omega_compile_layout_sass($layout, $layoutName, $theme = 'omega', $options) {
  //dsm($layout);
  // get a list of themes
  $themes = \Drupal::service('theme_handler')->listInfo();
  // get the current settings/info for the theme
  $themeSettings = $themes[$theme];
  // get the default layout/breakpoint group
  $defaultLayout = $layoutName;
  // get all the active breakpoints we'll be editing
  $breakpoints = _omega_getActiveBreakpoints($layoutName, $theme);
  //kint($breakpoints);
  // get the stored layout data
  // $layouts = theme_get_setting('layouts', $theme);
  // pull an array of "region groups" based on the "all" media query that should always be present
  // @todo consider adjusting this data to be stored in the top level of the $theme.layout.$layout.yml file instead
  $region_groups = $layout['region_groups']['all'];
  //dsm($region_groups);
  //dsm($layouts);
  $theme_regions = $themeSettings->info['regions'];
  // create variable to hold all SCSS we need
  $scss = '';
 
  $parser = new SassParser($options);

  // If we are set to compile scss, we include it directly,
  // Otherwise, we use @import for compass.
  // @todo - This may change when switching scss php compilers soon
  $compile_scss = theme_get_setting('compile_scss', $theme);
  $compile = isset($compile_scss) ? $compile_scss : FALSE;
  if ($compile) {
    // get the variables for the theme
    $vars = realpath(".") . base_path() . drupal_get_path('theme', 'omega') . '/style/scss/_omega-default-style-vars.scss';
    $omegavars = new SassFile;
    $varscss = $omegavars->get_file_contents($vars, $parser);
    // set the grid to fluid
    $varscss .= '$twidth: 100%;';

    // get the SCSS for the grid system
    $gs = realpath(".") . base_path() . drupal_get_path('theme', 'omega') . '/style/scss/grids/_omegags.scss';
    $omegags = new SassFile;
    $gsscss = $omegags->get_file_contents($gs, $parser);
    $scss = $varscss . $gsscss;
  }
  else {
    $scss = '$twidth: 100%; // setting grid to fluid 
@import "omega_mixins", "omega-style-vars", "omega-default-style-vars", "omegags";

';
  }
  

  // loop over the media queries
  foreach($breakpoints as $breakpoint) {
    // create a clean var for the scss for this breakpoint
    $breakpoint_scss = '';
    $idtrim = omega_return_clean_breakpoint_id($breakpoint);
    
    // loop over the region groups
    foreach ($region_groups as $gid => $info ) {
      /* add row mixin */
      // @todo change $layout['region_groups'][$idtrim][$gid] to $info
      $rowname = str_replace("_", "-", $gid) . '-layout';
      $rowval = $layout['region_groups'][$idtrim][$gid]['row'];
      $primary_region = $layout['region_groups'][$idtrim][$gid]['primary_region'];
      $total_regions = count($layout['region_groups'][$idtrim][$gid]['regions']);
      $maxwidth = $layout['region_groups'][$idtrim][$gid]['maxwidth'];
      if ($layout['region_groups'][$idtrim][$gid]['maxwidth_type'] == 'pixel') {
        $unit = 'px';
      }
      else {
        $unit = '%';
      }
      if ($maxwidth && $rowval) {
/* FORMATTED INTENTIONALLY */
      $breakpoint_scss .= '
// Breakpoint: ' . $breakpoint->getLabel() . '; Region Group: ' . $gid . ';
.' . $rowname . ' { 
  @include row(' . $rowval . ');
  max-width: '. $maxwidth . $unit .';
';
/* END FORMATTED INTENTIONALLY */
      }
      // loop over regions for basic responsive configuration
      foreach($layout['region_groups'][$idtrim][$gid]['regions'] as $rid => $data) {
        $regionname = str_replace("_", "-", $rid);
/* FORMATTED INTENTIONALLY */        
        $breakpoint_scss .= '
  // Breakpoint: ' . $breakpoint->getLabel() . '; Region Group: ' . $gid . '; Region: ' . $rid . ';
  .region--' . $regionname . ' { 
    @include column(' . $layout['region_groups'][$idtrim][$gid]['regions'][$rid]['width'] . ', ' . $layout['region_groups'][$idtrim][$gid]['row'] . '); ';
        
        if ($layout['region_groups'][$idtrim][$gid]['regions'][$rid]['prefix'] > 0) {
          $breakpoint_scss .= '  
    @include prefix(' . $layout['region_groups'][$idtrim][$gid]['regions'][$rid]['prefix'] . '); ';  
        }
        
        if ($layout['region_groups'][$idtrim][$gid]['regions'][$rid]['suffix'] > 0) {
        $breakpoint_scss .= '  
    @include suffix(' . $layout['region_groups'][$idtrim][$gid]['regions'][$rid]['suffix'] . '); ';
        }
        
        if ($layout['region_groups'][$idtrim][$gid]['regions'][$rid]['push'] > 0) {
        $breakpoint_scss .= '  
    @include push(' . $layout['region_groups'][$idtrim][$gid]['regions'][$rid]['push'] . '); ';
        }
        
        if ($layout['region_groups'][$idtrim][$gid]['regions'][$rid]['pull'] > 0) {
        $breakpoint_scss .= '  
    @include pull(' . $layout['region_groups'][$idtrim][$gid]['regions'][$rid]['pull'] . '); ';
        }
        
        $breakpoint_scss .= '
    margin-bottom: $regionSpacing;
  } 
'; // end of initial region configuration
/* END FORMATTED INTENTIONALLY */        
      }
      // check to see if primary region is set
      if ($primary_region && $total_regions <= 3) {
/* FORMATTED INTENTIONALLY */        
        $breakpoint_scss .= '
  // A primary region exists for the '. $gid .' region group.
  // so we are going to iterate over combinations of available/missing
  // regions to change the layout for this group based on those scenarios.
  
  // 1 missing region
';
/* END FORMATTED INTENTIONALLY */
        // loop over the regions that are not the primary one again
        $mainRegion = $layout['region_groups'][$idtrim][$gid]['regions'][$primary_region];
        $otherRegions = $layout['region_groups'][$idtrim][$gid]['regions'];
        unset($otherRegions[$primary_region]);
        $num_otherRegions = count($otherRegions);
        $cols = $layout['region_groups'][$idtrim][$gid]['row'];
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
          
/* FORMATTED INTENTIONALLY */          
          $breakpoint_scss .= '
  &.with--'. $primary_region . '.without--' . $regionname .' {
    .region--' . $primary_region . ' {
      @include column-reset();
      @include column(' . $adjust['width'] . ', ' . $cols . ');';
/* END FORMATTED INTENTIONALLY */
      // @todo need to adjust for push/pull here
      // ACK!!! .sidebar-first would need push/pull adjusted if 
      // the sidebar-second is gone
      // this might be IMPOSSIBLE
      $pushPullAltered = FALSE;
      
      if ($adjust['pull'] >= 1) {
/* FORMATTED INTENTIONALLY */          
          $pushPullAltered = TRUE;
          $breakpoint_scss .= '
      @include pull(' . $adjust['pull'] . ');';
/* END FORMATTED INTENTIONALLY */        
      }
      
      if ($adjust['push'] >= 1) {
/* FORMATTED INTENTIONALLY */          
          $pushPullAltered = TRUE;
          $breakpoint_scss .= '
      @include push(' . $adjust['push'] . ');';
/* END FORMATTED INTENTIONALLY */        
      }
      
/* FORMATTED INTENTIONALLY */          
          $breakpoint_scss .= '
    }'; // end of iteration of condition missing one region
/* END FORMATTED INTENTIONALLY */
        
        
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
/* END FORMATTED INTENTIONALLY */

            // APPEARS to position the remaining (not primary) region
            // BUT the primary region is positioned wrong with push/pull
            // if there is a pull on the primary region, we adjust the push on the remaining one
            if ($adjust['pull'] >= 1) {
/* FORMATTED INTENTIONALLY */          
              $pushPullAltered = TRUE;
              $breakpoint_scss .= '
      @include push(' . $adjust['width'] . ');';
/* END FORMATTED INTENTIONALLY */        
            }
            // if there is a push on the primary region, we adjust the pull on the remaining one
            if ($adjust['push'] >= 1) {
/* FORMATTED INTENTIONALLY */          
              $pushPullAltered = TRUE;
              $breakpoint_scss .= '
      @include pull(' . $adjust['width'] . ');';
/* END FORMATTED INTENTIONALLY */        
            }
            
            
/* FORMATTED INTENTIONALLY */          
          $breakpoint_scss .= '
    }'; // end of iteration of condition missing one region
/* END FORMATTED INTENTIONALLY */
          }
          
        
/* FORMATTED INTENTIONALLY */
        $breakpoint_scss .= '
  }
'; // end of intial loop of regions to assign individual cases of missing regions first in the scss/css
/* END FORMATTED INTENTIONALLY */
        
        
        
        } /* end foreach loop*/ 
        
/* FORMATTED INTENTIONALLY */
        // throw a comment in the scss
        $breakpoint_scss .= '
  // 2 missing regions
';
/* END FORMATTED INTENTIONALLY */

          // here we are beginning to loop again, assuming more than just 
          // one region might be missing and to assign to the primary_region accordingly
          $classMatch = array();
          
          // loop the "other" regions that aren't the primary one again
          foreach($otherRegions as $orid => $odata) {
            $regionname = str_replace("_", "-", $orid);
            
            // now that we are looping, we will loop again to then create
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

            
/* FORMATTED INTENTIONALLY */          
                  $breakpoint_scss .= '
  &' . $classCreate . ' {
    .region--' . $primary_region . ' {
      @include column-reset();
      @include column(' . $adjust['width'] . ', ' . $cols . ');
';
/* END FORMATTED INTENTIONALLY */          
          
      // @todo need to adjust for push/pull here
/* FORMATTED INTENTIONALLY */          
          $breakpoint_scss .= '
    }'; 
/* END FORMATTED INTENTIONALLY */

/* FORMATTED INTENTIONALLY */
              $breakpoint_scss .= '
  }
'; 
/* END FORMATTED INTENTIONALLY */
                } // end if ($notYetMatched)
              } // end if ($regionname != $regionname2)
            } // end foreach $otherRegions (2nd loop)
          }  // end foreach $otherRegions (1st loop)
        }  // end if($primary_region)
/* FORMATTED INTENTIONALLY */      
      $breakpoint_scss .= '
}
'; // end of region group
/* END FORMATTED INTENTIONALLY */
      
    }
    
    // if not the defualt media query that should apply to all screens
    // we will wrap the scss we've generated in the appropriate media query.
    if ($breakpoint->getLabel() != 'all') {
      $breakpoint_scss = '@media ' . $breakpoint->getMediaQuery() . ' { ' . $breakpoint_scss . '
}
';
    }
    
    // add in the SCSS from this breakpoint and add to our SCSS
    $scss .= $breakpoint_scss;
    //dsm($breakpoint_scss);
  }
  return $scss;
}

/**
 * Function to take SCSS/CSS data and save to appropriate files
 */ 

function _omega_save_layout_files($scss, $theme, $layout_id) {
  // create full paths to the scss and css files we will be rendering.
  $layoutscss = realpath(".") . base_path() . drupal_get_path('theme', $theme) . '/style/scss/layout/' . $layout_id . '-layout.scss';
  $layoutcss = realpath(".") . base_path() . drupal_get_path('theme', $theme) . '/style/css/layout/' . $layout_id . '-layout.css';
  
  // save the scss file
  $scssfile = file_unmanaged_save_data($scss, $layoutscss, FILE_EXISTS_REPLACE);
  // check for errors
  if ($scssfile) {
    drupal_set_message(t('SCSS file saved: <strong>'. str_replace(realpath(".") . base_path(), "", $scssfile) .'</strong>'));
  }
  else {
    drupal_set_message(t('WTF001: SCSS save error... : function _omega_save_layout_files()'), 'error');
  }

  // if the Compile SCSS option is enabled, continue
  $compile_scss = theme_get_setting('compile_scss', $theme);
  $compile = isset($compile_scss) ? $compile_scss : FALSE;
  if ($compile) {

    // generate the CSS from the SCSS created above
    $css = _omega_compile_css($scss, $options);
    // save the css file
    $cssfile = file_unmanaged_save_data($css, $layoutcss, FILE_EXISTS_REPLACE);
    // check for errors
    if ($cssfile) {
      drupal_set_message(t('CSS file saved: <strong>'.str_replace(realpath(".") . base_path(), "", $cssfile).'</strong>'));
    }
    else {
      drupal_set_message(t('WTF002: CSS save error... : function _omega_save_layout_files()'), 'error');
    }
  }
  // else throw a warning/reminder that it IS disabled and they should be using compass or alternative compiler.
  elseif (theme_get_setting('show_compile_warning', $theme)) {
    drupal_set_message(t("Since <strong>Compile SCSS Directly</strong> is disabled, please ensure Compass or an alternative SCSS compiler is set to watch for these saved changes. You can disable this warning under <strong>Default Options</strong>."), "warning");
  }

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

// returns select field options for the available layouts
function _omega_layout_select_options($layouts) {
  $options = array();
  foreach($layouts as $id => $info) {
    //$options[$id] = $info['theme'] . '--' . $info['name'];
    $options[$id] = $id;
  }
  //dsm($options);
  return $options;
}