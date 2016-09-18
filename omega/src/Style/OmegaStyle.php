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
  public function themeStylesUpdate() {
    // TODO: Implement themeStylesUpdate() method.
  }

  /**
   * @inheritdoc
   */
  public function scssVariablesUpdate() {
    // TODO: Implement scssVariablesUpdate() method.
  }

  /**
   * @inheritdoc
   */
  public function compileScss() {
    // TODO: Implement compileScss() method.
  }

  /**
   * @inheritdoc
   */
  public function compileCss() {
    // TODO: Implement compileCss() method.
  }

  /**
   * @inheritdoc
   */
  public function getImportPaths() {
    // TODO: Implement getImportPaths() method.
  }

  /**
   * @inheritdoc
   */
  public function setImportPaths() {
    // TODO: Implement setImportPaths() method.
  }

  /**
   * @inheritdoc
   */
  public function getScssOptions() {
    // TODO: Implement getScssOptions() method.
  }

}
