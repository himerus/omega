<?php

/**
 * Returns HTML for a marker for new or updated content.
 *
 * @see theme_mark().
 */
function omega_mark($variables) {
  $type = $variables['type'];

  if ($GLOBALS['user']->uid) {
    if ($type == MARK_NEW) {
      return ' <mark class="marker">' . t('new') . '</span>';
    }
    elseif ($type == MARK_UPDATED) {
      return ' <mark class="marker">' . t('updated') . '</span>';
    }
  }
}
