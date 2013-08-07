<?php

/**
 * Implements hook_preprocess_TEMPLATE().
 */
function ohm_preprocess_node(&$variables) {
  $node = $variables['node'];

  $date = format_date($variables['created'], 'long');

  if (isset($node->comment) && $node->comment) {
    $comments = l(t('Comments (@comment_count)', array('@comment_count' => $variables['comment_count'])), 'node/' . $node->nid, array('fragment' => 'comments'));
    $variables['submitted'] = t('@date | <strong>Author:</strong> !author | !comments', array('@date' => $date, '!author' => $variables['name'], '!comments' => $comments));
  }
  else {
    $variables['submitted'] = t('@date | <strong>Author:</strong> !author', array('@date' => $date, '!author' => $variables['name']));
  }
}
