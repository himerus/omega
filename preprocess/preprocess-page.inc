<?php
// $Id$
global $theme_key;

/**
 * Adding in the ability to combine the sidebars on certain paths in order to adjust 
 * layouts based on context of the page. My main purpose for this is in the admin 
 * section to help widen up the layout if two sidebars are being used.
 * 
 * All it does is combine the sidebar_first and sidebar_last into sidebar_first and 
 * unset the sidebar_last variable. This resets the layout to act like only one 
 * sidebar is present in certain paths
 */
if (theme_get_setting('sidebar_contain_pages')) {
  $path = drupal_get_path_alias($_GET['q']);
  // Compare with the internal and path alias (if any).
  $page_match = drupal_match_path($path, theme_get_setting('sidebar_contain_pages'));
  if ($path != $_GET['q']) {
    $page_match = $page_match || drupal_match_path($_GET['q'], theme_get_setting('sidebar_contain_pages'));
  }
  $page_match = !(theme_get_setting('sidebar_combine') xor $page_match);
  if ($page_match && substr($_GET['q'], 0, 17) != 'admin/build/block') {
    $vars['page']['sidebar_first'] = $vars['page']['sidebar_first']. $vars['page']['sidebar_last'];
    unset($vars['page']['sidebar_last']); 
  }
}

/**
 * Header Region
 * Depends on the width of the logo and title region, which is 4 grids by default.
 * This leaves 12 (Grid-12 by default) for the maximum width of any one of the elements in this zone
 * If only one zone is included, it fills the maximum width, and if both zones are present, 
 * they will use the provided settings from the theme configuration interface.
 */
 
// the width of the wrapper item around the navigation menus & header block regions
// This width plus the logo width should total the width of your grid.
$vars['header_wrapper_width'] = theme_get_setting('omega_header_wrapper_width');

// The width of the logo element.
// This width plus the logo width should total the width of your grid.

$vars['default_container_width'] = theme_get_setting('omega_default_container_width');
$vars['branding_wrapper_width'] = theme_get_setting('omega_branding_wrapper_width');
$vars['header_logo_width'] = theme_get_setting('omega_header_logo_width');
$vars['header_menu_width'] = theme_get_setting('omega_header_menu_width');
$vars['internal_nav_wrapper_width'] = theme_get_setting('omega_internal_nav_wrapper_width');
$vars['breadcrumb_slogan_width'] = theme_get_setting('omega_breadcrumb_slogan_width');
$vars['search_width'] = theme_get_setting('omega_search_width');

$header_regions = array(
  'header_first' => array(
    'data' => $vars['page']['header_first'],
    'width' => theme_get_setting('omega_header_first_width'),
  ),
  'header_last' => array(
    'data' => $vars['page']['header_last'],
    'width' => theme_get_setting('omega_header_last_width'),
  ),
);
$vars = static_region_builder($header_regions, theme_get_setting('omega_header_wrapper_width'), $vars);


/**
 * Preface Region
 * Same as above, preparing the preface regions to accept settings configurations
 */
$vars['preface_wrapper_grids'] = theme_get_setting('omega_preface_wrapper_grids');
$preface_regions = array(
  'preface_first' => array(
    'data' => $vars['page']['preface_first'],
    'width' => theme_get_setting('omega_preface_first_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_preface_first_prefix'),
      'suffix' => theme_get_setting('omega_preface_first_suffix')),
    ),
  'preface_middle' => array(
    'data' => $vars['page']['preface_middle'],
    'width' => theme_get_setting('omega_preface_middle_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_preface_middle_prefix'),
      'suffix' => theme_get_setting('omega_preface_middle_suffix')),
    ),
  'preface_last' => array(
    'data' => $vars['page']['preface_last'],
    'width' => theme_get_setting('omega_preface_last_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_preface_last_prefix'),
      'suffix' => theme_get_setting('omega_preface_last_suffix'))
  ),
);
$vars = static_region_builder($preface_regions, theme_get_setting('preface_wrapper_grids'), $vars);

/**
 * Body Region
 * Configuration of $sidebar_first, $sidebar_last, and the main content zone
 * 
 * This section needs MUCH cleanup as I know I can condense the switch
 * statement to be much shorter than completely redeclaring each zone
 * for each case that can be present.
 * 
 * @todo cleanup switch statement
 */
