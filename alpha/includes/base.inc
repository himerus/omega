<?php

class alpha_theme_container {
  var $theme;
  var $settings;
  var $css;
  var $grid;
  var $libraries;
  var $sections;
  var $zones;
  var $regions;
  
  /**
   * @todo
   */
  function __construct($theme, $delta = NULL) {
    $this->theme = $theme;
    $this->delta = $delta;
    
    if ($cache = alpha_cache_get($theme, $delta)) {
      foreach ($cache->data as $key => $item) {
        $this->$key = $item;
      }
    }
    else {
      $this->init();
      
      alpha_alter('alpha', $this, $theme, $delta);
      alpha_cache_set($this);
    }
  }
  
  /**
   * @todo
   */
  function init() {
    $this->settings();
    $this->sections();
    $this->zones();    
    $this->regions();
    $this->grid();
    $this->css();
    $this->libraries();
  }
  
  /**
   * @todo
   */
  function settings() {
    if (!isset($this->settings)) {
      $roles = array_keys(array_filter(alpha_theme_get_setting('alpha_debug_grid_roles', array(), $this->theme)));

      $this->settings = array(
        'grid' => alpha_theme_get_setting('alpha_grid', 'default', $this->theme),
        'primary' => alpha_theme_get_setting('alpha_primary', NULL, $this->theme),
        'css' => alpha_theme_get_setting('alpha_css', array(), $this->theme),
        'libraries' => alpha_theme_get_setting('alpha_libraries', array(), $this->theme),
        'exclude' => alpha_theme_get_setting('alpha_exclude', array(), $this->theme),      
        'responsive' => alpha_theme_get_setting('alpha_responsive', FALSE, $this->theme),
        'toggle' => array(),
        'hidden' => array(),
        'viewport' => array(
          'enabled' => alpha_theme_get_setting('alpha_viewport', FALSE, $this->theme),
          'initial' => alpha_theme_get_setting('alpha_viewport_initial_scale', 1, $this->theme),
          'min' => alpha_theme_get_setting('alpha_viewport_min_scale', 1, $this->theme),
          'max' => alpha_theme_get_setting('alpha_viewport_max_scale', 1, $this->theme),
          'user' => alpha_theme_get_setting('alpha_viewport_user_scaleable', TRUE, $this->theme),
        ),
        'debug' => array(
          'block' => alpha_theme_get_setting('alpha_debug_block_toggle', FALSE, $this->theme),
          'block_active' => alpha_theme_get_setting('alpha_debug_block_active', FALSE, $this->theme),
          'grid' => alpha_theme_get_setting('alpha_debug_grid_toggle', FALSE, $this->theme),
          'grid_active' => alpha_theme_get_setting('alpha_debug_grid_active', FALSE, $this->theme),
          'roles' => $roles,
          'access' => alpha_debug_access($GLOBALS['user'], $roles),
        ),
      );

      foreach (alpha_toggle() as $item => $title) {
        $this->settings['toggle'][$item] = alpha_theme_get_setting('alpha_toggle_' . $item, TRUE, $this->theme);
      }

      foreach (alpha_visibility() as $item => $title) {
        $this->settings['hidden'][$item] = alpha_theme_get_setting('alpha_hidden_' . $item, FALSE, $this->theme);
      }

      alpha_alter('alpha_settings', $this->settings, $this->theme);
    }
    
    return $this->settings;
  }
  
