<?php

namespace Drupal\omega\Layout;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;

class OmegaLayout implements OmegaLayoutInterface {

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
   * An array of Drupal themes, each an array of information about that theme.
   *
   * @var array
   */
  public $themes;

  /**
   * Constructs a layout object.
   *
   * @param ThemeHandlerInterface $theme_handler
   * @param FileSystemInterface $file_handler
   */
  public function __construct(ThemeHandlerInterface $theme_handler, FileSystemInterface $file_handler) {
    $this->themeHandler = $theme_handler;
    $this->fileHandler = $file_handler;
    $this->themes = $this->themeHandler->rebuildThemeData();
  }
}
