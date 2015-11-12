<?php

/**
 * @file
 * Contains \Drupal\views\Form\ViewsForm.
 */

namespace Drupal\omega\savelayout;

/**
 * 
 */
class SaveLayout extends ThemeSettingsForm {







  public function submitForm(array &$form, array &$form_state) {
    //$form_object = $this->getFormObject($form_state);
    //$form_object->submitForm($form, $form_state);
    dsm('Running my own custom ->submitForm');
    
  }
  
  
}