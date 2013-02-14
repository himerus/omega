<?php

/**
 * @file
 * Registers Omega layouts as Panels layouts.
 */

/**
 * Implementation of hook_LAYOUT_panels_layouts()
 */
function omega_omega_panels_layouts() {
  // We cannot use the 'path' property here and anywhere in our layouts declared
  // in omega_panels_get_sublayouts() because panels uses it for both, the icon
  // and the include file in panels_theme() which we don't want. It's something
  // that works for things like 'flexible' layouts but not when the child plugin
  // mechanism is only used for an alternative discovery logic as in this
  // case.
  return array(
    'title' => t('Omega'),
    'category' => t('Omega page layouts'),
    'description' => t('Omega page layouts for use as Panels Everywhere site templates.'),
    'get child' => 'omega_panels_get_sublayout',
    'get children' => 'omega_panels_get_sublayouts',
    'theme' => 'omega_panels',
    'admin theme' => 'omega_panels_admin',
    'regions' => array(),
  );
}

/**
 * Callback to provide a single Omega layout.
 */
function omega_panels_get_sublayout($plugin, $layout, $sublayout) {
  $layouts = omega_panels_get_sublayouts($plugin, $layout);
  if (isset($layouts["$layout:$sublayout"])) {
    return $layouts["$layout:$sublayout"];
  }
}

/**
 * Callback to provide all Omega layouts.
 */
function omega_panels_get_sublayouts($plugin, $layout) {
  require_once drupal_get_path('theme', 'omega') . '/includes/omega.inc';

  $layouts = array();
  if ($items = omega_layouts_info('omega')) {
    foreach ($items as $name => $info) {
      $layouts["$layout:$name"] = array(
        'name' => "$layout:$name",
        'title' => $info['info']['name'],
        'layout' => $info,
      ) + $plugin;

      // Panels calls the preview image 'icon'.
      if (isset($info['info']['preview'])) {
        $layouts["$layout:$name"]['icon'] = $info['path'] . '/' . $info['info']['preview'];
      }

      // Various optional elements from the .info file of the layout.
      foreach (array('regions', 'category', 'description') as $key) {
        if (isset($info['info'][$key])) {
          $layouts["$layout:$name"][$key] = $info['info'][$key];
        }
      }
    }
  }

  return $layouts;
}

/**
 * Theme function for rendering a Omega layout.
 */
function theme_omega_panels($variables) {
  drupal_process_attached(array('#attached' => $variables['layout']['attached']));

  $variables['page'] = &$variables['content'];

  $variables['base_path']         = base_path();
  $variables['front_page']        = url();
  $variables['feed_icons']        = drupal_get_feeds();
  $variables['language']          = $GLOBALS['language'];
  $variables['language']->dir     = $GLOBALS['language']->direction ? 'rtl' : 'ltr';
  $variables['logo']              = theme_get_setting('logo');
  $variables['main_menu']         = theme_get_setting('toggle_main_menu') ? menu_main_menu() : array();
  $variables['secondary_menu']    = theme_get_setting('toggle_secondary_menu') ? menu_secondary_menu() : array();
  $variables['action_links']      = menu_local_actions();
  $variables['site_name']         = (theme_get_setting('toggle_name') ? filter_xss_admin(variable_get('site_name', 'Drupal')) : '');
  $variables['site_slogan']       = (theme_get_setting('toggle_slogan') ? filter_xss_admin(variable_get('site_slogan', '')) : '');
  $variables['tabs']              = menu_local_tabs();

  if ($node = menu_get_object()) {
    $variables['node'] = $node;
  }

  // Build the breadcrumb last, so as to increase the chance of being able to
  // re-use the cache of an already rendered menu containing the active link
  // for the current page.
  // @see menu_tree_page_data()
  $variables['breadcrumb'] = theme('breadcrumb', array('breadcrumb' => drupal_get_breadcrumb()));
  $variables['title'] = drupal_get_title();

  // Generate messages last in order to capture as many as possible for the
  // current page.
  $variables['messages'] = theme('status_messages');

  return theme('omega_layout_renderer', array('layout' => $variables['layout']['layout'], 'type' => 'panels', 'variables' => $variables));
}

/**
 * Theme function for rendering a Omega layout on the Panels admin UI.
 */
function theme_omega_panels_admin($variables) {
  drupal_process_attached(array('#attached' => $variables['layout']['attached']));

  $variables['page'] = &$variables['content'];
  return theme('omega_layout_renderer', array('layout' => $variables['layout']['layout'], 'type' => 'panels', 'variables' => $variables));
}
