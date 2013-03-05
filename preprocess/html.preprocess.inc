<?php

/**
 * Implements hook_preprocess_html().
 */
function omega_preprocess_html(&$variables, $hook) {
  // Return early, so the maintenance page does not call any of the code below.
  if ($hook != 'html') {
    return;
  }

  // Serialize RDF Namespaces into an RDFa 1.1 prefix attribute.
  if ($variables['rdf_namespaces']) {
    $prefixes = array();
    foreach (explode("\n  ", ltrim($variables['rdf_namespaces'])) as $namespace) {
      // Remove xlmns: and ending quote and fix prefix formatting.
      $prefixes[] = str_replace('="', ': ', substr($namespace, 6, -1));
    }
    $variables['rdf_namespaces'] = ' prefix="' . implode(' ', $prefixes) . '"';
  }

  // Classes for body element. Allows advanced theming based on context
  // (home page, node of certain type, etc.).
  if (!$variables['is_front']) {
    // Add unique class for each page.
    $path = drupal_get_path_alias($_GET['q']);
    // Add unique class for each section of the website.
    list($section, ) = explode('/', $path, 2);
    $arg = explode('/', $_GET['q']);

    if ($arg[0] == 'node' && isset($arg[1])) {
      if ($arg[1] == 'add') {
        $section = 'node-add';
      }
      elseif (isset($arg[2]) && is_numeric($arg[1]) && ($arg[2] == 'edit' || $arg[2] == 'delete')) {
        $section = 'node-' . $arg[2];
      }
    }

    $variables['attributes_array']['class'][] = drupal_html_class('section-' . $section);
  }

  // Add some styles in case the Omega theme is the active theme.
  if ($GLOBALS['theme'] == 'omega') {
    _omega_preprocess_html($variables);
  }

  // Build array of touch icons and attributes.
  $path = drupal_get_path('theme', $GLOBALS['theme']);
  $touch_icons = array(
    // For third-generation iPad with high-resolution Retina display.
    'apple-144x144' => array(
      '#tag' => 'link',
      '#attributes' => array(
        'rel' => 'apple-touch-icon',
        'sizes' => '144x144',
        'href' => file_create_url($path . '/apple-touch-icon-144x144.png'),
      ),
    ),
    // For iPhone with high-resolution Retina display.
    'apple-114x114' => array(
      '#tag' => 'link',
      '#attributes' => array(
        'rel' => 'apple-touch-icon',
        'sizes' => '114x114',
        'href' => file_create_url($path . '/apple-touch-icon-114x114.png'),
      ),
    ),
    // For first and second-generation iPad.
    'apple-72x72' => array(
      '#tag' => 'link',
      '#attributes' => array(
        'rel' => 'apple-touch-icon',
        'sizes' => '72x72',
        'href' => file_create_url($path . '/apple-touch-icon-72x72.png'),
      ),
    ),
    // For non-Retina iPhone, iPod Touch, and Android 2.1+ devices.
    'apple' => array(
      '#tag' => 'link',
      '#attributes' => array(
        'rel' => 'apple-touch-icon',
        'href' => file_create_url($path . '/apple-touch-icon.png'),
      ),
    ),
  );
  // Add the touch icons to the HTML head.
  foreach($touch_icons as $touch_icon_name => $touch_icon) {
    drupal_add_html_head($touch_icon, 'omega-touch-' . $touch_icon_name);
  }
}

/**
 * Helper function for loading theme assets in case 'Omega' is the active theme.
 *
 * @see omega_preprocess_html()
 */
function _omega_preprocess_html(&$variables) {
  // @todo
}
