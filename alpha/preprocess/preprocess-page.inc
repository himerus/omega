<?php

/*
 * @todo
 */
function alpha_alpha_preprocess_page(&$vars) {
  global $theme_key;
  $sections = alpha_sections();
  $zones = alpha_zones($theme_key);
  
  $settings = $vars['#alpha'] = alpha_settings($theme_key);
  
  alpha_include_grid($vars['#alpha']['grid']);

  $regions = array();
  foreach (alpha_regions($theme_key) as $region => $item) {
    if (isset($zones[$item['zone']]) && $zones[$item['zone']]['enabled']) {
      if (!empty($vars['page'][$region]) || $vars['#alpha']['debug']['block']) {
        $regions[$item['zone']][$region] = $vars['page'][$region];
        $regions[$item['zone']][$region]['#region'] = $region;      
        $regions[$item['zone']][$region]['#data'] = $item;
        $regions[$item['zone']][$region]['#page'] = &$vars;
        $regions[$item['zone']][$region]['#sorted'] = FALSE;
        $regions[$item['zone']][$region]['#weight'] = (int) $item['weight'];
        
        if ($vars['#alpha']['debug']['block']) {
          $regions[$item['zone']][$region]['alpha_debug_'. $region] = alpha_debug_block($region, $item);
          $regions[$item['zone']][$region]['#theme_wrappers'] = array('region');
        }
      }
      // insert the branding "block" if it is enabled for this region
      if($region == $settings['blocks']['branding']['region'] && $settings['blocks']['branding']['enabled']) {
        $branding = $settings['blocks']['branding'];        
        
        $block = new stdClass();
        $block->delta = $region;
        $block->region = $region;
        $block->module = 'alpha_blockify';
        $block->subject = $info['name'];
        $block->weight = $branding['weight'];
        $block->block_type = 'branding';
        $block->vars = array(
          'site_name' => $vars['site_name'],
          'site_slogan' => $vars['site_slogan'],
          'logo' => $vars['logo'],
        );
        $regions[$item['zone']][$region]['alpha_branding_block'] = array(
      	'#printed' => FALSE,
          '#theme_wrappers' => array('block'),
          '#block' => $block,
        );
      }
      unset($vars['page'][$region]);
    }   
  }

  foreach ($zones as $zone => $item) {
    if (isset($item['section']) && isset($sections[$item['section']]) && isset($regions[$zone])) {
      if (!empty($item['primary']) && isset($regions[$zone][$item['primary']])) {
        $primary = &$regions[$zone][$item['primary']];
        
        $primary['#weight'] = -100;
        $primary['#data']['columns'] = $item['columns'] - $primary['#region']['prefix'] - $primary['#data']['suffix'];
        $primary['#data']['width'] = $item['columns'];
      
        $pull = array();
        
        foreach ($regions[$zone] as $region => $info) {
          if ($region != $item['primary']) {
            $primary['#data']['columns'] -= $info['#data']['width'];
            $primary['#data']['width'] -= $info['#data']['width'];

            if ($primary['#data']['weight'] > $info['#data']['weight']) {
              $primary['#data']['push'] += $info['#data']['width'];              
              $pull[$region] = &$primary['#data']['width'];
            }
          }         
        }
        
        foreach ($pull as $region => $value) {
          $regions[$zone][$region]['#data']['pull'] = $value;
        }
      }
      
      $item['type'] = $item['primary'] && isset($regions[$zone][$item['primary']]) ? 'dynamic' : 'static';
      
      $vars['page'][$item['section'] . '_section'][$zone] = $regions[$zone];
      $vars['page'][$item['section'] . '_section'][$zone]['#theme_wrappers'][] = 'zone';
      $vars['page'][$item['section'] . '_section'][$zone]['#zone'] = $item;
      $vars['page'][$item['section'] . '_section'][$zone]['#weight'] = (int) $item['weight'];
      $vars['page'][$item['section'] . '_section'][$zone]['#sorted'] = FALSE;
      $vars['page'][$item['section'] . '_section'][$zone]['#page'] = &$vars;
    }
  }

  foreach ($sections as $section => $item) {
    if (!empty($vars['page'][$section . '_section'])) {   
      $vars['page'][$section . '_section']['#theme_wrappers'][] = 'section';
      $vars['page'][$section . '_section']['#section'] = $section;
      $vars['page'][$section . '_section']['#page'] = &$vars;
    }
  }
}