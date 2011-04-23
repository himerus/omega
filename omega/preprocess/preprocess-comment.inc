<?php

/*
 * @todo
 */
function omega_alpha_preprocess_comment(&$vars) {
  // Prepare the arrays to handle the classes and ids for the node container.
  $vars['attributes_array']['class'] = $vars['classes_array'];
  $vars['attributes_array']['class'][] = 'clearfix';
  
  $vars['datetime'] = format_date($vars['comment']->created, 'custom', 'c');
  $vars['unpublished'] = '';
  if ($vars['status'] == 'comment-unpublished') {
    $vars['unpublished'] = '<div class="unpublished">' . t('Unpublished') . '</div>';
  }
}