$vars['content_container_width'] = theme_get_setting('omega_content_container_width');

$content_regions = array(
  'main_content' => array(
    'data' => $vars['page']['content'],
    'width' => theme_get_setting('omega_content_main_width'),
    // primary defines which region will get all the extra space
    // should other regions be left out.
    'primary' => TRUE,
    // we need to tell the "related" zones so that we can determine widths for this primary zone
    'related' => array(
      'sidebar_first' => array('width' => theme_get_setting('omega_sidebar_first_width')), 
      'sidebar_last' => array('width' => theme_get_setting('omega_sidebar_last_width')),
    ),
    'push' => array(
      'width' => '',
      'conditions' => array(
        // 'sidebar_last',
      ),
    ),
    'pull' => array(
      'width' => '',
      'conditions' => array(
        // 'main_content',
      ),
    ),
  ),
  'sidebar_first' => array(
    'data' => $vars['page']['sidebar_first'],
    'width' => theme_get_setting('omega_sidebar_first_width'),
    'push' => array(
      'width' => '',
      'conditions' => array(
        // this just needs the region name it depends on in this primary array
        // must be a region in this group though, not a region outside the 
        // $content_regions area.
        // 'sidebar_last',
      ),
    ),
    'pull' => array(
      'width' => '',
      'conditions' => array(
        // same as above
        // 'main_content',
      ),
    ),
  ),
  'sidebar_last' => array(
    'data' => $vars['page']['sidebar_last'],
    'width' => theme_get_setting('omega_sidebar_last_width'),
    'push' => array(
      'width' => '',
      'conditions' => array(
        // 'sidebar_last',
      ),
    ),
    'pull' => array(
      'width' => '',
      'conditions' => array(
        // 'main_content',
      ),
    ),
  ),
);

/**
 * For panels and other modules that may set $show_blocks to false, we will need to 
 * unset the sidebars in this case
 */
/*
if (!$vars['page']['show_blocks']) {
	unset($vars['page']['sidebar_first']);
	unset($vars['page']['sidebar_last']);
}
*/

