<?php

/**
 * Implements hook_preprocess_comment().
 */
function omega_preprocess_comment(&$variables) {
  $comment = $variables['elements']['#comment'];
  $variables['user_picture'] = theme_get_setting('toggle_comment_user_picture') ? theme('user_picture', array('account' => $comment)) : '';
  $variables['content_attributes_array']['class'][] = 'content';
}
