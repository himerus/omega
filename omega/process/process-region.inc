<?php
// $Id$

/**
 * @file
 * Contents of omega_process_region().
 */

$omega_classes = isset($vars['elements']['#omega_classes']) ? $vars['elements']['#omega_classes'] : array();
$omega_attributes = isset($vars['elements']['#omega_attributes']) ? $vars['elements']['#omega_attributes'] : array();

$vars['classes_array'] = array_merge($vars['classes_array'], $omega_classes);
$vars['attributes_array'] = array_merge($vars['attributes_array'], $omega_attributes);

$vars['classes'] = implode(' ', $vars['classes_array']);
$vars['attributes'] = drupal_attributes($vars['attributes_array']);

// let's handle some special regions

if(isset($vars['elements']['#custom'])) {
	$cvars = $vars['elements']['#custom'];
	foreach($cvars as $var => $val) {
		$vars[$var] = $val;
	}
}

if ($vars['elements']['#region'] == 'branding') {
  //krumo($vars);
}