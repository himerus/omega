<?php

/**
 * @file
 * Main extension file for the 'css' extension.
 */

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

