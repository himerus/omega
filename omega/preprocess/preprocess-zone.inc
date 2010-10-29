<?php
// $Id$
  
// Figure out why theme suggestions aren't working
//$vars['theme_hook_suggestions'][] = 'zone';
$vars['theme_hook_suggestions'][] = 'zone__' . $vars['type'];
$vars['theme_hook_suggestions'][] = 'zone__' . $vars['zid'];

$vars['attributes_array'] = array(
  'class' => array(
    'clearfix', 
    'container-' . $vars['container_width'],
    'zone-' . $vars['zone_type'],
    'zone-' . $vars['type'],
  ),
  'id' => array($vars['zid'] . '-container'),
);

$zone_classes = explode(' ', theme_get_setting('omega_'. $vars['zid'] . '_zone_extra_classes'));
foreach ($zone_classes as $class) {
  if($class) {
    $vars['attributes_array']['class'][] = $class;
  }
}

$vars['attributes'] = drupal_attributes($vars['attributes_array']);


//krumo($vars);