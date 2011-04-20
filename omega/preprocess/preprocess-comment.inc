<?php

/*
 * @todo
 */
function omega_alpha_preprocess_comment(&$vars) {
  // Prepare the arrays to handle the classes and ids for the node container.
  $vars['attributes_array']['class'] = $vars['classes_array'];
  $vars['attributes_array']['class'][] = 'clearfix';
}