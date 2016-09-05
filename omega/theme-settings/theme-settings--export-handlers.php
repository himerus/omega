<?php

use Drupal\omega\Export\OmegaExport;

function omega_theme_generate_validate(&$form, &$form_state) {
  $themeHandler = \Drupal::service('theme_handler');
  $fileHandler = \Drupal::service('file_system');
  // get all the values of the submitted form
  $values = $form_state->getValues();
  // create a variable for the export fields
  $exportValues = $values['export'];
  // remove some variables we don't need
  unset($exportValues['export_options_clone']);
  unset($exportValues['export_options_kit']);
  $export = new OmegaExport($themeHandler, $fileHandler);
  $build = $export->buildExport($exportValues);
  $target_path = $build['destination_path'];
  // machine name functionality should handle this on its own, this is just a 100% verification
  if (file_exists($target_path) && is_dir($target_path)) {
    $form_state->setErrorByName('export][export_details][theme_machine_name', t('The target directory <strong><small>' . $export->getBuildPath() . '</small></strong> already exists, please change machine name and try again, or remove existing directory and try again.'));
  }
}

function omega_theme_generate_submit(&$form, &$form_state) {
  $themeHandler = \Drupal::service('theme_handler');
  $fileHandler = \Drupal::service('file_system');
  // get all the values of the submitted form
  $values = $form_state->getValues();
  // create a variable for the export fields
  $exportValues = $values['export'];
  // remove some variables we don't need
  unset($exportValues['export_options_clone']);
  unset($exportValues['export_options_kit']);
  $export = new OmegaExport($themeHandler, $fileHandler);
  $export->buildExport($exportValues);
  $export->saveExport($form_state);
}
