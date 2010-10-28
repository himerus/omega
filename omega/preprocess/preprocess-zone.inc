<?php
// $Id$
//krumo($vars);
  //drupal_set_message('<strong>preprocess-zone.inc</strong> was just called...');
  
  // Clean up name so there are no underscores.
  $vars['theme_hook_suggestions'][] = 'zone__' . $vars['type'];
  $vars['theme_hook_suggestions'][] = 'zone__' . $vars['zid'];
  
  //krumo($vars);