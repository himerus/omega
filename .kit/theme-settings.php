<?php

/**
 * @file
 * Contains functions to alter the theme settings form for omega_subtheme.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Function implements HOOK_form_system_theme_settings_alter().
 */
function omega_subtheme_form_system_theme_settings_alter(&$form, FormStateInterface &$form_state) {
  // Add custom validation handler. Uncomment following line to activate.
  /* $form['#validate'][] = 'omega_subtheme_theme_settings_validate'; */
  // Add custom submit handler. Uncomment following line to activate.
  /* $form['#submit'][] = 'omega_subtheme_theme_settings_submit'; */
}

/**
 * Custom validation for omega_subtheme_form_system_theme_settings_alter().
 */
function omega_subtheme_theme_settings_validate(&$form, FormStateInterface &$form_state) {

}

/**
 * Custom submit handler for omega_subtheme_form_system_theme_settings_alter().
 */
function omega_subtheme_theme_settings_submit(&$form, FormStateInterface &$form_state) {

}
