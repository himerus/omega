<?php

/**
 * @file
 * Contains \Drupal\omega\Theme\OmegaExportInterface
 */

namespace Drupal\omega\Export;

use Drupal\Core\Form\FormStateInterface;
/**
 * Defines an interface for serialization formats.
 */
interface OmegaExportInterface {

  /**
   * Returns the information array on the export
   *
   * @return array
   *   Array of information regarding the new theme to create
   */
  public function buildExport($export);
  
  /**
   * Performs save operations, and creates a new theme
   *
   * @return array
   *   Array of information regarding the newly created theme
   */
  public function saveExport(FormStateInterface $form_state);
  
  /**
   * Returns the information array on the export
   *
   * @return array
   *   Array of information regarding the newly created theme
   */
  public function getBuildPath();  
  
  /**
   * Returns the information array on the export
   *
   * @return array
   *   Array of information regarding the newly created theme
   */
  public function getParentPath();
  
  /**
   * Returns the information array on the export
   *
   * @return array
   *   Array of information regarding the newly created theme
   */
  public function getInfo($key = '');
  
  /**
   * Returns the options array on the export
   *
   * @return array
   *   Array of options for newly created subtheme
   */
  public function getOptions($key = '');
  
  /**
   * Returns the friendly name of the theme to be created
   *
   * @return string
   *   Theme friendly name
   */
  public function getFriendlyName();
  
  /**
   * Returns the machine name of the theme to be created
   *
   * @return string
   *   Theme machine name
   */
  public function getMachineName();
  
  /**
   * Returns the description of the theme to be created
   *
   * @return string
   *   Theme description
   */
  public function getDescription();
  
  /**
   * Returns the version of the theme to be created
   *
   * @return string
   *   Theme version 
   */
  public function getVersion();
  
  /**
   * Returns the type of theme to be created
   *
   * @return string
   *   Theme export type (clone, subtheme)
   */
  public function getExportType();
  
  public function directoryCloneCopy($source, $target, $ignore = '/^(\.(\.)?|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/');
  
  public function updateInfoFile($info);
  
  /**
   * Opens a file path and scans for $find and replaces all instances with $replace and saves the edited file
   */
  public function fileStrReplace($file_path, $find, $replace);
  
  /**
   * Takes a PHP array and returns the YAML version
   */
  public function yamlEncode(array $php);
  
  /**
   * Takes raw YAML and returns a PHP array
   */
  public function yamlDecode($yaml);
  
  /**
   * Function to clear the theme regsitry and data
   * Current just doesn't work no matter what I've done
   * When attempting to use drupal_get_path in the saveExport function to get path of newly copied/created theme.
   * 
   */
  public function refreshThemeData();
}