  /**
   * @todo
   */
  function grid() {
    if (!isset($this->grid)) {      
      $this->settings();
      $this->grid = array();     
      $grids = alpha_retrieve_grids($this->theme);
      
      if (isset($grids[$this->settings['grid']])) {
        $grid = $grids[$this->settings['grid']];
        $trail = array_keys(alpha_theme_trail($this->theme));
        $nonie = $this->settings['responsive'] ? array('IE' => 'gte IE 9', '!IE' => TRUE) : array('IE' => TRUE, '!IE' => TRUE);
        $ie = array('IE' => '(lt IE 9)&(!IEMobile)', '!IE' => FALSE);
        
        $this->grid = array(
          'grid' => $this->settings['grid'],
          'sanitized' => str_replace('_', '-', $this->settings['grid']),
          'path' => $grid['path'],
          'primary' => alpha_theme_get_setting('alpha_primary_' . $this->settings['grid'], NULL, $this->theme),
          'layouts' => array(),
          'columns' => $grid['columns'],
          'css' => array(),          
        );
        
        if (!$this->settings['responsive']) {
          $grid['layouts'] = isset($grid['layouts'][$this->grid['primary']]) ? array($this->grid['primary'] => $grid['layouts'][$this->grid['primary']]) : array();          
        }
        
        foreach ($grid['layouts'] as $layout => $title) {
          $responsive = alpha_theme_get_setting('alpha_layouts_' . $this->grid['grid'] . '_' . $layout . '_responsive', FALSE, $this->theme);
          
          if (!$this->settings['responsive'] || $responsive) {
            $this->grid['layouts'][$layout] = array(
              'layout' => $layout,
              'sanitized' => str_replace('_', '-', $layout),
              'attached' => array(),
              'responsive' => $responsive,
              'media' => $this->settings['responsive'] ? alpha_theme_get_setting('alpha_layouts_' . $this->grid['grid'] . '_' . $layout . '_media', 'all', $this->theme) : 'all',
              'weight' => alpha_theme_get_setting('alpha_layouts_' . $this->grid['grid'] . '_' . $layout . '_weight', 0, $this->theme),
            );

            foreach ($trail as $item) {
              $path = drupal_get_path('theme', $item) . '/css';
              $file = str_replace('_', '-', $item) . '-' . $this->grid['sanitized'];

              if ($attached = alpha_find_stylesheet($path, $file)) {
                $this->grid['layouts'][$layout]['attached'][] = $attached;
              }

              if ($attached = alpha_find_stylesheet($path, $file . '-' . $this->grid['layouts'][$layout]['sanitized'])) {
                $this->grid['layouts'][$layout]['attached'][] = $attached;
              }
            }
            
            foreach ($this->grid['layouts'][$layout]['attached'] as $attached) {
              $basename = $layout . '::' . $attached;
              
              $this->grid['css'][$basename]['processed'] = FALSE;
              $this->grid['css'][$basename]['item'] = $basename;
              $this->grid['css'][$basename]['options'] = array(
                'group' => 2000,
                'weight' => $this->grid['layouts'][$layout]['weight'],                
                'data' => $attached, 
                'basename' => $basename,                
                'media' => $this->grid['layouts'][$layout]['media'],
                'browsers' => $nonie,
                'aggregate' => TRUE,                
              );

              if ($this->settings['responsive'] && $this->grid['primary'] == $layout) {
                $basename = 'ie::' . $basename;
                
                $this->grid['css'][$basename]['processed'] = FALSE;
                $this->grid['css'][$basename]['item'] = $basename;
                $this->grid['css'][$basename]['options'] = array(
                  'group' => 1000,
                  'weight' => $this->grid['layouts'][$layout]['weight'],
                  'data' => $attached, 
                  'basename' => $basename,
                  'browsers' => $ie,
                );
              }
            }

            $path = $this->grid['path'] . '/' . $layout . '/' . $this->grid['sanitized'] . '-' . $this->grid['layouts'][$layout]['sanitized'];
            
            foreach ($this->grid['columns'] as $columns => $title) {
              $file = $path . '-' . $columns . '.css';
              
              $this->grid['css'][$file]['processed'] = FALSE;
              $this->grid['css'][$file]['columns'] = $columns;
              $this->grid['css'][$file]['item'] = $file;
              $this->grid['css'][$file]['options'] = array(
                'group' => 2000,
                'weight' => $this->grid['layouts'][$layout]['weight'],
                'media' => $this->grid['layouts'][$layout]['media'],
                'browsers' => $nonie,          
                'aggregate' => TRUE,
              );
              
              if ($this->settings['responsive'] && $this->grid['primary'] == $layout) {                
                $basename = 'ie::' . $layout . '::' . $path . '-' . $columns . '.css';

                $this->grid['css'][$basename]['processed'] = FALSE;
                $this->grid['css'][$basename]['columns'] = $columns;
                $this->grid['css'][$basename]['item'] = $basename;
                $this->grid['css'][$basename]['options'] = array(
                  'group' => 1000,
                  'weight' => $this->grid['layouts'][$layout]['weight'],
                  'data' => $file,
                  'basename' => $basename,
                  'browsers' => $ie,                
                );
              }
            }
          }
        }
      }      

      alpha_alter('alpha_grid', $this->grid, $this->theme);
    }

    return $this->grid;
  }
  
