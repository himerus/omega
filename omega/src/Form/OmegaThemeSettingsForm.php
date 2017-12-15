<?php

namespace Drupal\omega\Settings;

use Drupal\system\Form\ThemeSettingsForm;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * Extension of ThemeSettingsForm.
 */
class OmegaThemeSettingsForm extends ThemeSettingsForm {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The MIME type guesser.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * Constructs a ThemeSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler instance to use.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface $mime_type_guesser
   *   The MIME type guesser instance to use.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, MimeTypeGuesserInterface $mime_type_guesser) {
    parent::__construct($config_factory, $module_handler, $theme_handler, $mime_type_guesser);

    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->mimeTypeGuesser = $mime_type_guesser;
  }

  /**
   * Form buildForm method.
   *
   * @param array $form
   *   Array containing form definition.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Array containing active state of form.
   * @param string $theme
   *   Theme name.
   *
   * @return array
   *   Return array of form values after any changes in build.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $theme = '') {
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * Form validateForm method.
   *
   * @param array $form
   *   Array containing form definition.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Array containing active state of form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * Form submitForm method.
   *
   * @param array $form
   *   Array containing form definition.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Array containing active state of form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

}
