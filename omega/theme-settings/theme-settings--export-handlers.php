<?php

use Drupal\omega\Theme\OmegaSettingsInfo;
use Drupal\omega\Export\OmegaExport;

function omega_theme_generate_validate(&$form, &$form_state) {
  //drupal_set_message(t('Running <strong>omega_theme_generate_validate()</strong>'));
  $build_info = $form_state->getBuildInfo();
  $themeHandler = \Drupal::service('theme_handler');
  // Get the theme name.
  $theme = $build_info['args'][0];
  // get all the values of the submitted form
  $values = $form_state->getValues();
  //dsm($form_state);
  // create a variable for the export fields
  $exportValues = $values['export'];
  // remove some variables we don't need
  unset($exportValues['export_options_clone']);
  unset($exportValues['export_options_kit']);
  
  $export = new OmegaExport($themeHandler);
  //dpm($export);
  $build = $export->buildExport($exportValues);
  //dpm($exportValues);
  //dpm($build);
  $target_path = $build['destination_path'];
  
  //$save = $export->saveExport($form_state);
  
  //$form_state->setErrorByName('export][export_details][theme_machine_name', t('Development Pause'));
  
  // machine name functionality should handle this on its own, this is just a 100% verification
  if (file_exists($target_path) && is_dir($target_path)) {
    $form_state->setErrorByName('export][export_details][theme_machine_name', t('The target directory <strong><small>' . $export->getBuildPath() . '</small></strong> already exists, please change machine name and try again, or remove existing directory and try again.'));
  }
  
  if (!is_writable(dirname($target_path))) {
    $form_state->setErrorByName('', t('The target directory (' . $target_path . ') is not writable, please check directory permissions.'));
  }
}

function omega_theme_generate_submit(&$form, &$form_state) {
  //drupal_set_message(t('Running <strong>omega_theme_generate_submit()</strong>'));
  $build_info = $form_state->getBuildInfo();
  $themeHandler = \Drupal::service('theme_handler');
  // Get the theme name.
  $theme = $build_info['args'][0];
  // get all the values of the submitted form
  $values = $form_state->getValues();
  // create a variable for the export fields
  $exportValues = $values['export'];
  // remove some variables we don't need
  unset($exportValues['export_options_clone']);
  unset($exportValues['export_options_kit']);
  $export = new OmegaExport($themeHandler);
  $build = $export->buildExport($exportValues);
  $save = $export->saveExport($form_state);
  //dsm($export);
  //dsm($build);
  //dsm($save);
}
