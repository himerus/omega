<?php
// $Id: preprocess-maintenance-page.inc,v 1.1 2009/07/08 20:07:32 himerus Exp $
$vars['body_attributes'] = array();

$vars['body_attributes']['id'] = 'maintenance-page';

$classes[] = $vars['body_classes'];

$vars['body_attributes']['class'] = implode(' ', $classes);

$vars['attributes'] = drupal_attributes($vars['body_attributes']);
