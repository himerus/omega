<?php

/**
 * @file
 * Main extension file for the 'scripts' extension.
 */

/**
 * Extension callback for attaching enabled libraries.
 */
function omega_extension_scripts_attach_libraries(&$variables) {
  $registry = theme_get_registry();

  // Check if there are any enabled libraries.
  foreach ($registry['html']['libraries'] as $library => $info) {
    drupal_process_attached(array('#attached' => $info['attached']));

    if ($info['callback']) {
      $info['callback']($library, $info['settings']);
    }
  }
}

function _omega_render_conditional_scripts($items) {
  $output = '';
  // The index counter is used to keep aggregated and non-aggregated files in
  // order by weight.
  $index = 1;
  $processed = array();
  $files = array();
  $preprocess_js = (variable_get('preprocess_js', FALSE) && (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update'));

  // A dummy query-string is added to filenames, to gain control over
  // browser-caching. The string changes on every update or full cache
  // flush, forcing browsers to load a new copy of the files, as the
  // URL changed. Files that should not be cached (see drupal_add_js())
  // get REQUEST_TIME as query-string instead, to enforce reload on every
  // page request.
  $default_query_string = variable_get('css_js_query_string', '0');

  // For inline JavaScript to validate as XHTML, all JavaScript containing
  // XHTML needs to be wrapped in CDATA. To make that backwards compatible
  // with HTML 4, we need to comment out the CDATA-tag.
  $embed_prefix = "\n<!--//--><![CDATA[//><!--\n";
  $embed_suffix = "\n//--><!]]>\n";

  // Since JavaScript may look for arguments in the URL and act on them, some
  // third-party code might require the use of a different query string.
  $js_version_string = variable_get('drupal_js_version_query_string', 'v=');

  // Sort the JavaScript so that it appears in the correct order.
  uasort($items, 'drupal_sort_css_js');

  // Provide the page with information about the individual JavaScript files
  // used, information not otherwise available when aggregation is enabled.
  $setting['ajaxPageState']['js'] = array_fill_keys(array_keys($items), 1);
  unset($setting['ajaxPageState']['js']['settings']);
  drupal_add_js($setting, 'setting');

  // Loop through the JavaScript to construct the rendered output.
  $element = array(
    '#tag' => 'script',
    '#value' => '',
    '#attributes' => array(
      'type' => 'text/javascript',
    ),
  );

  foreach ($items as $item) {
    $query_string =  empty($item['version']) ? $default_query_string : $js_version_string . $item['version'];

    switch ($item['type']) {
      case 'setting':
        $js_element = $element;
        $js_element['#value_prefix'] = $embed_prefix;
        $js_element['#value'] = 'jQuery.extend(Drupal.settings, ' . drupal_json_encode(drupal_array_merge_deep_array($item['data'])) . ");";
        $js_element['#value_suffix'] = $embed_suffix;
        $output .= theme('html_tag', array('element' => $js_element));
        break;

      case 'inline':
        $js_element = $element;
        if ($item['defer']) {
          $js_element['#attributes']['defer'] = 'defer';
        }
        $js_element['#value_prefix'] = $embed_prefix;
        $js_element['#value'] = $item['data'];
        $js_element['#value_suffix'] = $embed_suffix;
        $processed[$index++] = theme('html_tag', array('element' => $js_element));
        break;

      case 'file':
        $js_element = $element;
        if (!$item['preprocess'] || !$preprocess_js) {
          if ($item['defer']) {
            $js_element['#attributes']['defer'] = 'defer';
          }
          $query_string_separator = (strpos($item['data'], '?') !== FALSE) ? '&' : '?';
          $js_element['#attributes']['src'] = file_create_url($item['data']) . $query_string_separator . ($item['cache'] ? $query_string : REQUEST_TIME);
          $processed[$index++] = theme('html_tag', array('element' => $js_element));
        }
        else {
          // By increasing the index for each aggregated file, we maintain
          // the relative ordering of JS by weight. We also set the key such
          // that groups are split by items sharing the same 'group' value and
          // 'every_page' flag. While this potentially results in more aggregate
          // files, it helps make each one more reusable across a site visit,
          // leading to better front-end performance of a website as a whole.
          // See drupal_add_js() for details.
          $key = 'aggregate_' . $item['group'] . '_' . $item['every_page'] . '_' . $index;
          $processed[$key] = '';
          $files[$key][$item['data']] = $item;
        }
        break;

      case 'external':
        $js_element = $element;
        // Preprocessing for external JavaScript files is ignored.
        if ($item['defer']) {
          $js_element['#attributes']['defer'] = 'defer';
        }
        $js_element['#attributes']['src'] = $item['data'];
        $processed[$index++] = theme('html_tag', array('element' => $js_element));
        break;
    }
  }

  // Aggregate any remaining JS files that haven't already been output.
  if ($preprocess_js && count($files) > 0) {
    foreach ($files as $key => $file_set) {
      $uri = drupal_build_js_cache($file_set);
      // Only include the file if was written successfully. Errors are logged
      // using watchdog.
      if ($uri) {
        $preprocess_file = file_create_url($uri);
        $js_element = $element;
        $js_element['#attributes']['src'] = $preprocess_file;
        $processed[$key] = theme('html_tag', array('element' => $js_element));
      }
    }
  }

  // Keep the order of JS files consistent as some are preprocessed and others are not.
  // Make sure any inline or JS setting variables appear last after libraries have loaded.
  return implode('', $processed) . $output;
}