<?php

/**b
 * Returns HTML for a field.
 */
function omega_field($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<div' . $variables['title_attributes'] . '>' . $variables['label'] . ':</div>';
  }

  // Render the items.
  $output .= '<div' . $variables['content_attributes'] . '>';
  foreach ($variables['items'] as $delta => $item) {
    $classes = 'field-item ' . ($delta % 2 ? 'odd' : 'even');
    $output .= '<div class="' . $classes . '"' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</div>';
  }
  $output .= '</div>';

  // Render the top-level DIV.
  $output = '<div' . $variables['attributes'] . '>' . $output . '</div>';

  return $output;
}