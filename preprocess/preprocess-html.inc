<?php
// NINESIXTY - Make sure framework styles are placed above all others.
if(isset($vars['css'])) {
  $vars['css_alt'] = omega_css_reorder($vars['css']);
  $vars['styles'] = drupal_get_css($vars['css_alt']);
}