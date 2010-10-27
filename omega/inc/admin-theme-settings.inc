<?php
// $Id$
// Administrative Settings
$form['omega_admin'] = array(
  '#type' => 'vertical_tabs',
  '#prefix' => t('<h2 class="omega-config-title">Administrative Omega 960 Settings</h2>'),
  '#description' => t('Configure options for administration and development.'),
  '#weight' => 11,
);
// overlay toggle options
$form['omega_admin']['grid_overlay'] = array(
  '#type' => 'fieldset',
  '#title' => t('Grid Overlay / Debugging'),
  '#collapsible' => TRUE,
  '#collapsed' => TRUE,
);
$form['omega_admin']['grid_overlay']['debug_grid_toggle'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable grid overlay/toggle for selected roles.'),
  '#default_value' => theme_get_setting('debug_grid_toggle'),
);
$form['omega_admin']['grid_overlay']['debug_grid_toggle_state'] = array(
  '#type' => 'checkbox',
  '#title' => t('Turn on grid overlay on page load. (otherwise requires clicking to enable)'),
  '#default_value' => theme_get_setting('debug_grid_toggle_state'),
);
$role_options = array_map('check_plain', user_roles());
unset($role_options[1]);
$form['omega_admin']['grid_overlay']['debug_grid_roles'] = array(
  '#type' => 'checkboxes',
  '#title' => t('Roles that may use the grid overlay/debugging tool. (User 1 Automatically has access.)'),
  '#default_value' => theme_get_setting('debug_grid_roles'),
  '#options' => $role_options,
);