<?php
// $Id: preprocess-region.inc,v 1.1.2.7 2010/12/28 18:14:32 himerus Exp $

/**
 * @file
 * Contents of omega_preprocess_region().
 */

// add a 'sidebar' class to the sidebar_first sidebar_second
switch($vars['region']) {
  case 'sidebar_first':
  case 'sidebar_second':  
    $vars['classes_array'][] = 'sidebar';
    break;
  
}