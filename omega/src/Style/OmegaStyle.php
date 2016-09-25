<?php

namespace Drupal\omega\Style;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;

class OmegaStyle implements OmegaStyleInterface {

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The file system handler service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileHandler;

  /**
   * Constructs a style object.
   *
   * @param ThemeHandlerInterface $theme_handler
   * @param FileSystemInterface $file_handler
   */
  public function __construct(ThemeHandlerInterface $theme_handler, FileSystemInterface $file_handler) {
    $this->themeHandler = $theme_handler;
    $this->fileHandler = $file_handler;
  }

  /**
   * @inheritdoc
   */
  public static function themeStylesUpdate() {
    // TODO: Implement themeStylesUpdate() method.
  }

  /**
   * @inheritdoc
   */
  public static function scssVariablesUpdate() {
    // TODO: Implement scssVariablesUpdate() method.
  }

  /**
   * @inheritdoc
   */
  public static function compileScss() {
    // TODO: Implement compileScss() method.
  }

  /**
   * @inheritdoc
   */
  public static function compileCss() {
    // TODO: Implement compileCss() method.
  }

  /**
   * @inheritdoc
   */
  public static function getImportPaths() {
    // TODO: Implement getImportPaths() method.
  }

  /**
   * @inheritdoc
   */
  public static function setImportPaths() {
    // TODO: Implement setImportPaths() method.
  }

  /**
   * @inheritdoc
   */
  public static function getScssOptions() {
    // TODO: Implement getScssOptions() method.
  }

  /**
   * @inheritdoc
   */
  public static function getOptionalLibraries($theme) {
    $status = theme_get_setting('styles', $theme);
    $themeHandler = \Drupal::service('theme_handler');
    $library_discovery = \Drupal::service('library.discovery');
    $themes = $themeHandler->rebuildThemeData();
    $themeObject = $themes[$theme];
    $baseThemes = $themeObject->base_themes;

    $ignore_libraries = array(
      'omega/omega_admin',
      // removed as it is only used for theme admin page(s) and is required
    );

    // create a variable to hold the full library data
    $allLibraries = array();
    // create a variable to combine all the libraries we can select/desect in our form
    $returnLibraries = array();
    // the libraries for the primary theme
    $themeLibraries = $library_discovery->getLibrariesByExtension($theme);
    foreach ($themeLibraries as $libraryKey => $themeLibrary) {
      if (!in_array($theme . '/' . $libraryKey, $ignore_libraries)) {
        $allLibraries[$libraryKey] = $themeLibrary;
        $returnLibraries[$theme . '/' . $libraryKey] = array(
          'title' => isset($themeLibrary['omega']['title']) ? $themeLibrary['omega']['title'] : $theme . '/' . $libraryKey,
          'description' => isset($themeLibrary['omega']['description']) ? $themeLibrary['omega']['description'] : 'No Description Available. :(',
          'library' => $theme . '/' . $libraryKey,
          'status' => isset($status[$theme . '/' . $libraryKey]) ? $status[$theme . '/' . $libraryKey] : TRUE,
          'allow_disable' => isset($themeLibrary['omega']['allow_enable_disable']) ? $themeLibrary['omega']['allow_enable_disable'] : TRUE,
          'allow_clone' => isset($themeLibrary['omega']['allow_clone_for_subtheme']) ? $themeLibrary['omega']['allow_clone_for_subtheme'] : TRUE,
        );
      }
    }

    // setup some themes to skip.
    // Essentially trimming this down to only Omega and any Omega subthemes.
    $ignore_base_themes = array(
      'stable',
      'classy'
    );

    // the libraries for any parent theme
    foreach ($baseThemes as $baseKey => $baseTheme) {
      if (!in_array($baseKey, $ignore_base_themes)) {
        foreach ($library_discovery->getLibrariesByExtension($baseKey) as $libraryKey => $themeLibrary) {
          if (!in_array($baseKey . '/' . $libraryKey, $ignore_libraries)) {
            $allLibraries[$libraryKey] = $themeLibrary;
            $returnLibraries[$baseKey . '/' . $libraryKey] = array(
              'title' => isset($themeLibrary['omega']['title']) ? $themeLibrary['omega']['title'] : $baseKey . '/' . $libraryKey,
              'description' => isset($themeLibrary['omega']['description']) ? $themeLibrary['omega']['description'] : 'No Description Available. :(',
              'library' => $baseKey . '/' . $libraryKey,
              'status' => isset($status[$baseKey . '/' . $libraryKey]) ? $status[$baseKey . '/' . $libraryKey] : TRUE,
              'allow_disable' => isset($themeLibrary['omega']['allow_enable_disable']) ? $themeLibrary['omega']['allow_enable_disable'] : TRUE,
              'allow_clone' => isset($themeLibrary['omega']['allow_clone_for_subtheme']) ? $themeLibrary['omega']['allow_clone_for_subtheme'] : TRUE,
            );
          }
        }
      }
    }
    return $returnLibraries;
  }
}
