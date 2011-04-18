<?php 

function alpha_theme_settings_block(&$form, &$form_state) {
  $theme = $form_state['build_info']['args'][0];
  
  $notice = module_exists('blockify') ?
    t("<p class=\"alpha-notice\">Alpha has detected that you are running the !blockify module. The settings on this page won't have any effect while the !blockify module is activated. However, we strongly recommend using the !blockify module instead of the <em>special blocks</em> provided by Omega.</p>", array('!blockify' => '<a href="http://drupal.org/project/blockify">Blockify</a>')) :
    t('<p class="alpha-notice">This feature tries to fix some of the rather inflexible parts of a standard Drupal page by wrapping them in <em>fake</em> blocks. For a real solution, please use the !blockify module instead (recommended).</p>', array('!blockify' => '<a href="http://drupal.org/project/blockify">Blockify</a>'));
  
  $form['alpha_settings']['special_blocks'] = array(
    '#weight' => -7,
    '#type' => 'fieldset',
    '#title' => t('Special blocks'),
  );
  
  $form['alpha_settings']['special_blocks']['notice'] = array(
    '#type' => 'markup',
    '#markup' => $notice,
  );
   
  foreach (alpha_special_blocks() as $item => $name) {
    $form['alpha_settings']['special_blocks']['alpha_blocks_' . $item] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable the @name block', array('@name' => $name)),
      '#default_value' => alpha_theme_get_setting('alpha_blocks_' . $item, $theme, TRUE),
    );
        
    $form['alpha_settings']['special_blocks'][$item . '_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('@name block settings', array('@name' => $name)),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#states' => array(
        'visible' => array(
          ':input[name="alpha_blocks_' . $item . '"]' => array('checked' => TRUE),
        ),
      ),
      '#attributes' => array(
        'class' => array('alpha-block-settings'),
      ),
    );
    
    $form['alpha_settings']['special_blocks'][$item . '_settings']['alpha_blocks_' . $item . '_region'] = array(
      '#type' => 'select',
      '#title' => t('Region'),
      '#options' => $form_state['#regions'],
      '#default_value' => alpha_theme_get_setting('alpha_blocks_' . $item . '_region', $theme),
      '#description' => t('The region in which this block should be rendered in.'),
    );   
    
    $form['alpha_settings']['special_blocks'][$item . '_settings']['alpha_blocks_' . $item . '_weight'] = array(
      '#type' => 'weight',
      '#delta' => 50,
      '#title' => t('Weight'),
      '#default_value' => alpha_theme_get_setting('alpha_blocks_' . $item . '_weight', $theme),
      '#description' => t('The weight for this block (relative to its region).'),
    );
  }
  
  $form['alpha_settings']['special_blocks']['branding_settings']['alpha_blocks_branding_name_hidden'] = array(
    '#type' => 'checkbox',
    '#weight' => -10,
    '#title' => t('Hide the site name'),
    '#default_value' => alpha_theme_get_setting('alpha_blocks_branding_name_hidden', $theme),
    '#description' => t('Activating this will make the site name invisible.'),
  );
  
  $form['alpha_settings']['special_blocks']['branding_settings']['alpha_blocks_branding_slogan_hidden'] = array(
    '#type' => 'checkbox',
    '#weight' => -9,
    '#title' => t('Hide the site slogan'),
    '#default_value' => alpha_theme_get_setting('alpha_blocks_branding_slogan_hidden', $theme, TRUE),
    '#description' => t('Activating this will make the site slogan invisible.'),
  );    
}