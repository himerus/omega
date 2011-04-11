<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_html(&$vars) {
  foreach (alpha_theme_get_setting('alpha_optional_css', array()) as $item) {
    drupal_add_css($item['path'], array('media' => $item['media']));
  }
}