  /**
   * @todo
   */
  function css() {
    if (!isset($this->css)) {
      $this->css = alpha_retrieve_css($this->theme);

      alpha_alter('alpha_css', $this->css, $this->theme);
    }
    
    return $this->css;
  }
  
  /**
   * @todo
   */
  function libraries() {
    if (!isset($this->libraries)) {
      $this->libraries = alpha_retrieve_libraries($this->theme);
      
      alpha_alter('alpha_libraries', $this->libraries, $this->theme);
    }
    
    return $this->libraries;
  }
  
  /**
   * @todo
   */
  function sections() {
    if (!isset($this->sections)) {
      $this->sections = array(
        'header' => t('Header'),
        'content' => t('Content'),
        'footer' => t('Footer'),
      );
    }
    
    return $this->sections;
  }
  
  /**
   * @todo
   */
  function zones() {
    if (!isset($this->zones)) {
      $this->sections();
      $this->zones = array();

      if ($zones = alpha_info('zones', $this->theme)) {
        foreach ($zones as $zone => $title) {
          $section = alpha_zone_get_setting('section', $zone, NULL, $this->theme);
          
          $this->zones[$zone] = array(
            'zone' => $zone,
            'name' => $title,
            'enabled' => isset($this->sections[$section]),
            'force' => alpha_zone_get_setting('force', $zone, FALSE, $this->theme),
            'columns' => alpha_zone_get_setting('columns', $zone, 0, $this->theme),
            'section' => $section,
            'weight' => alpha_zone_get_setting('weight', $zone, 0, $this->theme),
            'wrapper' => alpha_zone_get_setting('wrapper', $zone, FALSE, $this->theme),
            'wrapper_css' => alpha_zone_get_setting('wrapper_css', $zone, NULL, $this->theme),
            'primary' => alpha_zone_get_setting('primary', $zone, NULL, $this->theme),
            'order' => alpha_zone_get_setting('order', $zone, FALSE, $this->theme),
            'css' => alpha_zone_get_setting('css', $zone, NULL, $this->theme),
          );
        }
      }

      uasort($this->zones, 'drupal_sort_weight');    
      alpha_alter('alpha_zones', $this->zones, $this->theme);
    }

    return $this->zones;
  }
  
  /**
   * @todo
   */
  function regions() {
    if (!isset($this->regions)) {
      $this->zones();
      $this->regions = array();      
      $exclude = alpha_regions_exclude();

      foreach (system_region_list($this->theme) as $region => $title) {
        if (!in_array($region, $exclude)) {
          $zone = alpha_region_get_setting('zone', $region, NULL, $this->theme);
          $prefix = alpha_region_get_setting('prefix', $region, 0, $this->theme);
          $columns = alpha_region_get_setting('columns', $region, 1, $this->theme);
          $suffix = alpha_region_get_setting('suffix', $region, 0, $this->theme);
          $enabled = !empty($zone) && isset($this->zones[$zone]);
          
          $this->regions[$region] = array(
            'region' => $region,
            'name' => $title,
            'zone' => $zone,
            'enabled' => $enabled,
            'force' => alpha_region_get_setting('force', $region, FALSE, $this->theme),
            'prefix' => $prefix,
            'columns' => $columns,
            'suffix' => $suffix,
            'width' => $prefix + $columns + $suffix,
            'push' => 0,
            'pull' => 0,
            'css' => alpha_region_get_setting('css', $region, NULL, $this->theme),
            'weight' => alpha_region_get_setting('weight', $region, 0, $this->theme),
            'position' => alpha_region_get_setting('position', $region, 0, $this->theme),
            'primary' => $enabled && $this->zones[$zone]['primary'] == $region,
          );
        }
      }

      uasort($this->regions, 'drupal_sort_weight');
      alpha_alter('alpha_regions', $this->regions, $this->theme);
    }

    return $this->regions;
  }
  
  /**
   * @todo
   */
  function cacheable() {
    $cacheable = array('settings', 'libraries', 'css', 'grid', 'regions', 'zones', 'sections');
    
    alpha_alter('alpha_cacheable', $cacheable, $this->theme);
    
    return $cacheable;
  }
}