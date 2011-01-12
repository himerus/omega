<?php
// $Id: default-theme-settings.inc,v 1.1.2.8 2011/01/11 21:26:32 himerus Exp $
// General Settings
$form['omega_general']['optional_css'] = array(
  '#type' => 'fieldset',
  '#title' => t('Optional CSS'),
  '#description' => t('Here, you may disable default theme CSS provided by the Omega base theme.'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
);
$form['omega_general']['optional_css']['reset_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable reset.css'),
  '#default_value' => omega_theme_get_setting('reset_css'),
  '#description' => t('reset.css is the default CSS reset standard created by <a href="http://meyerweb.com/eric/tools/css/reset/">Eric Meyer</a>.'),
);
$form['omega_general']['optional_css']['text_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable text.css'),
  '#default_value' => omega_theme_get_setting('text_css'),
  '#description' => t('text.css offers some generic typography to give the default text presenation a bit more love.'),
);
$form['omega_general']['optional_css']['table_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable tables.css'),
  '#default_value' => omega_theme_get_setting('table_css'),
  '#description' => t('tables.css provides enhancements to the default table styles.'),
);
$form['omega_general']['optional_css']['menu_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable menus.css'),
  '#default_value' => omega_theme_get_setting('menu_css'),
  '#description' => t('menus.css provides enhancements to the default menu styles.'),
);
$form['omega_general']['optional_css']['link_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable links.css'),
  '#default_value' => omega_theme_get_setting('link_css'),
  '#description' => t('links.css provides enhancements to the default link styles.'),
);
$form['omega_general']['optional_css']['form_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable forms.css'),
  '#default_value' => omega_theme_get_setting('form_css'),
  '#description' => t('forms.css provides enhancements to the default form styles.'),
);
$form['omega_general']['optional_css']['pager_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable pagers.css'),
  '#default_value' => omega_theme_get_setting('pager_css'),
  '#description' => t('tables.css provides enhancements to the default pager styles.'),
);
$form['omega_general']['optional_css']['regions_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable regions.css'),
  '#default_value' => omega_theme_get_setting('regions_css'),
  '#description' => t('regions.css defines all the default regions of the Omega theme and its sub-themes. Currently there are no defining characteristics in this file, and it can be disabled without affecting any region presentation.'),
);
$form['omega_general']['optional_css']['defaults_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable defaults.css'),
  '#default_value' => omega_theme_get_setting('defaults_css'),
  '#description' => t('defaults.css gives the Omega theme the majority of the spacing and alignment CSS for various elements.'),
);
$form['omega_general']['optional_css']['custom_css'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable custom.css'),
  '#default_value' => omega_theme_get_setting('custom_css'),
  '#description' => t('custom.css provides some additional CSS that is module related, and not a part of core drupal. Can be disabled and used as a reference for certain items. Contributed CSS that is not directly related to core markup will be in this file.'),
);

  // Page titles
