<?php
// $Id$
/**
 * @file
 * preprocess-block.inc is an alternate location for storage on items
 * to be ran during hook_preprocess_html.
 */
drupal_add_js('misc/ui/jquery.effects.core.min.js');
drupal_add_js('misc/ui/jquery.effects.bounce.min.js');

$vars['scripts'] = drupal_get_js();