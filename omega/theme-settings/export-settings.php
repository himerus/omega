<?php
$form['export'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('debug')),
  '#title' => t('Export & Subtheme Generator'),
  '#weight' => 999,
  '#group' => 'omega',
  //'#open' => TRUE,
);
$form['export']['export_info'] = array(
  '#prefix' => '<div class="messages messages--error omega-export-info">',
  '#markup' => '',
  '#suffix' => '</div>',
  '#weight' => -9999,
);

$form['export']['export_info']['#markup'] .= '<p><strong>WARNING:</strong> The export settings for this form are only currently placeholder fields. This functionality will be completed soon.</p>';

$form['export']['export_new_subtheme'] = array(
  '#type' => 'checkbox',
  '#title' => t('Export settings changes as a new subtheme'),
  '#description' => t('This will not save changes to this current theme, rather export the updated settings as a new subtheme to be used and customized.'),
  '#default_value' => 0,
);


/*
$js_settings = array(
  'type' => 'setting',
  'data' => array(
    'machineName' => array(
      '#' . $source['#id'] => $element['#machine_name'],
    ),
    'langcode' => $language->id,
  ),
);
$form['#attached']['library'][] = 'core/drupal.machine-name';
$form['#attached']['js'][] = $js_settings;
*/



$form['export']['export_name'] = array(
  '#type' => 'machine_name',
  '#maxlength' => 55,
  '#title' => t('Theme Name'),
  '#description' => t(''),
  '#default_value' => '',
  '#required' => false,
  
  '#machine_name' => array(

    'exists' => 'omega_theme_exists',

    'source' => array('title'),

    'label' => t('Theme Machine Name'),

    'replace_pattern' => '[^a-z0-9-]+',

    'replace' => '-',

  ),
  
  '#states' => array(
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => FALSE),
    ),
  ),
);

$form['export']['export_description'] = array(
  '#type' => 'textfield',
  '#title' => t('Description'),
  '#description' => t('Enter a short description to describe the newly exported subtheme. This appears only in the administrative interface.'),
  '#default_value' => '',
  '#states' => array(
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => FALSE),
    ),
  ),
);

$form['export']['export_version'] = array(
  '#type' => 'textfield',
  '#title' => t('Version'),
  '#description' => t(''),
  '#default_value' => '8.x-5.x',
  '#states' => array(
    'invisible' => array(
     ':input[name="export_new_subtheme"]' => array('checked' => FALSE),
    ),
  ),
);