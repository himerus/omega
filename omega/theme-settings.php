<?php 

function omega_form_system_theme_settings_alter(&$form, &$form_state) {
  $settings = $form_state['alpha_settings'];
  $form['alpha_settings']['styles']['omega_formalize'] = array(
    '#type' => 'checkbox',
    '#default_value' => isset($settings['omega']['formalize']) ? $settings['omega']['formalize'] : '',
    '#title' => t('Enable Formalize by <a href="http://formalize.me/" title="Formalize">Nathan Smith</a>'),
    '#description' => t('Formalize is a framework by Nathan Smith for neat looking cross-browser forms with extended functionality.'),
    '#weight' => -10,
  );
}