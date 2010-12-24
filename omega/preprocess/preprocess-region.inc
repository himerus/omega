<?php
// $Id: preprocess-region.inc,v 1.1.2.6 2010/11/29 16:25:34 himerus Exp $

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