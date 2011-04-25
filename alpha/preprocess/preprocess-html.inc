<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_html(&$vars) {
  $vars['attributes_array']['class'] = $vars['classes_array'];
  
  $settings = alpha_settings($GLOBALS['theme_key']);
  $css = alpha_css($GLOBALS['theme_key']);

  foreach (array_keys(array_filter($settings['css'])) as $item) {
    if (isset($css[$item])) {
      $options['media'] = $css[$item]['media'];
      $options['weight'] = $css[$item]['weight'];
      
      $options['browsers'] = empty($css[$item]['condition']) ? array() : array(
        'IE' => $css[$item]['condition'],
        '!IE' => FALSE,
      );
      
      drupal_add_css($css[$item]['path'], $options);
    }
  }
  
  if(($settings['debug']['grid'] || $settings['debug']['block']) && $settings['debug']['access']) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/debug.css', array('weight' => -5));

    if ($settings['debug']['grid']) {
      $vars['attributes_array']['class'][] = 'alpha-grid-debug';
    }
    
    if ($settings['debug']['block']) {
      $vars['attributes_array']['class'][] = 'alpha-region-debug';
    }
  }

  if($settings['responsive']['enabled'] && $settings['viewport']['enabled']) {
    $meta = array(
  	  '#tag' => 'meta',
      '#attributes' => array(
        'name' => 'viewport',
        'content' => 'width=device-width; initial-scale=' . $settings['viewport']['initial'] . '; maximum-scale=' . $settings['viewport']['max'] . '; minimum-scale=' . $settings['viewport']['min'] . '; user-scalable=' . $settings['viewport']['user'] . ';',
      ),
    );

    drupal_add_html_head($meta, 'alpha-viewport');
  } 
}