<?php

namespace Drupal\omega\Builder;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Asset\Exception\InvalidLibraryFileException;

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
   * @param ThemeHandlerInterface $theme_handler
   * @param FileSystemInterface $file_handler
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
      'export-exclude', // administrative templates
      'theme-settings', // theme-settings directory in omega
      //'', // custom template files for theme generation
      'images', // any top level images directory
      'src', // primary classes from Omega (or any source)
    ];
  }

}
