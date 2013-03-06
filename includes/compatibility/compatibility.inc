<?php

/**
 * @file
 * Main extension file for the 'compatibility' extension.
 */

/**
 * Implements hook_extension_EXTENSION_registry_alter().
 */
function omega_extension_compatibility_theme_registry_alter(&$registry) {
  $registry['html']['includes'][] = drupal_get_path('theme', 'omega') . '/includes/compatibility/compatibility.inc';
  $registry['html']['preprocess functions'][] = 'omega_extension_compatibility_preprocess_html';

  if (omega_theme_get_setting('omega_apple_touch', TRUE)) {
    // Cache the array of apple touch icons if they are enabled.
    $registry['html']['apple-touch-icon'] = omega_theme_trail_info('apple', FALSE);
  }
}

/**
 * Implements hook_extension_EXTENSION_preproces_html().
 */
function omega_extension_compatibility_preprocess_html(&$variables) {
  // Add the 'viewport' meta tag.
  if (omega_theme_get_setting('omega_viewport', TRUE)) {
    $meta = array(
      '#tag' => 'meta',
      '#attributes' => array('name' => 'viewport', 'content' => 'width=device-width'),
    );

    drupal_add_html_head($meta, 'omega-viewport');
  }

  // Add the 'HandheldFriendly' meta tag.
  if (omega_theme_get_setting('omega_handheld_friendly', TRUE)) {
    $meta = array(
      '#tag' => 'meta',
      '#attributes' => array('name' => 'HandheldFriendly', 'content' => 'true'),
    );

    drupal_add_html_head($meta, 'omega-handheld-friendly');
  }

  // Add a the 'MobileOptimized' meta tag.
  if (omega_theme_get_setting('omega_mobile_optimized', TRUE)) {
    $meta = array(
      '#tag' => 'meta',
      '#attributes' => array('name' => 'MobileOptimized', 'content' => 'width'),
    );

    drupal_add_html_head($meta, 'omega-mobile-optimized');
  }

  // Add the 'cleartype' meta tag.
  if (omega_theme_get_setting('omega_cleartype', TRUE)) {
    $meta = array(
      '#tag' => 'meta',
      '#attributes' => array('http-equiv' => 'cleartype', 'content' => 'on'),
    );

    drupal_add_html_head($meta, 'omega-cleartype');
  }

  // Add Apple touch icons.
  if (omega_theme_get_setting('omega_apple_touch', TRUE)) {
    $registry = theme_get_registry(FALSE);

    // Iterate over the array of touch icons and attributes.
    $path = drupal_get_path('theme', $registry['html']['apple-touch-icon']['theme']);
    foreach($registry['html']['apple-touch-icon']['info'] as $sizes => $icon) {
      $meta = array(
        '#tag' => 'link',
        '#attributes' => array(
          'rel' => 'apple-touch-icon',
          'href' => file_create_url($path . '/' . $icon),
        ),
      );

      if ($sizes != 'default') {
        $meta['#attributes']['sizes'] = $sizes;
      }

      drupal_add_html_head($meta, 'omega-touch-' . $sizes);
    }
  }

  // Send X-UA-Compatible HTTP header to force IE to use the most recent
  // rendering engine or use Chrome's frame rendering engine if available.
  if (omega_theme_get_setting('omega_chrome_edge', TRUE)) {
    $supported = omega_theme_get_setting('omega_internet_explorer_support');

    if (drupal_get_http_header('X-UA-Compatible') === NULL) {
      // Only add the header if it hasn't been added before.
      drupal_add_http_header('X-UA-Compatible', 'IE=edge,chrome=IE' . $supported);
    }

    if (omega_theme_get_setting('omega_chrome_popup', FALSE)) {
      // Automatically open the Google Chrome Frame installation iframe.
      drupal_add_js('//ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js', array(
        'type' => 'external',
        'scope' => 'footer',
        'weight' => 1000,
        'browsers' => array(
          'IE' => !$supported ? TRUE : 'lte IE ' . $supported,
          '!IE' => FALSE,
        ),
      ));

      drupal_add_js('window.attachEvent("onload",function(){CFInstall.check({mode:"overlay"});})', array(
        'type' => 'inline',
        'scope' => 'footer',
        'weight' => 1000,
        'browsers' => array(
          'IE' => !$supported ? TRUE : 'lte IE ' . $supported,
          '!IE' => FALSE,
        ),
      ));
    }
  }
}
