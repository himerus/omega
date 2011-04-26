<?php

require_once dirname(__FILE__) . '/includes/omega.inc';

/**
 * Implements hook_alpha_settings_alter().
 */
function omega_alpha_settings_alter(&$settings, $theme) {
  $settings['omega'] = array(
    'formalize' => alpha_theme_get_setting('omega_formalize', $theme, TRUE),
  );
}