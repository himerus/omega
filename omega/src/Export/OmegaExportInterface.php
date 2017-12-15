<?php

namespace Drupal\omega\Export;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for creating an Omega subtheme.
 */
interface OmegaExportInterface {

  /**
   * Returns the information array on the export.
   *
   * @return array
   *   Array of information regarding the new theme to create.
   */
  public function buildExport($export);

  /**
   * Performs save operations, and creates a new theme.
   *
   * @return array
   *   Array of information regarding the newly created theme.
   */
  public function saveExport(FormStateInterface $form_state);

  /**
   * Returns the path of a .kit directory to use when generating subtheme.
   *
   * @return string
   *   Path to directory.
   */
  public function getKitPath();

  /**
   * Returns the information array on the export.
   *
   * @return array
   *   Array of information regarding the newly created theme.
   */
  public function getBuildPath();

  /**
   * Returns the information array on the export.
   *
   * @return array
   *   Array of information regarding the newly created theme.
   */
  public function getParentPath();

  /**
   * Returns the information array on the export.
   *
   * @return array
   *   Array of information regarding the newly created theme.
   */
  public function getInfo($key = '');

  /**
   * Returns the options array on the export.
   *
   * @return array
   *   Array of options for newly created subtheme.
   */
  public function getOptions($key = '');

  /**
   * Returns the friendly name of the theme to be created.
   *
   * @return string
   *   Theme friendly name.
   */
  public function getFriendlyName();

  /**
   * Returns the machine name of the theme to be created.
   *
   * @return string
   *   Theme machine name.
   */
  public function getMachineName();

  /**
   * Returns the description of the theme to be created.
   *
   * @return string
   *   Theme description.
   */
  public function getDescription();

  /**
   * Returns the version of the theme to be created.
   *
   * @return string
   *   Theme version.
   */
  public function getVersion();

  /**
   * Returns the type of theme to be created.
   *
   * @return string
   *   Theme export type. (clone, subtheme)
   */
  public function getExportType();

  /**
   * Create a clone of an Omega theme.
   *
   * @param string $source
   *   Directory of theme we are cloning.
   * @param string $target
   *   Directory we are saving cloned theme to.
   * @param string $ignore
   *   Regular expression of files to ignore found in $source.
   */
  public function omegaThemeClone($source, $target, $ignore = '/^(\.(\.)?|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/');

  /**
   * Save new data to a theme's .info file.
   *
   * @param array $info
   *   Array of data to be converted to YAML format and saved in the .info file.
   */
  public function updateInfoFile(array $info);

  /**
   * Replaces content in a file.
   *
   * Opens a file path and scans for $find and replaces all instances with
   * $replace and saves the edited file.
   *
   * @param string $file_path
   *   Directory path to operate on.
   * @param string $find
   *   String to search for.
   * @param string $replace
   *   String to replace $find with.
   */
  public function fileStrReplace($file_path, $find, $replace);

  /**
   * Takes a PHP array and returns the YAML version.
   *
   * @param array $php
   *   PHP array of the YAML contents.
   *
   * @return string
   *   Contents of a YAML formatted file to be written.
   */
  public function yamlEncode(array $php);

  /**
   * Takes raw YAML and returns a PHP array.
   *
   * @param string $yaml
   *   Contents of a YAML formatted file.
   *
   * @return array
   *   PHP array of the YAML contents.
   */
  public function yamlDecode($yaml);

  /**
   * Function to clear the theme regsitry and data.
   *
   * Current just doesn't work no matter what I've done.
   * When attempting to use drupal_get_path in the saveExport function to
   * get path of newly copied/created theme.
   *
   * @todo: Make this work somehow.
   */
  public function refreshThemeData();

}
