<?php

/**
* Implements hook_process_html_tag().
*/
function omega_process_html_tag(&$variables) {
  $element = &$variables['element'];
  // Remove type="something" and CDATA prefix/suffix.
  unset($element['#attributes']['type'], $element['#value_prefix'], $element['#value_suffix']);
  // Remove media="all" but leave others unaffected.
  if (isset($element['#attributes']['media']) && $element['#attributes']['media'] == 'all') {
    unset($element['#attributes']['media']);
  }
}