// now we will be switching between our primary content layout types.
switch(theme_get_setting('omega_content_layout')){
  default:
  case 'first_content_last':
  // FIRST - CONTENT - LAST
  
  // the distance to pull the "first" / left sidebar to place it on the left of the content
  // this does always include the right sidebar width, and does not need to account for if 
  // there is content available in that region or not. If there wasn't content in it, the 
  // primary content region would swallow up that extra grid space anyway.
  
  // if sidebar_last isn't present, we will pull the first sidebar the combined width of the content & sidebar last spacing
  // otherwise,
  $sl_pull = $vars['page']['sidebar_last'] ? theme_get_setting('omega_content_main_width') : theme_get_setting('omega_content_main_width') + theme_get_setting('omega_sidebar_last_width');
  
  // let's assign some new data to our $content_regions array to assing pull classes
  
  // sidebar_first will be pulled an exact amount assuming it exists in this layout
  $content_regions['sidebar_first']['pull']['width'] = $sl_pull;
  $content_regions['sidebar_first']['pull']['conditions'] = array();
  $content_regions['sidebar_first']['push'] = FALSE;
  // sidebar_last doesn't move in this layout
  $content_regions['sidebar_last']['pull'] = FALSE;
  $content_regions['sidebar_last']['push'] = FALSE;
  // main_content will only be pushed in this layout
  $content_regions['main_content']['pull'] = FALSE;
  $content_regions['main_content']['push'] = array(
    'width' => theme_get_setting('omega_sidebar_first_width'),
    'conditions' => array(
      'sidebar_first' => array(
        // type should either be TRUE or FALSE
        // false value here will use a ! operator in front of the variable in the ns() function
        'type' => FALSE,
        'value' => theme_get_setting('omega_sidebar_first_width'),
      ),
    ),
  );
  // calling the dynamic region builder function to position these elements
  $vars = dynamic_region_builder($content_regions, theme_get_setting('omega_content_container_width'), $vars);
  
  
  break;

  // CONTENT  - FIRST - LAST
  case 'content_first_last':
  // This is seriously the easiest implementation because source order is used
  // making push/pull irrelevant in this case.
  
  // sidebar_first doesn't move in this layout
  $content_regions['sidebar_first']['pull'] = FALSE;
  $content_regions['sidebar_first']['push'] = FALSE;
  // sidebar_last doesn't move in this layout
  $content_regions['sidebar_last']['pull'] = FALSE;
  $content_regions['sidebar_last']['push'] = FALSE;
  // main_content doesn't move in this layout
  $content_regions['main_content']['pull'] = FALSE;
  $content_regions['main_content']['push'] = FALSE;
  // calling the dynamic region builder function to position these elements
  $vars = dynamic_region_builder($content_regions, theme_get_setting('omega_content_container_width'), $vars);
  
  break;

  // FIRST - LAST - CONTENT
  case 'first_last_content':
  // if sidebar_last isn't present, we will pull the first sidebar the combined width of the content & sidebar last spacing
  // otherwise,
  $sl_pull = $vars['page']['sidebar_last'] ? theme_get_setting('omega_content_main_width') : theme_get_setting('omega_content_main_width') + theme_get_setting('omega_sidebar_last_width');
  $sr_pull = $vars['page']['sidebar_first'] ? theme_get_setting('omega_content_main_width') : theme_get_setting('omega_content_main_width') + theme_get_setting('omega_sidebar_first_width');
  $sc_max_push = theme_get_setting('omega_sidebar_first_width') + theme_get_setting('omega_sidebar_last_width');
  // let's assign some new data to our $content_regions array to assing pull classes
  
  // sidebar_first will be pulled an exact amount assuming it exists in this layout
  $content_regions['sidebar_first']['pull']['width'] = $sl_pull;
  $content_regions['sidebar_first']['pull']['conditions'] = array();
  $content_regions['sidebar_first']['push'] = FALSE;
  // sidebar_last doesn't move in this layout
  $content_regions['sidebar_last']['pull']['width'] = $sr_pull;
  $content_regions['sidebar_last']['pull']['conditions'] = array();
  $content_regions['sidebar_last']['push'] = FALSE;
  // main_content will only be pushed in this layout
  $content_regions['main_content']['pull'] = FALSE;
  $content_regions['main_content']['push'] = array(
    'width' => $sc_max_push,
    'conditions' => array(
      'sidebar_first' => array(
        // type should either be TRUE or FALSE
        // false value here will use a ! operator in front of the variable in the ns() function
        'type' => FALSE,
        'value' => theme_get_setting('omega_sidebar_first_width'),
      ),
      'sidebar_last' => array(
        // type should either be TRUE or FALSE
        // false value here will use a ! operator in front of the variable in the ns() function
        'type' => FALSE,
        'value' => theme_get_setting('omega_sidebar_last_width'),
      ),
    ),
  );
  // calling the dynamic region builder function to position these elements
  $vars = dynamic_region_builder($content_regions, theme_get_setting('omega_content_container_width'), $vars);

  break;
}

/**
 * Postscript Region
 * Same as above, preparing the postscript regions to accept settings configurations
 */
$vars['postscript_container_width'] = theme_get_setting('omega_postscript_container_width');
$vars['preface_wrapper_grids'] = theme_get_setting('omega_preface_wrapper_grids');
$postscript_regions = array(
  'postscript_one' => array(
    'data' => $vars['page']['postscript_one'],
    'width' => theme_get_setting('omega_postscript_one_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_one_prefix'),
      'suffix' => theme_get_setting('omega_postscript_one_suffix')),
    ),
  'postscript_two' => array(
    'data' => $vars['page']['postscript_two'],
    'width' => theme_get_setting('omega_postscript_two_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_two_prefix'),
      'suffix' => theme_get_setting('omega_postscript_two_suffix')),
    ),
  'postscript_three' => array(
    'data' => $vars['page']['postscript_three'],
    'width' => theme_get_setting('omega_postscript_three_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_three_prefix'),
      'suffix' => theme_get_setting('omega_postscript_three_suffix'))
  ),
  'postscript_four' => array(
    'data' => $vars['page']['postscript_four'],
    'width' => theme_get_setting('omega_postscript_four_width'),
    'spacing' => array(
      'prefix' => theme_get_setting('omega_postscript_four_prefix'),
      'suffix' => theme_get_setting('omega_postscript_four_suffix'))
  ),
);
$vars = static_region_builder($postscript_regions, theme_get_setting('postscript_container_width'), $vars);

