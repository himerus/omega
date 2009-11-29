<?php
<<<<<<< HEAD:preprocess/preprocess-maintenance-page.inc
// $Id: preprocess-maintenance-page.inc,v 1.1.2.2 2009/10/12 02:42:28 himerus Exp $
=======
// $Id: preprocess-maintenance-page.inc,v 1.1 2009/07/08 20:07:32 himerus Exp $
>>>>>>> 63c9d0c1bc2fbe6086ea93a046fcff1c467e9bf0:preprocess/preprocess-maintenance-page.inc
$vars['body_attributes'] = array();

$vars['body_attributes']['id'] = 'maintenance-page';

$classes[] = $vars['body_classes'];

$vars['body_attributes']['class'] = implode(' ', $classes);

$vars['attributes'] = drupal_attributes($vars['body_attributes']);
