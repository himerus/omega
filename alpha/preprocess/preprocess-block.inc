<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_block(&$vars) {
  // adding a class to the title attributes
  $vars['title_attributes_array']['class'][] = 'block-title';
  
  $vars['classes_array'][] = drupal_html_class('block-' . $vars['block']->delta);
  
  if(isset($vars['block']->subject) && strlen($vars['block']->subject) == 0) {
    // add a class to provide CSS for blocks without titles
    // in those cases, we want to chop out that lower border.
    $vars['classes_array'][] = 'block-without-title';
  }
  
  // add odd/even zebra classes into the array of classes
  $vars['classes_array'][] = $vars['block_zebra'];
  
  if($vars['block_id'] == 1) {
    $vars['classes_array'][] = 'first';
  }

  if (isset($vars['block']->last)) {
    $vars['classes_array'][] = 'last';
  }
  
  if ($vars['block']->module == 'alpha') {
    if ($vars['block']->delta == 'debug') {
      $vars['block_html_id'] = drupal_html_id('block-' . $vars['block']->module . '-' . $vars['block']->delta . '-' . $vars['block']->region);
    }
    else if ($vars['block']->delta == 'branding') {
      $vars['site_name'] = $vars['block']->vars['site_name'];
      $vars['site_name_linked'] = l($vars['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home')));
      $vars['site_name_hidden'] = $vars['block']->settings['name_hidden'];
      $vars['site_name_attributes_array']['class'][] = 'site-title';
      
      if ($vars['block']->settings['name_hidden']) {
        $vars['site_name_attributes_array']['class'][] = 'element-invisible';
      }
      
      $vars['site_slogan'] = $vars['block']->vars['site_slogan'];        
      $vars['site_slogan_hidden'] = $vars['block']->settings['slogan_hidden'];        
      $vars['site_slogan_attributes_array']['class'][] = 'site-slogan';
          
      if ($vars['block']->settings['slogan_hidden']) {
        $vars['site_slogan_attributes_array']['class'][] = 'element-invisible';
      }
      
      if(isset($vars['block']->vars['logo'])) {
        $vars['logo'] = $vars['block']->vars['logo'];
        $vars['logo_img'] = '<img src="' . $vars['logo'] . '" alt="' . $vars['site_name'] . '" id="logo" />';
        $vars['logo_img_linked'] = l($vars['logo_img'], '<front>', array('rel' => 'home', 'title' => t($vars['site_name']), 'html' => TRUE)); 
      }        
    }
    else {
      foreach ($vars['block']->vars as $key => $variable) {
        $vars[$key] = $variable;        
      }
    }
  }
  
  $vars['attributes_array']['class'] = $vars['classes_array'];
  $vars['attributes_array']['id'] = $vars['block_html_id'];
}