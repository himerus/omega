<?php

/**
 * Returns HTML for a field.
 *
 * @see theme_field().
 */
function omega_field($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<div' . $variables['title_attributes'] . '>' . $variables['label'] . ':&nbsp;</div>';
  }

  // Render the items.
  $output .= '<div' . $variables['content_attributes'] . '>';
  foreach ($variables['items'] as $delta => $item) {
    $variables['item_attributes'][$delta]['class'][] = 'field-item ' . ($delta % 2 ? 'odd' : 'even');
    $output .= '<div' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</div>';
  }
  $output .= '</div>';

  // Render the top-level DIV.
  $output = '<div' . $variables['attributes'] . '>' . $output . '</div>';

  return $output;
}
