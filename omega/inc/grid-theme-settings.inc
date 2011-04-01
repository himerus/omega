<?php
$themes = list_themes();
$theme = $form_state['build_info']['args'][0];
$settings_theme_info = $themes[$theme]->info;

for ($i = 0; $i <= 24; $i++) {
  $grids[$i] = $i;
}
for ($i = 0; $i <= 23; $i++) {
  $spacing[$i] = $i;
}
$containers = array(
  '12' => '12 column grid',
  '16' => '16 column grid',
  '24' => '24 column grid',
);
// Region Settings

// create a wrapper fieldset for default grid configurations
$form['omega_grid']['defaults'] = array(
  '#type' => 'fieldset',
  '#title' => t('Omega Layout Configuration'),
  '#weight' => -100,
);
// provide default container width
$form['omega_grid']['defaults']['omega_default_container_width'] = array(
  '#type' => 'select',
  '#title' => t('Default container width'),
  '#default_value' => omega_theme_get_setting('omega_default_container_width'),
  '#options' => $containers,
  '#weight' => -50,
  '#description' => t('This width is used for regions like $help, $messages and other non-important regions in page.tpl.php'),
);

// use a responsive grid instead of the default 960gs
$responsive = omega_theme_get_setting('omega_responsive_grid') ? omega_theme_get_setting('omega_responsive_grid') : 'disabled';
$form['omega_grid']['defaults']['omega_responsive_grid'] = array(
  '#type' => 'radios',
  '#description' => t('Enable adaptive/responsive grid layout. This allows the grid to resize based on the size of the users device. This incorporates mobile first design, so the default behavior is to show the simplest (stacked) version of the layout, and for devices that meet certain display criteria (width), new CSS and grid sizing will be applied. <strong>Implementing this feature will ignore the fixed/fluid setting as it currently is only available for the fixed size grid.</strong>'),
  '#title' => t('Enable / Disable Responsive Grid Layout'),
  '#default_value' => $responsive,
  '#options' => array(
    'enabled' => t('Enable responsive grid sizing (theme default)'),
    'disabled' => t('Disable responsive grid sizing'),
  ),
);
// in order to not have to define this state over and over, assign it to a var
// when the responsive grid is enabled, any #states set with $responsive_state will be visible
$responsive_state = array(
  'visible' => array(
    ':input[name="omega_responsive_grid"]' => array('value' => t('enabled')),
  )
);
// select fixed/fluid for all zones/entire site.
$form['omega_grid']['defaults']['omega_responsive_mobile'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable mobile sizing'),
  '#description' => t('This option cannot be disabled as Omega, and all subthemes designed in a responsive manner are mobile first, then add other sizes for browsers that can support @media queries.'),
  '#default_value' => 1,
  '#disabled' => TRUE,
  '#states' => $responsive_state,
);

$responsive_medium = omega_theme_get_setting('omega_responsive_medium') ? omega_theme_get_setting('omega_responsive_medium') : 0;
$form['omega_grid']['defaults']['responsive_settings']['omega_responsive_medium'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable 720px grid (medium)'),
  '#description' => t('The 720px grid is designed to work well for tablets in a portrait viewing mode.'),
  '#default_value' => $responsive_medium,
  '#states' => $responsive_state,
);

$responsive_default = omega_theme_get_setting('omega_responsive_default') ? omega_theme_get_setting('omega_responsive_default') : 0;
$form['omega_grid']['defaults']['omega_responsive_default'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable 960px grid (default)'),
  '#description' => t('The 960px grid is designed to work well for tablets in a landscape viewing mode and most web browsers.'),
  '#default_value' => $responsive_default,
  '#states' => $responsive_state,
);

$responsive_large = omega_theme_get_setting('omega_responsive_large') ? omega_theme_get_setting('omega_responsive_large') : 0;
$form['omega_grid']['defaults']['omega_responsive_large'] = array(
  '#type' => 'checkbox',
  '#title' => t('Enable 1200px grid (large)'),
  '#description' => t('The 1200px grid is designed to work well for users with a lot of screen real estate. However, this may not look well in a single sidebar layout. Design considerations must be made when using this layout.'),
  '#default_value' => $responsive_large,
  '#states' => $responsive_state,
);

// viewport scaling
$responsive_viewport = omega_theme_get_setting('omega_responsive_viewport') ? omega_theme_get_setting('omega_responsive_viewport') : 'disabled';
$form['omega_grid']['defaults']['omega_responsive_viewport'] = array(
  '#type' => 'radios',
  '#title' => t('Enable / Disable Viewport Scaling'),
  '#description' => t('Enable/Disable Viewport scaling on mobile devices. By default, smart phones like iPhone/Android will attempt to auto scale a website when viewing in the internal browser. Disabling this setting will modify that feature, and tell the mobile device to respect our theme as we have a mobile version. Leaving this option enabled will leave the mobile scaling unaffected.'),
  '#default_value' => $responsive_viewport,
  '#options' => array(
    'enabled' => t('Do NOT modify viewport scaling (enable)'),
    'disabled' => t('Modify viewport scaling (disable)'),
  ),
  '#states' => $responsive_state,
);


