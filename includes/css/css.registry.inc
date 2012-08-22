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

/**
 * Fully parses a class rule and transforms it into its array representation.
 *
 * @param $rule
 *   The class rule string.
 *
 * @return mixed
 *   The array representation of the class rule or FALSE if there was a syntax
 *   error.
 */
function omega_parse_class_rule($rule) {
  if (preg_match('/^(\+|-)([a-z_]+)(:([a-z_])*)?\[(([a-z\-]+|(\*(?!\*))|\[[a-zA-Z0-9_# ]+(:[a-zA-Z0-9_# ]+)*\])+)\]$/', $rule, $matches)) {
    if ($matches[1] == '+' && strpos($rule, '*') !== FALSE) {
      // 'Add' rules don't support wildcards of course.
      return FALSE;
    }

    // Replace all wildcards with proper regex.
    $rule = str_replace('*', '(.*)', $matches[5]);

    $output = array(
      'action' => $matches[1] == '-' ? 'remove' : 'add',
      'hook' => $matches[2],
      'subtype' => $matches[4] ? $matches[4] : FALSE,
      'class' => $rule,
      'tokens' => array(),
      'regex' => $rule != $matches[5],
    );

    // If there are any tokens in our rule, extract them now.
    if ($rule && $tokens = _omega_extract_class_tokens($rule)) {
      $output['tokens'] = $tokens;
      // The token helper function alters the rule by adding placeholders to it
      // instead of the full token declarations. Therefore we h
      $output['class'] = $rule;
    }

    return $output;
  }

  // The class rule seems to be broken, return FALSE.
  return FALSE;
}

/**
 * Helper function for extracting the tokens from a class rule string.
 *
 * @param $string
 *   The class rule string. The rule string is altered in the process of
 *   extracting the tokens as we are removing the actual tokens in favor of
 *   simplified placeholders.
 *
 * @return array
 *   An array of extracted tokens.
 */
function _omega_extract_class_tokens(&$string) {
  $tokens = array();
  if (preg_match_all('/\[([a-z0-9#_ ]+(:[a-z0-9#_ ]+)*)\]/', $string, $matches)) {
    // Optimize the token replacement process by removing duplicates.
    foreach (array_unique($matches[0]) as $key => $match) {
      // Remove the wrapping brackets from the token string.
      $substr = substr($match, 1, strlen($match) - 2);
      $tokens[$key] = explode(':', $substr);
      // Replace the actual token with the keyed placeholder. The placeholder is
      // easier to replace when processing the class rule.
      $string = str_replace($match, '[' . $key . ']', $string);
    }
  }
  return $tokens;
}