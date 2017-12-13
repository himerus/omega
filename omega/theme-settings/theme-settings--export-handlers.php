<?php

/**
 * @file
 * Implements custom theme settings for Omega Five related to exports.
 */

use Drupal\omega\Export\OmegaExport;
use Drupal\Core\Form\FormStateInterface;

/**
 * Custom validation function for generating subthemes.
 *
 * @param array $form
 *   Nested array of form elements that comprise the form.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   A keyed array containing the current state of the form.
 */
function omega_theme_generate_validate(array &$form, FormStateInterface &$form_state) {
  // Grab the Drupal theme handler service.
  $themeHandler = \Drupal::service('theme_handler');
  // Grab the Drupal file handler service.
  $fileHandler = \Drupal::service('file_system');
  // Get all the values of the submitted form.
  $values = $form_state->getValues();
  // Create a variable for the export fields.
  $exportValues = $values['export'];
  // Remove some variables we don't need.
  unset($exportValues['export_options_clone']);
  unset($exportValues['export_options_kit']);
  // Setup export.
  $export = new OmegaExport($themeHandler, $fileHandler);
  // Build export.
  $build = $export->buildExport($exportValues);
  $target_path = $build['destination_path'];

  // Ensure that you can't clone Omega.
  if ($build['parent'] == 'omega' && $build['type'] == 'clone') {
    $form_state->setErrorByName('export][export_options][export_type', t('You are not allowed to create a CLONE of Omega. Try instead to create a SUBTHEME of Omega or a CLONE of an Omega subtheme.'));
  }

  // Machine name functionality should handle this on its own,
  // this is just a 100% verification.
  if (file_exists($target_path) && is_dir($target_path)) {
    $form_state->setErrorByName('export][export_details][theme_machine_name', t('The target directory <strong><small>@buildpath</small></strong> already exists, please change machine name and try again, or remove existing directory and try again.', ['@buildpath' => $export->getBuildPath()]));
  }
}

/**
 * Custom validation function for generating subthemes.
 *
 * @param array $form
 *   Nested array of form elements that comprise the form.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   A keyed array containing the current state of the form.
 */
function omega_theme_generate_submit(array &$form, FormStateInterface &$form_state) {
  // Grab the Drupal theme handler service.
  $themeHandler = \Drupal::service('theme_handler');
  // Grab the Drupal file handler service.
  $fileHandler = \Drupal::service('file_system');
  // Get all the values of the submitted form.
  $values = $form_state->getValues();
  // Create a variable for the export fields.
  $exportValues = $values['export'];
  // Remove some variables we don't need.
  unset($exportValues['export_options_clone']);
  unset($exportValues['export_options_kit']);
  // Setup export.
  $export = new OmegaExport($themeHandler, $fileHandler);
  // Build export.
  $export->buildExport($exportValues);
  // Save export.
  $export->saveExport($form_state);
}
