<?php

/**
 * @file
 * Main extension file for the 'development' extension.
 */

/**
 * Implements hook_extension_EXTENSION_registry_alter().
 */
function omega_extension_layouts_theme_registry_alter(&$registry) {
  $registry['page']['includes'][] = drupal_get_path('theme', 'omega') . '/includes/layouts/layouts.inc';
  $registry['page']['process functions'][] = 'omega_extension_layouts_process_layout';

  foreach (omega_layouts_info() as $layout => $info) {
    $suggestion = 'page__' . $layout . '_layout';

    if (isset($registry[$suggestion])) {
      // Look up possible CSS and JS file overrides.
      foreach (array('js', 'css') as $type) {
        if (!empty($registry[$suggestion]['layout']['attached'][$type])) {
          foreach ($registry[$suggestion]['layout']['attached'][$type] as $file => &$options) {
            if (!is_array($options)) {
              $options = array('type' => $options);
            }

            // We only look for local files that do not have an explicitly
            // defined path via the 'data' option.
            if ((isset($options['type']) && $options['type'] != 'file') || isset($options['data'])) {
              continue;
            }

            if ($path = omega_theme_trail_find_file($file, $registry[$suggestion]['layout']['path'])) {
              $options['data'] = $path;
              continue;
            }

            // If we get to this point, the file could not be found.
            unset($registry[$suggestion]['layout']['attached'][$type][$file]);
          }
        }
      }

      // Look up layout specific include files.
      foreach (omega_theme_trail_find_files($layout . '.inc', $registry[$suggestion]['layout']['path']) as $key => $path) {
        $registry[$suggestion]['layout']['includes'][] = $path;
        $registry[$suggestion]['layout']['hooks'][] = $key . '_' . $layout . '_layout_callback';
      }
    }
  }
}