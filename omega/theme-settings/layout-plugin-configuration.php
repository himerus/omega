<?php

use \Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface;
// \Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface::getLayoutOptions().

$form['layout_plugin'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('styles')),
  '#title' => t('Layout Plugin Configuration'),
  '#weight' => -599,
  '#group' => 'omega',
  '#open' => FALSE,
  '#tree' => TRUE,
);

$form['layout_plugin']['styles_info'] = array(
  '#markup' => '<div class="messages messages--status omega-styles-info">The following section allows some optional configuration for omega themes using the layout_plugin module.</div>',
  '#weight' => -9999,
);

// $options = $layout_manager->getLayoutOptions(array('group_by_category' => TRUE));
$layout_manager = \Drupal::service('plugin.manager.layout_plugin');
$layout_plugin_layout_options = $layout_manager->getLayoutOptions(array('group_by_category' => TRUE));
$layout_plugin_layout_definitions = $layout_manager->getDefinitions();
/*
 *
foreach ($plugins as $id => $pluginInfo) {
  $plugin = $layoutsManager->createInstance($id, array());
  // @var $plugin \Drupal\layout_plugin\Plugin\Layout\LayoutInterface
  drush_print(dt('Layout !id: !regions', array('!id' => $id, '!regions' => print_r($plugin->getRegionNames(), TRUE))));
}
 * */
foreach ($layout_plugin_layout_options AS $category => $layout) {
  $form['layout_plugin']['default layouts'][$category] = array(
    '#type' => 'checkboxes',
    '#options' => $layout_plugin_layout_options[$category],
    '#title' => t($category . ' Layouts'),
    '#description' => t('<p class="description">The layouts defined in this section are provided in the <em>' . $layout_plugin_layout_definitions[$layout]['provider'] . '.layout.yml</em> in the ' . $layout_plugin_layout_definitions[$layout]['provider'] . ' . '. $layout_plugin_layout_definitions[$layout]['provider_type'].'</p>'),
    '#tree' => FALSE,
  );
}
