<?php
// $Id$
/**
 * @file
 * template.php for gamma theme
 */

/**
 * Implementation of THEMEHOOK_settings() function.
 *
 * @param $saved_settings
 *   An array of saved settings for this theme.
 * @return
 *   A form array.
 */
function gamma_form_system_theme_settings_alter(&$form, &$form_state) {
  for ($i = 1; $i <= 24; $i++) {
    $grids[$i] = $i;
  }
  // add setting for user menu
  $form['omega_regions']['branding']['omega_user_menu_width'] = array(
    '#type' => 'select',
    '#title' => t('Width for User Menu'),
    '#default_value' => theme_get_setting('omega_user_menu_width'),
    '#options' => $grids,
    '#description' => t('Width of the user menu which provides different links for the logged in or anonymous user.'),
  );
  // add a text field to prefix the postscript region for sexification of lower regions
  $form['omega_regions']['postscript']['omega_footer_header_tag'] = array(
    '#type' => 'textfield',
    '#title' => t('Custom "Footer" Header'),
    '#size' => 60,
    '#weight' => -100,
    '#default_value' => theme_get_setting('omega_footer_header_tag'),
    '#description' => t('This text will appear above the postscript regions in the "footer" area of the site, and be rendered with an H2 tag... <em>Plain Text only</em>.'),
  );
  // Return the form
  return $form;
}