// select fixed/fluid for all zones/entire site.
$default_omega_layout = omega_theme_get_setting('omega_fixed_fluid') ? omega_theme_get_setting('omega_fixed_fluid') : 'fixed';
$form['omega_grid']['defaults']['omega_fixed_fluid'] = array(
  '#type' => 'radios',
  '#description' => t('You may select fluid layout, or the default fixed width layout.'),
  '#title' => t('Fixed / Fluid Layout'),
  '#default_value' => $default_omega_layout,
  '#options' => array(
    'fixed' => t('Fixed width 960 Grid (theme default)'),
    'fluid' => t('Fluid width'),
  ),
  '#states' => array(
    'visible' => array(
      ':input[name="omega_responsive_grid"]' => array('value' => t('disabled')),
    ),
  ),
);

$zones = $settings_theme_info['zones'];
$all_regions = $settings_theme_info['regions'];
// Looping over each zone, and region in that zone.
foreach ($zones AS $zone => $regions) {
  // creating an additional fieldset for each zone 
  $form['omega_grid'][$zone] = array(
    '#type' => 'fieldset',
    '#title' => t(ucwords($zone) . ' Zone Config'),
    //'#prefix' => t('<h3 class="omega-accordion"><a href="#">' . ucwords($zone) . ' Zone Config</a></h3><div class="omega-accordion-content">'),
    //'#suffix' => '</div>', 
  );
  // provide a quick enable/disable option for this zone
  $zone_enabled = omega_theme_get_setting('omega_'. $zone .'_enabled') || omega_theme_get_setting('omega_'. $zone .'_enabled') == 0 ? omega_theme_get_setting('omega_'. $zone .'_enabled') : 1;
  $form['omega_grid'][$zone]['omega_'. $zone .'_enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable this Zone.'),
    '#description' => t('Disabling this zone will instantly hide the container and all regions within from being rendered on page.'),
    '#default_value' => $zone_enabled,
  '#weight' => -50,
  );
  // provide a full width wrapper around the zone (allowing for design elements outside the grid)
  $zone_wrapper = omega_theme_get_setting('omega_'. $zone .'_wrapper') ? omega_theme_get_setting('omega_'. $zone .'_wrapper') : 0;
  $form['omega_grid'][$zone]['omega_'. $zone .'_wrapper'] = array(
    '#type' => 'checkbox',
    '#title' => t('Provide full width wrapper around this zone.'),
    '#description' => t('Enabling this feature will give a &lt;div&gt; wrapper around the zone itself, allowing you to theme in elements that appear outside the 960 pixel container zone.'),
    '#default_value' => $zone_wrapper,
  );
  // create a container width selection menu for EACH zone
  $form['omega_grid'][$zone]['omega_'. $zone .'_container_width'] = array(
    '#type' => 'select',
    '#title' => t('Container width'),
    '#default_value' => omega_theme_get_setting('omega_'. $zone .'_container_width'),
    '#options' => $containers,
  );
  // create a chooser for static/dynamic zone
  $zone_type = omega_theme_get_setting('omega_'. $zone .'_zone_type') ? omega_theme_get_setting('omega_'. $zone .'_zone_type') : 'static';
  $form['omega_grid'][$zone]['omega_'. $zone .'_zone_type'] = array(
    '#type' => 'radios',
    '#description' => t('<p><strong>Static</strong> Zones are displayed either inline or stacked based on settings.</p><p><strong>Dynamic</strong> Zones are always displayed inline, with a primary region and MUST fit inside the container wrap. Dynamic zones also allow for push & pull classes for content first ordering.'),
    '#title' => t('Zone Type'),
    '#default_value' => $zone_type,
    '#options' => array(
      'static' => t('Static Zone'),
      'dynamic' => t('Dynamic Zone'),
    ),
  );
  // provide content first layout ordering via push/pull if this is a dynamic zone
  $region_options = array();
  // must change to a for loop to then foreach over the "associated items"
  $i=0;
  foreach($regions as $region) {
    $option_val = $region;
    if (!isset($all_regions[$region])) {
      $option_txt = ucwords($region);
    }
    else {
      $option_txt = $all_regions[$region];
    }
    $region_options[$region] = $option_txt;
  }
  // decide which region is the primary item
  // the "primary" region is the one that will absorb the size of empty regions
  // that are related in the same zone.
  $form['omega_grid'][$zone]['omega_'. $zone .'_dynamic_zone_primary_region'] = array(
    '#type' => 'select',
    '#title' => t('Primary Region'),
    '#default_value' => omega_theme_get_setting('omega_'. $zone .'_dynamic_zone_primary_region'),
    '#options' => $region_options,
    '#description' => t('The primary region is the one that will gain width when associated regions are empty.'),
    '#states' => array(
      'visible' => array(
        ':input[name="omega_'. $zone .'_zone_type"]' => array('value' => t('dynamic')),
      ),
    ),
  );
  // allow for zone classes
  $form['omega_grid'][$zone]['omega_' . $zone . '_zone_extra_classes'] = array(
    '#type' => 'textfield',
    '#title' => t('Additional Zone Classes'),
    '#size' => 60,
    '#default_value' => omega_theme_get_setting('omega_' . $zone . '_zone_extra_classes'),
    '#description' => t('You can quickly add custom CSS classes to your zone here.'),
  );
  // create friendly names for regions
  // "normal" regions provide these automatically
  // "special" regions (branding, menu, breadcrumb, search need to have a 
  // friendly name created on the fly with ucwords()
  foreach($regions as $region) {
    if (!isset($all_regions[$region]) || $region == 'content') {
      $locked_flag = ' (special region)';
      $region_name = ucwords(str_replace('_', ' ', $region));
    }
    else {
      $locked_flag = '';
      $region_name = $all_regions[$region];
    }
    // switch this area to place these region groups via weight of the region
    $weight = intval(omega_theme_get_setting('omega_' . $zone . '_' . $region . '_region_weight') + 100);
    // create a fieldset around the regions so they can be weighted by the order
    // if they are a dynamic region.
    $form['omega_grid'][$zone][$region] = array(
      '#type' => 'fieldset', 
    );
    // only add weight value to dynamic zones
    if($zone_type == 'dynamic') {
      $form['omega_grid'][$zone][$region]['#weight'] = $weight;
    }
    // create a prefix dropdown
    $form['omega_grid'][$zone][$region]['omega_' . $zone . '_' . $region . '_region_prefix'] = array(
      '#prefix' => t('<h4 class="region-title">' . $region_name . $locked_flag . '</h4><div class="prefix_config">'),
      '#type' => 'select',
      '#title' => t('Prefix'),
      '#default_value' => omega_theme_get_setting('omega_' . $zone . '_' . $region . '_region_prefix'),
      '#options' => $spacing,
      '#suffix' => '</div>',
    );
    // create the width dropdown
    $form['omega_grid'][$zone][$region]['omega_' . $zone . '_' . $region . '_region_width'] = array(
      '#prefix' => '<div class="width_config">',
      '#type' => 'select',
      '#title' => t('Width'),
      '#default_value' => omega_theme_get_setting('omega_' . $zone . '_' . $region . '_region_width'),
      '#options' => $grids,
      '#suffix' => '</div>',
    );
    // create a suffix dropdown
    $form['omega_grid'][$zone][$region]['omega_' . $zone . '_' . $region . '_region_suffix'] = array(
      '#prefix' => '<div class="suffix_config">',
      '#type' => 'select',
      '#title' => t('Suffix'),
      '#default_value' => omega_theme_get_setting('omega_' . $zone . '_' . $region . '_region_suffix'),
      '#options' => $spacing,
      '#suffix' => '</div>',
    );
    // build a weight/order select based on the number of regions in the zone
    $weights = array();
    for ($i = 1; $i <= count($regions); $i++) {
      $weights[$i] = $i;
    }
    // allow for ordering the regions in dyanmic zones
    $form['omega_grid'][$zone][$region]['omega_' . $zone . '_' . $region . '_region_order'] = array(
      '#prefix' => '<div class="weight_config">',
      '#type' => 'select',
      '#title' => t('Order'),
      '#default_value' => omega_theme_get_setting('omega_' . $zone . '_' . $region . '_region_order'),
      '#options' => $weights,
      //'#description' => t('Only used for dynamic zones, the weight will determine in what order the regions are rendered in page.tpl.php, and also on this form to demonstrate the order in which they will appear. However, source ordering will remain intact as defined in the zones[] array in your .info file.'),
      '#suffix' => '</div>',
      '#states' => array(
        'visible' => array(
          ':input[name="omega_'. $zone .'_zone_type"]' => array('value' => t('dynamic')),
        ),
      ),
      
    );
    // allow adding of classes for regions
    $form['omega_grid'][$zone][$region]['omega_' . $zone . '_' . $region . '_region_extra_classes'] = array(
      '#prefix' => '<div class="classes_config">',
      '#type' => 'textfield',
      '#title' => t('Additional Region Classes'),
      '#size' => 60,
      '#default_value' => t(omega_theme_get_setting('omega_' . $zone . '_' . $region . '_region_extra_classes')),
      '#description' => t('You can quickly add custom CSS classes to your regions here.'),
      '#suffix' => '</div>',
    );
  }
}