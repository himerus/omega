<?php

namespace Drupal\omega\Settings;

use \Drupal\system\Form\ThemeSettingsForm;
use \Drupal\Core\Form\FormStateInterface;

/**
 * Extension of ThemeSettingsForm
 */
class OmegaThemeSettings extends ThemeSettingsForm {

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param string $theme
   *
   * @return array $form
   */
  public function buildForm(array $form, FormStateInterface $form_state, $theme = '') {
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }
}