$form['omega_general']['search_settings'] = array(
  '#type' => 'fieldset',
  '#title' => t('Search Settings'),
  '#description' => t('You may configure search settings here.'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
);
$form['omega_general']['search_settings']['omega_render_search_box'] = array(
  '#type' => 'checkbox',
  '#title' => t('Render tradional search box in theme.'),
  '#description' => t('By default in Drupal 7, the search box is no longer rendered in the theme. Enabling this setting will recreate that Drupal 6 behavior, allowing the search box to be renderd in the theme, AND as a block elsewhere should you choose.'),
  '#default_value' => omega_theme_get_setting('omega_render_search_box'),
);
$form['omega_general']['search_settings']['omega_search_default_text'] = array(
  '#type' => 'textfield',
  '#title' => t('Custom Default Search Text'),
  '#size' => 60,
  '#default_value' => omega_theme_get_setting('omega_search_default_text'),
  '#description' => t('Enter default value to use in search boxes.'),
);
$form['omega_general']['page_format_titles'] = array(
  '#type' => 'fieldset',
  '#title' => t('Page titles'),
  '#description' => t('This is the title that displays in the title bar of your web browser. Your site title, slogan, and mission can all be set on your Site Information page. [NOTE: For more advanced page title functionality, consider using the "Page Title" module.  However, the Page titles theme settings do not work in combination with the "Page Title" module and will be disabled if you have it enabled.]'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
);

if (module_exists('page_title') == FALSE) {
  $form['omega_general']['page_format_titles']['general_page_title_settings'] = array(
    '#type' => 'fieldset',
    '#prefix' => t('<h3 class="omega-accordion"><a href="#">General Page Title Settings</a></h3><div class="omega-accordion-content">'),
    '#suffix' => '</div>',
    '#title' => t('Front page title'),
    '#description' => t('Your front page in particular should have important keywords for your site in the page title'),
  );
    // SEO configurable separator
  $form['omega_general']['page_format_titles']['general_page_title_settings']['configurable_separator'] = array(
    '#type' => 'textfield',
    '#title' => t('Title separator'),
    '#description' => t('Customize the separator character used in the page title'),
    '#size' => 60,
    '#default_value' => omega_theme_get_setting('configurable_separator'),
  );
    // front page title
  $form['omega_general']['page_format_titles']['front_page_format_titles'] = array(
    '#type' => 'fieldset',
    '#prefix' => t('<h3 class="omega-accordion"><a href="#">Front page titles</a></h3><div class="omega-accordion-content">'),
    '#suffix' => '</div>',
    '#title' => t('Front page title'),
    '#description' => t('Your front page in particular should have important keywords for your site in the page title'),
  );
  $form['omega_general']['page_format_titles']['front_page_format_titles']['front_page_title_display'] = array(
    '#type' => 'select',
    '#title' => t('Set text of front page title'),
    '#default_value' => omega_theme_get_setting('front_page_title_display'),
    '#options' => array(
    'title_slogan' => t('Site title | Site slogan'),
    'slogan_title' => t('Site slogan | Site title'),
    'custom' => t('Custom (below)'),
      ),
  );
  $form['omega_general']['page_format_titles']['front_page_format_titles']['page_title_display_custom'] = array(
    '#type' => 'textfield',
    '#title' => t('Custom'),
    '#size' => 60,
    '#default_value' => omega_theme_get_setting('page_title_display_custom'),
    '#description' => t('Enter a custom page title for your front page'),
  );

  // other pages title
  $form['omega_general']['page_format_titles']['other_page_format_titles'] = array(
    '#type' => 'fieldset',
    '#title' => t('Other page titles'),
    '#prefix' => t('<h3 class="omega-accordion"><a href="#">Other page titles</a></h3><div class="omega-accordion-content">'),
    '#suffix' => '</div>',
  );
  $form['omega_general']['page_format_titles']['other_page_format_titles']['other_page_title_display'] = array(
    '#type' => 'select',
    '#title' => t('Set text of other page titles'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#default_value' => omega_theme_get_setting('other_page_title_display'),
    '#options' => array(
    'ptitle_slogan' => t('Page title | Site slogan'),
    'ptitle_stitle' => t('Page title | Site title'),
    'ptitle_custom' => t('Page title | Custom (below)'),
    'custom' => t('Custom (below)'),
      ),
  );
  $form['omega_general']['page_format_titles']['other_page_format_titles']['other_page_title_display_custom'] = array(
    '#type' => 'textfield',
    '#title' => t('Custom'),
    '#size' => 60,
    '#default_value' => omega_theme_get_setting('other_page_title_display_custom'),
    '#description' => t('Enter a custom page title for all other pages'),
  );
}
else {
  $form['omega_general']['page_format_titles']['#description'] = 'NOTICE: You currently have the "Page Title" module installed and enabled, so the Page titles theme settings have been disabled to prevent conflicts.  If you wish to re-enable the Page titles theme settings, you must first disable the "Page Title" module.';
  $form['omega_general']['page_format_titles']['configurable_separator']['#disabled'] = 'disabled';
}

// Breadcrumb
$form['omega_general']['breadcrumb'] = array(
  '#type' => 'fieldset',
  '#title' => t('Breadcrumb settings'),
  '#attributes' => array('id' => 'omega-breadcrumb'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
);
$form['omega_general']['breadcrumb']['omega_breadcrumb'] = array(
  '#type' => 'select',
  '#title' => t('Display breadcrumb'),
  '#default_value' => omega_theme_get_setting('omega_breadcrumb'),
  '#options' => array(
    'yes' => t('Yes'),
    'admin' => t('Only in admin section'),
    'no' => t('No'),
  ),
);
$form['omega_general']['breadcrumb']['omega_breadcrumb_separator'] = array(
  '#type' => 'textfield',
  '#title' => t('Breadcrumb separator'),
  '#description' => t('Text only. Don’t forget to include spaces.'),
  '#default_value' => omega_theme_get_setting('omega_breadcrumb_separator'),
  '#size' => 5,
  '#maxlength' => 10,
);
$form['omega_general']['breadcrumb']['omega_breadcrumb_home'] = array(
  '#type' => 'checkbox',
  '#title' => t('Show home page link in breadcrumb'),
  '#default_value' => omega_theme_get_setting('omega_breadcrumb_home'),
);
$form['omega_general']['breadcrumb']['omega_breadcrumb_trailing'] = array(
  '#type' => 'checkbox',
  '#title' => t('Append a separator to the end of the breadcrumb'),
  '#default_value' => omega_theme_get_setting('omega_breadcrumb_trailing'),
  '#description' => t('Useful when the breadcrumb is placed just before the title.'),
);
$form['omega_general']['breadcrumb']['omega_breadcrumb_title'] = array(
  '#type' => 'checkbox',
  '#title' => t('Append the content title to the end of the breadcrumb'),
  '#default_value' => omega_theme_get_setting('omega_breadcrumb_title'),
  '#description' => t('Useful when the breadcrumb is not placed just before the title.'),
);
// menu settings
// don't be silly, you should never display more than 10 levels of a menu.
// 5 levels is max recommended.
for ($i = 1; $i <= 10; $i++) {
  $menu_depth[$i] = $i;
}
$form['omega_general']['menu'] = array(
  '#type' => 'fieldset',
  '#title' => t('Menu settings'),
  '#attributes' => array('id' => 'omega-menu'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
);
$form['omega_general']['menu']['omega_menu_depth'] = array(
  '#type' => 'select',
  '#title' => t('Depth of menu to display'),
  '#description' => t('<p>If in your menu settings, you have selected to use the same menu for primary & secondary navigations, Omega will render the menu as dropdown menus assuming you have menu.css enabled in the Optional CSS section of your theme settings.</p><p>This option will determine how deep your menus will be. 1 level only shows the top level, 2 shows the top level plus the next level down, etc.</p>'),
  '#default_value' => omega_theme_get_setting('omega_menu_depth'),
  '#options' => $menu_depth,
);