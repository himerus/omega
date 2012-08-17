<?php

/**
 * @file
 * Main extension file for the 'scripts' extension.
 */

/**
 * Extension callback for attaching enabled libraries.
 */
function omega_extension_scripts_attach_libraries($variables) {
  $registry = theme_get_registry();

  // Check if there are any enabled libraries.
  foreach ($registry['html']['libraries'] as $library => $info) {
    drupal_process_attached(array('#attached' => $info['attached']));

    if ($info['callback']) {
      $info['callback']($library, $info['settings']);
    }
  }
}