<?php
// $Id: admin-theme-settings.inc,v 1.1.2.4 2011/01/11 21:26:32 himerus Exp $
// Administrative Settings
// overlay toggle options
$form['omega_general']['grid_overlay'] = array(
  '#type' => 'fieldset',
  '#title' => t('Grid Overlay / Debugging'),
  '#collapsible' => TRUE,
  '#collapsed' => FALSE,
);
$form['omega_general']['grid_overlay']['debug_grid_toggle'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable grid overlay/toggle for selected roles.'),
  '#default_value' => omega_theme_get_setting('debug_grid_toggle'),
);
$form['omega_general']['grid_overlay']['debug_grid_toggle_state'] = array(
  '#type' => 'checkbox',
  '#title' => t('Turn on grid overlay on page load. (otherwise requires clicking to enable)'),
  '#default_value' => omega_theme_get_setting('debug_grid_toggle_state'),
);
$role_options = array_map('check_plain', user_roles());
unset($role_options[1]);
$form['omega_general']['grid_overlay']['debug_grid_roles'] = array(
  '#type' => 'checkboxes',
  '#title' => t('Roles that may use the grid overlay/debugging tool. (User 1 Automatically has access.)'),
  '#default_value' => omega_theme_get_setting('debug_grid_roles'),
  '#options' => $role_options,
);