<?php

namespace Drupal\omega\Builder;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * ThemeBuilder declares methods used to build a new subtheme.
 */
class ThemeBuilder {
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
   * Constructs an export object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Drupal Theme Handler interface.
   * @param \Drupal\Core\File\FileSystemInterface $file_handler
   *   Drupal File interface.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, FileSystemInterface $file_handler) {
    $this->themeHandler = $theme_handler;
    $this->fileHandler = $file_handler;
    $this->themes = $this->themeHandler->rebuildThemeData();
    $this->themeDirectories = [
      'themes',
      'themes/custom',
      'themes/contrib',
      'core/themes',
    ];
    $this->omegaExcludedThemeFiles = [
      // Administrative templates to exclude.
      'export-exclude',
      // Theme-settings directory in omega should be excluded.
      'theme-settings',
      // Any top level images directory.
      'images',
      // Primary classes from Omega. (or any source)
      'src',
    ];
  }

}
