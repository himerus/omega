<?php

/**
 * @file
 * Contains the theme settings form elements for the compatibility extension.
 */

/**
 * Implements hook_extension_EXTENSION_theme_settings_form_alter().
 */
function omega_extension_compatibility_settings_form($element, &$form, $form_state) {
  $element['omega_conditional_classes_html'] = array(
    '#type' => 'checkbox',
    '#title' => t('Add conditional classes for Internet Explorer'),
    '#description' => t('Adds conditional classes (for Internet Explorer) to the &lt;html&gt;.'),
    '#default_value' => omega_theme_get_setting('omega_conditional_classes_html', TRUE),
  );

  $element['omega_metatags'] = array(
    '#type' => 'fieldset',
    '#title' => t('Metatags and HTTP headers'),
  );

  $element['omega_metatags']['omega_cleartype'] = array(
    '#type' => 'checkbox',
    '#title' => t('Cleartype support for Windows'),
    '#description' => t('Mobile Internet Explorer allows us to activate ClearType technology for smoothing fonts for easy reading.'),
    '#default_value' => omega_theme_get_setting('omega_cleartype', TRUE),
  );

  $element['omega_metatags']['omega_handheld_friendly'] = array(
    '#type' => 'checkbox',
    '#title' => t('Handheld friendly'),
    '#description' => t('The HandheldFriendly meta-tag was used initially by older Palm and Blackberry models as well as browsers like AvantGo.'),
    '#default_value' => omega_theme_get_setting('omega_handheld_friendly', TRUE),
  );

  $element['omega_metatags']['omega_mobile_optimized'] = array(
    '#type' => 'checkbox',
    '#title' => t('Mobile Optimized'),
    '#description' => t('Microsoft introduced the MobileOptimized tag for the PocketPC.'),
    '#default_value' => omega_theme_get_setting('omega_mobile_optimized', TRUE),
  );

  $element['omega_metatags']['omega_viewport'] = array(
    '#type' => 'checkbox',
    '#title' => t('Viewport'),
    '#description' => t('This is more widely supported by modern smartphones, including but not limited to: iOS, Android, Palm Pre, Blackberry, Windows Phone 7.'),
    '#default_value' => omega_theme_get_setting('omega_viewport', TRUE),
  );

  $element['omega_metatags']['omega_chrome_edge'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Chrome Edge support for Internet Explorer'),
    '#description' => t('Edge mode tells Internet Explorer to display content in the highest mode available. With Internet Explorer 9, this is equivalent to IE9 mode. If a (hypothetical) future release of Internet Explorer supported a higher compatibility mode, pages set to edge mode would appear in the highest mode supported by that version. Those same pages would still appear in IE9 mode when viewed with Internet Explorer 9.'),
    '#default_value' => omega_theme_get_setting('omega_chrome_edge', TRUE),
  );

  return $element;
}
