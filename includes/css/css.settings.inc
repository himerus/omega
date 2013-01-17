<?php

/**
 * @file
 * Contains the theme settings form elements for the css extension.
 */

include_once dirname(__FILE__) . '/css.inc';

/**
 * Implements hook_extension_EXTENSION_theme_settings_form_alter().
 */
function omega_extension_css_settings_form($element, &$form, $form_state) {
  $element['omega_media_queries_inline'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enforce embedded media queries'),
    '#description' => t('Embeds all media queries (and media types) in the CSS files automatically instead of leaving them on the &lt;styles&gt; tags. This potentially reduces the amount of separate CSS aggregates on your website.'),
    '#default_value' => omega_theme_get_setting('omega_media_queries_inline', TRUE),
  );

  $element['omega_class_rules'] = array(
    '#type' => 'textarea',
    '#title' => t('Class rules'),
    '#description' => t("Enter one class rule per line."),
    // The rules are stored in an array.
    '#default_value' => implode("\n", (array) omega_theme_get_setting('omega_class_rules', array())),
    '#element_validate' => array('omega_class_rules_element_validate'),
  );

  $element['omega_css_exclude'] = array(
    '#type' => 'textarea',
    '#title' => t('Exclude CSS files'),
    '#description' => t("Enter one file per line. The '*' character is a wildcard. Each path is relative to a module, theme, profile, theme engine or the 'misc' folder."),
    // The paths are stored in an array.
    '#default_value' => implode("\n", (array) omega_theme_get_setting('omega_css_exclude', array())),
  );

  // We need a custom submit handler to store the CSS and JS paths as arrays.
  $form['#submit'][] = 'omega_extension_css_theme_settings_form_submit';

  return $element;
}

/**
 * Form submit handler for the css theme settings extension.
 */
function omega_extension_css_theme_settings_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  // Explode and trim the values for the CSS exclusions and class rules so we
  // end up with a clean array.
  foreach (array('omega_css_exclude', 'omega_class_rules') as $item) {
    $exclude = array_filter(array_map('trim', explode("\n", $values[$item])));
    form_set_value(array('#parents' => array($item)), $exclude, $form_state);
  }
}

/**
 * Validation callback for the CSS class rules form element.
 */
function omega_class_rules_element_validate($element, &$form_state) {
  $values = array_filter(array_map('trim', explode("\n", $element['#value'])));

  foreach ($values as $rule) {
    if (!preg_match('/^(\+|-)([a-z_]+)(:([a-z_])*)?\[(([a-z\-]+|(\*(?!\*))|\[[a-zA-Z0-9_# ]+(:[a-zA-Z0-9_# ]+)*\])+)\]$/', $rule, $matches)) {
      // The class rule doesn't match the regex pattern.
      form_error($element, t('At least one of the provided class rules is invalid: @rule.', array('@rule' => $rule)), $form_state);
    }
    elseif ($matches[1] == '+' && strpos($rule, '*') !== FALSE) {
      // 'Add' rules don't support wildcards of course.
      form_error($element, t("Wildcards (asterisk characters) are not supported by 'add' (+) rules: @rule.", array('@rule' => $rule)), $form_state);
    }
  }
}
