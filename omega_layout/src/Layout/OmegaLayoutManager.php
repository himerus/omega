<?php

namespace Drupal\omega_layout\Layout;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;

class OmegaLayoutManager implements OmegaLayoutManagerInterface {

  /**
   * Holds arrays of layouts, keyed by id.
   *
   * @var array
   */
  protected $layouts = [];

  /**
   * Contains the features enabled for layouts by default.
   **/
  protected $layoutStructure = [
    'label' => '',
    'category' => '',
    'type' => '',
    'template' => '',
    'css' => '',
    'scss' => '',
    'icon' => '',
    'regions' => [],
    'groups' => [],
  ];

  /**
   * The config factory to get the installed themes.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The theme handler to fire themes_installed/themes_uninstalled hooks.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Construct function.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ThemeHandlerInterface $theme_handler) {
    $this->configFactory = $config_factory;
    $this->themeHandler = $theme_handler;
  }

  /**
   * Return layout object.
   */
  public function getLayout() {

  }

  /**
   * Return array of layouts.
   */
  public function getLayouts() {

  }

  /**
   * Add a new layout.
   */
  public function addLayout() {

  }

  /**
   * Save an existing layout.
   */
  public function saveLayout() {

  }

  /**
   * Remove an existing layout.
   */
  public function removeLayout() {

  }
}
