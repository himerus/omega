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
  if ($rules = omega_theme_get_setting('omega_class_rules', array())) {
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

/**
 * Preprocess function for applying all class rules that are registered for a
 * theme hook.
 *
 * Implements hook_preprocess_HOOK().
 */
function omega_apply_class_rules(&$variables, $hook) {
  $registry = theme_get_registry();

  // This hook should only get invoked if the 'class rules' property has at
  // least one rule in it. This check is just for good measure.
  if (!empty($registry[$hook]['omega class rules'])) {
    foreach ($registry[$hook]['omega class rules'] as $rule) {
      $property = ($rule['subtype'] ? $rule['subtype'] . '_' : '') . 'attributes_array';

      if (!isset($variables[$property]) || ($rule['action'] == 'remove' && empty($variables[$property]['class']))) {
        // Jump to the next rule if the property doesn't exist or we are trying
        // to remove a class from an already empty class array.
        continue;
      }

      if (!empty($rule['tokens'])) {
        foreach ($rule['tokens'] as $key => $token) {
          $exists = TRUE;
          // Retrieve the token value from the variables array.
          $value = omega_get_nested_value($variables, $token, $exists);

          if (!$exists) {
            // Proceed with the next class rule if the token does not exist in
            // the variables array.
            continue 2;
          }

          // Replace the token placeholder with the actual token value.
          $rule['class'] = str_replace('[' . $key . ']', (string) $value, $rule['class']);
        }
      }

      switch ($rule['action']) {
        case 'add':
          // Append the provided class to the classes array.
          $variables[$property]['class'][] = $rule['class'];
          break;

        case 'remove':
        default:
          if (!$rule['regex']) {
            // If this rule doesn't use regex we can simply use the search
            // provided by the array_keys() function. The array_search()
            // function is not good enough here because it only returns the
            // key of the first matching element and we might have multiple
            // occurrences of the same class string in this array.
            foreach (array_keys($variables[$property]['class'], $rule['class']) as $key) {
              unset($variables[$property]['class'][$key]);
            }
          }
          else {
            // Otherwise we have to loop over all the existing classes and
            // remove all those that match our string.
            foreach ($variables[$property]['class'] as $key => $value) {
              if (preg_match('/^' . $rule['class'] . '$/', $value)) {
                unset($variables[$property]['class'][$key]);
              }
            }
          }

          if (empty($variables[$property]['class'])) {
            // Remove the entire 'class' array if it is empty so we don't end
            // up with an empty 'class' attribute in the output.
            unset($variables[$property]['class']);
          }
          break;
      }
    }
  }
}
