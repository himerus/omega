<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_html(&$vars) {
  $vars['attributes_array']['class'] = $vars['classes_array'];

  $settings = alpha_settings($GLOBALS['theme_key']);
  $css = alpha_css($GLOBALS['theme_key']);
  $grid = alpha_grids($GLOBALS['theme_key'], $settings['grid']);
  $libraries = alpha_libraries($GLOBALS['theme_key']);
  
  if ($settings['responsive']['enabled'] && $settings['debug']['grid'] && $grid['type'] == 'fixed' && $settings['debug']['access']) {
    $block = new stdClass();
    $block->delta = 'grid-indicator';
    $block->region = 'page_bottom';
    $block->module = 'alpha-indicator';
    $block->subject = t('Responsive grid indicator block for @name', array('@name' => $grid['name']));
    $block->weight = -999;
    
    $block = array(
      '#theme_wrappers' => array('block'),
      '#block' => $block,
      '#markup' => t('This is a debugging block.'),
      '#printed' => FALSE,
    );
    
    $vars['page']['page_bottom']['alpha-grid-indicator'] = $block;
  }
  
  foreach (array_keys(array_filter($settings['libraries'])) as $item) {
    if (isset($libraries[$item])) {
      if (!empty($libraries[$item]['js'])) {
        foreach ($libraries[$item]['js'] as $include) {
          drupal_add_js($include['path'], $include['options']);
        }
      }

      if (!empty($libraries[$item]['css'])) {
        foreach ($libraries[$item]['css'] as $include) {
          drupal_add_css($include['path'], $include['options']);
        }
      }
    }
  }
  
  foreach (array_keys(array_filter($settings['css'])) as $item) {
    if (isset($css[$item])) {
      drupal_add_css($css[$item]['path'], $css[$item]['options']);
    }
  }
  
  if(($settings['debug']['grid'] || $settings['debug']['block']) && $settings['debug']['access']) {
    drupal_add_css(drupal_get_path('theme', 'alpha') . '/css/alpha-debug.css', array('weight' => -5));

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