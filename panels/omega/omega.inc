<?php

/**
 * @file
 * Registers Omega layouts as Panels layouts.
 */

/**
 * Implementation of hook_LAYOUT_panels_layouts()
 */
function omega_omega_panels_layouts() {
  // We cannot use the 'path' property here and anywhere in our layouts declared
  // in omega_panels_get_sublayouts() because panels uses it for both, the icon
  // and the include file in panels_theme() which we don't want. It's something
  // that works for things like 'flexible' layouts but not when the child plugin
  // mechanism is only used for an alternative discovery logic as in this
  // case.
  $path = drupal_get_path('theme', 'omega') . '/panels/omega';

  return array(
    'title' => t('Omega'),
    'category' => t('Omega page layouts'),
    'description' => t('Omega page layouts for use as Panels Everywhere site templates.'),
    'icon' => $path . '/omega.png',
    'theme' => 'omega_panels',
    'admin theme' => 'omega_panels_admin',
    'file' => $path . '/omega.inc',
    'path' => '',
    'get child' => 'omega_panels_get_sublayout',
    'get children' => 'omega_panels_get_sublayouts',
    'regions' => array(),
  );
}

/**
 * Callback to provide a single Omega layout.
 */
function omega_panels_get_sublayout($plugin, $layout, $sublayout) {
  $layouts = omega_panels_get_sublayouts($plugin, $layout);
  if (isset($layouts["$layout:$sublayout"])) {
    return $layouts["$layout:$sublayout"];
  }
}

/**
 * Callback to provide all Omega layouts.
 */
function omega_panels_get_sublayouts($plugin, $layout) {
  require_once drupal_get_path('theme', 'omega') . '/includes/omega.inc';

  $layouts = array();
  if ($items = omega_layouts_info('omega')) {
    foreach ($items as $name => $info) {
      $layouts["$layout:$name"] = array(
        'name' => "$layout:$name",
        'title' => $info['info']['name'],
      ) + $plugin;

      // Panels calls the preview image 'icon'.
      if (isset($info['info']['preview'])) {
        $layouts["$layout:$name"]['icon'] = $info['path'] . '/' . $info['info']['preview'];
      }

      // Various optional elements from the .info file of the layout.
      foreach (array('regions', 'category', 'description') as $key) {
        if (isset($info['info'][$key])) {
          $layouts["$layout:$name"][$key] = $info['info'][$key];
        }
      }
    }
  }

  return $layouts;
}

function theme_omega_panels($variables) {
  return '';
}

function theme_omega_panels_admin($variables) {
  return '';
}
