<?php
/**
 * @file
 * Contents of omega_preprocess_block().
 */
function alpha_alpha_preprocess_block(&$vars) {
  // adding a class to the title attributes
  $vars['title_attributes_array']['class'] = 'block-title';
  // give blocks placed through the menu_tree theme wrapper a specific class for proper theming
  if (isset($vars['elements']['#theme_wrappers'][0]) && substr($vars['elements']['#theme_wrappers'][0], 0, 9) == 'menu_tree') {
    $vars['classes_array'][] = 'menu-block';
  }
  // add the menu-block class to some default blocks in Drupal for proper styling.
  switch ($vars['block_html_id']) {
    case 'block-comment-recent':
      $vars['classes_array'][] = 'menu-block';
      break;
  }
  $block_data = $vars['elements']['#block'];
  if(isset($block_data->subject) && strlen($block_data->subject) == 0) {
    // add a class to provide CSS for blocks without titles
    // in those cases, we want to chop out that lower border.
    $vars['classes_array'][] = 'block-without-title';
  }
  
  // add odd/even zebra classes into the array of classes
  $vars['classes_array'][] = $vars['block_zebra'];
  if($vars['block_id'] == 1) {
    $vars['classes_array'][] = 'first';
  }
  // The last_in_region property is set in omega_page_alter().
  if (isset($vars['block']->last_in_region)) {
    $vars['classes_array'][] = 'last';
  }
  
  if(isset($vars['block']->block_type) && $vars['block']->module == 'alpha_blockify') {
    $block = $vars['block'];
    $vars['classes_array'][] = $vars['block']->block_type;
    switch($block->block_type) {
      case 'branding':
        $vars['site_name'] = $block->vars['site_name'];
        $vars['linked_site_name'] = $linked_site_name = l($vars['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home')));
        $vars['site_slogan'] = $block->vars['site_slogan'];
        $vars ['site_name_visibility'] = !$block->name_hidden ? '' : ' element-invisible';
        $vars ['site_slogan_visibility'] = !$block->slogan_hidden ? '' : ' element-invisible';
        if(isset($block->vars['logo'])) {
          $vars['logo'] = $block->vars['logo'];
          $vars['logo_img'] = '<img src="' . $vars['logo'] . '" alt="' . $vars['site_name'] . '" id="logo" />';
          $vars['linked_logo_img'] = l($vars['logo_img'], '<front>', array('rel' => 'home', 'title' => t($vars['site_name']), 'html' => TRUE)); 
        }
        break;
      default:
        break;
    }
  }
}