/**
 * Footer Region
 */
$vars['footer_container_width'] = theme_get_setting('omega_footer_container_width');

$footer_regions = array(
  'footer_first' => array(
    'data' => $vars['page']['footer_first'],
    'width' => theme_get_setting('omega_footer_first_width'),
  ),
  'footer_last' => array(
    'data' => $vars['page']['footer_last'],
    'width' => theme_get_setting('omega_footer_last_width'),
  ),
);
$vars = static_region_builder($footer_regions, theme_get_setting('omega_footer_container_width'), $vars);

// Set a variable for the site name title and logo alt attributes text.
$slogan_text = $vars['site_slogan'];
$site_name_text = $vars['site_name'];
$vars['site_name_and_slogan'] = $site_name_text . ' ' . $slogan_text;
if(isset($vars['logo'])) {
	//$vars['logo_img'] = theme('image', substr($vars['logo'], strlen(base_path())), t($vars['site_name']), t($vars['site_name']));
  $vars['logo_img'] = '<img src="'. $vars['logo'] .'" alt="'. $vars['site_name_and_slogan'] .'" id="logo" />';
}

// NINESIXTY - For easy printing of variables.
if(isset($vars['logo_img'])) {
  $vars['linked_logo_img'] = l($vars['logo_img'], '<front>', array('rel' => 'home', 'title' => t($vars['site_name']), 'html' => TRUE));
}
if(isset($vars['site_name'])) {
  $vars['linked_site_name'] = l($vars['site_name'], '<front>', array('rel' => 'home', 'title' => t('Home')));
}
//$vars['main_menu_links']      = theme('links', $vars['primary_links'], array('class' => 'links main-menu'));
//$vars['secondary_menu_links'] = theme('links', $vars['secondary_links'], array('class' => 'links secondary-menu'));

  




// ACQUIA-MARINA
// Set site title, slogan, mission, page title & separator (unless using Page Title module)
if (!module_exists('page_title')) {
  $title = t(variable_get('site_name', ''));
  $slogan = t(variable_get('site_slogan', ''));
  $mission = t(variable_get('site_mission', ''));
  $page_title = t(drupal_get_title());
  $title_separator = theme_get_setting('configurable_separator');
  if (drupal_is_front_page()) { // Front page title settings
    switch (theme_get_setting('front_page_title_display')) {
      case 'title_slogan':
        $vars['head_title'] = drupal_set_title($title . $title_separator . $slogan);
        break;
      case 'slogan_title':
        $vars['head_title'] = drupal_set_title($slogan . $title_separator . $title);
        break;
      case 'title_mission':
        $vars['head_title'] = drupal_set_title($title . $title_separator . $mission);
        break;
      case 'custom':
        if (theme_get_setting('page_title_display_custom') !== '') {
          $vars['head_title'] = drupal_set_title(t(theme_get_setting('page_title_display_custom')));
        }
    }
  }
  else { // Non-front page title settings
    switch (theme_get_setting('other_page_title_display')) {
      case 'ptitle_slogan':
        $vars['head_title'] = drupal_set_title($page_title . $title_separator . $slogan);
        break;
      case 'ptitle_stitle':
        $vars['head_title'] = drupal_set_title($page_title . $title_separator . $title);
        break;
      case 'ptitle_smission':
        $vars['head_title'] = drupal_set_title($page_title . $title_separator . $mission);
        break;
      case 'ptitle_custom':
        if (theme_get_setting('other_page_title_display_custom') !== '') {
          $vars['head_title'] = drupal_set_title($page_title . $title_separator . t(theme_get_setting('other_page_title_display_custom')));
        }
        break;
      case 'custom':
        if (theme_get_setting('other_page_title_display_custom') !== '') {
        	// make sure to wrap this in a t() or you might be sacrificing a kitten
          $vars['head_title'] = drupal_set_title(t(theme_get_setting('other_page_title_display_custom')));
        }
    }
  }
  $vars['head_title'] = strip_tags($vars['head_title']); // Remove any potential html tags
}