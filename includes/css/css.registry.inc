<?php

/**
 * @file
 * Main extension file for the 'css' extension.
 */

/**
 * Implements hook_extension_EXTENSION_theme_registry_alter().
 */
function omega_extension_css_theme_registry_alter(&$registry) {
  // Store all configured class rules in the right hook definition and add the
  // class killer hook to the array of preprocess functions.
  if ($rules = theme_get_setting('omega_class_rules')) {
    foreach ($rules as $rule) {
      // Only write valid rules into the registry.
      if ($rule = omega_parse_class_rule($rule)) {
        // Only proceed if the hook that this rule is targeted at exists.
        if (isset($registry[$rule['hook']])) {
          // Store the class rule in the registry for this hook.
          $registry[$rule['hook']]['omega class rules'][] = $rule;

          // Make sure that we don't add the hook multiple times.
          if (!array_search('omega_apply_class_rules', $registry[$rule['hook']]['preprocess functions'])) {
            $registry[$rule['hook']]['includes'][] = drupal_get_path('theme', 'omega') . '/includes/css/css.inc';
            $registry[$rule['hook']]['preprocess functions'][] = 'omega_apply_class_rules';
          }
        }
      }
    }
  }
}
