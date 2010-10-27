<?php
// $Id$

/**
 * @file
 * This file contains functions that must be accessed BOTH in template.php
 * AND in theme-settings.php
 * 
 * putting the functions in template.php alone is not enough when using an 
 * alternate administrative interface to call specific php functions related
 * to the Omega theme.
 */

function omega_prepare_zones($zones) {
  return $zones;
}