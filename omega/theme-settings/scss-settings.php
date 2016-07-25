<?php
// form a tree of variables that represent SCSS variables in vars.scss to be altered
$form['variables'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('variables')),
  '#title' => t('SCSS Variables'),
  '#weight' => -898,
  '#group' => 'omega',
  '#open' => FALSE,
  '#tree' => TRUE,
);

$form['variables']['variables_status'] = array(
  '#prefix' => '<div class="messages messages--error omega-variables-info">',
  '#markup' => '',
  '#suffix' => '</div>',
  '#weight' => -9999,
);

$form['variables']['variables_status']['#markup'] .= '<p><strong>NOTICE:</strong> While the settings in this form are saved, the functionality is not currently tied into regenerating the SCSS and CSS files. This will be completed soon.</p>';


$form['variables']['variables_info'] = array(
  '#prefix' => '<div class="messages messages--warning omega-variables-info">',
  '#markup' => '',
  '#suffix' => '</div>',
  '#weight' => -9999,
);

$form['variables']['variables_info']['#markup'] .= '<p><strong>The variables represented here are SCSS variables</strong>. When values are changed, and the form is saved, your <strong>style/scss/_omega-style-vars.scss</strong> will be rewritten, and the theme will be recompiled and any appropriate SCSS and CSS files overwritten accordingly with updated values.</p>';
$form['variables']['variables_info']['#markup'] .= '<p class="description">For a SCSS file in your theme to use the variables represented here, the line <em><strong>@import "_omega-style-vars.scss", "_omega-default-style-vars.scss";</strong></em> must be at the top of the SCSS file.</p>';

$form['variables']['colors'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('variables colors')),
  '#title' => t('Color Variables'),
  '#weight' => -999,
  //'#group' => 'omega',
  '#open' => FALSE,
  '#tree' => TRUE,
  '#description' => '<p>When the Omega SCSS system is enabled, the configurations here are translated to SCSS variables that help generate custom CSS styles for your theme. The color variables here can be used in any SCSS file that is in your subtheme.</p>',
);

$variables = theme_get_setting('variables', $theme);

$form['variables']['colors']['primary'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('color-config-menu')),
  '#title' => 'Primary Colors',
  '#description' => 'Default colors available for SCSS usage site-wide.',
  '#open' => FALSE,
  '#tree' => FALSE,
);

//dpm($variables);
$primaryColor1 = isset($variables['colors']['primaryColor1']) ? $variables['colors']['primaryColor1'] : '3a3a3a';
$form['variables']['colors']['primary']['primaryColor1'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'primaryColor1',
      'primary-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $primaryColor1,
  ),
  '#title' => t('Primary Color 1'),
  '#default_value' => $primaryColor1,
  '#parents' => array('variables', 'colors', 'primaryColor1'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
  '#tree' => TRUE,
);
$form['variables']['colors']['primary']['primaryColor1']['#suffix'] .= '<li><em>background-color: <strong>$primaryColor1</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor1']['#suffix'] .= '<li><em>border: 1px solid <strong>$primaryColor1</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor1']['#suffix'] .= '<li><em>color: lighten(<strong>$primaryColor1</strong>, 20%);</em></li>';
$form['variables']['colors']['primary']['primaryColor1']['#suffix'] .= '</ul></div>';

$primaryColor2 = isset($variables['colors']['primaryColor2']) ? $variables['colors']['primaryColor2'] : '5a5a5a';
$form['variables']['colors']['primary']['primaryColor2'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'primaryColor2',
      'primary-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $primaryColor2,
  ),
  '#title' => t('Primary Color 2'),
  '#default_value' => $primaryColor2,
  '#parents' => array('variables', 'colors', 'primaryColor2'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
  '#tree' => TRUE,
);
$form['variables']['colors']['primary']['primaryColor2']['#suffix'] .= '<li><em>background-color: <strong>$primaryColor2</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor2']['#suffix'] .= '<li><em>border: 1px solid <strong>$primaryColor2</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor2']['#suffix'] .= '<li><em>color: lighten(<strong>$primaryColor2</strong>, 20%);</em></li>';
$form['variables']['colors']['primary']['primaryColor2']['#suffix'] .= '</ul></div>';

$primaryColor3 = isset($variables['colors']['primaryColor3']) ? $variables['colors']['primaryColor3'] : 'CCCCCC';
$form['variables']['colors']['primary']['primaryColor3'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'primaryColor3',
      'primary-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $primaryColor3,
  ),
  '#title' => t('Primary Color 3'),
  '#default_value' => $primaryColor3,
  '#parents' => array('variables', 'colors', 'primaryColor3'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
  '#tree' => TRUE,
);
$form['variables']['colors']['primary']['primaryColor3']['#suffix'] .= '<li><em>background-color: <strong>$primaryColor3</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor3']['#suffix'] .= '<li><em>border: 1px solid <strong>$primaryColor3</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor3']['#suffix'] .= '<li><em>color: lighten(<strong>$primaryColor3</strong>, 20%);</em></li>';
$form['variables']['colors']['primary']['primaryColor3']['#suffix'] .= '</ul></div>';

$primaryColor4 = isset($variables['colors']['primaryColor4']) ? $variables['colors']['primaryColor4'] : '1a1a1a';
$form['variables']['colors']['primary']['primaryColor4'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'primaryColor4',
      'primary-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $primaryColor4,
  ),
  '#title' => t('Primary Color 4'),
  '#default_value' => $primaryColor4,
  '#parents' => array('variables', 'colors', 'primaryColor4'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
  '#tree' => TRUE,
);
$form['variables']['colors']['primary']['primaryColor4']['#suffix'] .= '<li><em>background-color: <strong>$primaryColor4</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor4']['#suffix'] .= '<li><em>border: 1px solid <strong>$primaryColor4</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor4']['#suffix'] .= '<li><em>color: lighten(<strong>$primaryColor4</strong>, 20%);</em></li>';
$form['variables']['colors']['primary']['primaryColor4']['#suffix'] .= '</ul></div>';

$primaryColor5 = isset($variables['colors']['primaryColor5']) ? $variables['colors']['primaryColor5'] : '9a9a9a';
$form['variables']['colors']['primary']['primaryColor5'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'primaryColor5',
      'primary-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $primaryColor5,
  ),
  '#title' => t('Primary Color 5'),
  '#default_value' => $primaryColor5,
  '#parents' => array('variables', 'colors', 'primaryColor5'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
  '#tree' => TRUE,
);
$form['variables']['colors']['primary']['primaryColor5']['#suffix'] .= '<li><em>background-color: <strong>$primaryColor5</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor5']['#suffix'] .= '<li><em>border: 1px solid <strong>$primaryColor5</strong>;</em></li>';
$form['variables']['colors']['primary']['primaryColor5']['#suffix'] .= '<li><em>color: lighten(<strong>$primaryColor5</strong>, 20%);</em></li>';
$form['variables']['colors']['primary']['primaryColor5']['#suffix'] .= '</ul></div>';

$form['variables']['colors']['menu'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('color-config-menu')),
  '#title' => 'Main Menu Styling',
  '#description' => 'Configurations here will alter the appearance of the primary menu.',
  '#open' => FALSE,
  '#tree' => FALSE,
);

$mainMenuBgColor = isset($variables['colors']['mainMenuBgColor']) ? $variables['colors']['mainMenuBgColor'] : '9a9a9a';
$form['variables']['colors']['menu']['mainMenuBgColor'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'mainMenuBgColor',
      'menu-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $mainMenuBgColor,
  ),
  '#title' => t('Main Menu Background Color'),
  '#default_value' => $mainMenuBgColor,
  '#parents' => array('variables', 'colors', 'mainMenuBgColor'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
);
$form['variables']['colors']['menu']['mainMenuBgColor']['#suffix'] .= '<li><em>background-color: <strong>$mainMenuBgColor</strong>;</em></li>';
$form['variables']['colors']['menu']['mainMenuBgColor']['#suffix'] .= '<li><em>border: 1px solid <strong>$mainMenuBgColor</strong>;</em></li>';
$form['variables']['colors']['menu']['mainMenuBgColor']['#suffix'] .= '<li><em>color: lighten(<strong>$mainMenuBgColor</strong>, 20%);</em></li>';
$form['variables']['colors']['menu']['mainMenuBgColor']['#suffix'] .= '</ul></div>';

$mainMenuFontColor = isset($variables['colors']['mainMenuFontColor']) ? $variables['colors']['mainMenuFontColor'] : '1a1a1a';
$form['variables']['colors']['menu']['mainMenuFontColor'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'mainMenuFontColor',
      'menu-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $mainMenuFontColor,
  ),
  '#title' => t('Main Menu Font Color'),
  '#default_value' => $mainMenuFontColor,
  '#parents' => array('variables', 'colors', 'mainMenuFontColor'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
);
$form['variables']['colors']['menu']['mainMenuFontColor']['#suffix'] .= '<li><em>background-color: <strong>$mainMenuFontColor</strong>;</em></li>';
$form['variables']['colors']['menu']['mainMenuFontColor']['#suffix'] .= '<li><em>border: 1px solid <strong>$mainMenuFontColor</strong>;</em></li>';
$form['variables']['colors']['menu']['mainMenuFontColor']['#suffix'] .= '<li><em>color: lighten(<strong>$mainMenuFontColor</strong>, 20%);</em></li>';
$form['variables']['colors']['menu']['mainMenuFontColor']['#suffix'] .= '</ul></div>';

$mainMenuBorderColor = isset($variables['colors']['mainMenuBorderColor']) ? $variables['colors']['mainMenuBorderColor'] : '1a1a1a';
$form['variables']['colors']['menu']['mainMenuBorderColor'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'mainMenuFontColor',
      'menu-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $mainMenuBorderColor,
  ),
  '#title' => t('Main Menu Border Color'),
  '#default_value' => $mainMenuBorderColor,
  '#parents' => array('variables', 'colors', 'mainMenuBorderColor'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
);
$form['variables']['colors']['menu']['mainMenuBorderColor']['#suffix'] .= '<li><em>background-color: <strong>$mainMenuBorderColor</strong>;</em></li>';
$form['variables']['colors']['menu']['mainMenuBorderColor']['#suffix'] .= '<li><em>border: 1px solid <strong>$mainMenuBorderColor</strong>;</em></li>';
$form['variables']['colors']['menu']['mainMenuBorderColor']['#suffix'] .= '<li><em>color: lighten(<strong>$mainMenuBorderColor</strong>, 20%);</em></li>';
$form['variables']['colors']['menu']['mainMenuBorderColor']['#suffix'] .= '</ul></div>';




$form['variables']['colors']['button'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('color-config-menu')),
  '#title' => 'Default button styling',
  '#description' => 'Configurations here will alter the appearance buttons sitewide. These include standard form buttons, and other elements like taxonomy terms on node listing pages.',
  '#open' => FALSE,
  '#tree' => FALSE,
);

$buttonBgColor = isset($variables['colors']['buttonBgColor']) ? $variables['colors']['buttonBgColor'] : '9a9a9a';
$form['variables']['colors']['button']['buttonBgColor'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'buttonBgColor',
      'menu-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $buttonBgColor,
  ),
  '#title' => t('Button Background Color'),
  '#default_value' => $buttonBgColor,
  '#parents' => array('variables', 'colors', 'buttonBgColor'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
);
$form['variables']['colors']['button']['buttonBgColor']['#suffix'] .= '<li><em>background-color: <strong>$buttonBgColor</strong>;</em></li>';
$form['variables']['colors']['button']['buttonBgColor']['#suffix'] .= '<li><em>border: 1px solid <strong>$buttonBgColor</strong>;</em></li>';
$form['variables']['colors']['button']['buttonBgColor']['#suffix'] .= '<li><em>color: lighten(<strong>$buttonBgColor</strong>, 20%);</em></li>';
$form['variables']['colors']['button']['buttonBgColor']['#suffix'] .= '</ul></div>';

$buttonFontColor = isset($variables['colors']['buttonFontColor']) ? $variables['colors']['buttonFontColor'] : '1a1a1a';
$form['variables']['colors']['button']['buttonFontColor'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'buttonFontColor',
      'menu-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $buttonFontColor,
  ),
  '#title' => t('Button Font Color'),
  '#default_value' => $buttonFontColor,
  '#parents' => array('variables', 'colors', 'buttonFontColor'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
);
$form['variables']['colors']['button']['buttonFontColor']['#suffix'] .= '<li><em>background-color: <strong>$buttonFontColor</strong>;</em></li>';
$form['variables']['colors']['button']['buttonFontColor']['#suffix'] .= '<li><em>border: 1px solid <strong>$buttonFontColor</strong>;</em></li>';
$form['variables']['colors']['button']['buttonFontColor']['#suffix'] .= '<li><em>color: lighten(<strong>$buttonFontColor</strong>, 20%);</em></li>';
$form['variables']['colors']['button']['buttonFontColor']['#suffix'] .= '</ul></div>';

$buttonBorderColor = isset($variables['colors']['buttonBorderColor']) ? $variables['colors']['buttonBorderColor'] : '1a1a1a';
$form['variables']['colors']['button']['buttonBorderColor'] = array(
  '#type' => 'textfield',
  '#size' => 5, 
  '#maxlength' => 6,
  '#attributes' => array(
    'class' => array(
      'buttonFontColor',
      'menu-color',
      'color-slider',
      'clearfix'
    ),
    'data-original-color-value' => $buttonBorderColor,
  ),
  '#title' => t('Button Border Color'),
  '#default_value' => $buttonBorderColor,
  '#parents' => array('variables', 'colors', 'buttonBorderColor'), // don't nest the variables in the resulting array
  '#suffix' => '<div class="scss-examples"><strong>SCSS Usage Examples:</strong><ul>',
);
$form['variables']['colors']['button']['buttonBorderColor']['#suffix'] .= '<li><em>background-color: <strong>$buttonBorderColor</strong>;</em></li>';
$form['variables']['colors']['button']['buttonBorderColor']['#suffix'] .= '<li><em>border: 1px solid <strong>$buttonBorderColor</strong>;</em></li>';
$form['variables']['colors']['button']['buttonBorderColor']['#suffix'] .= '<li><em>color: lighten(<strong>$buttonBorderColor</strong>, 20%);</em></li>';
$form['variables']['colors']['button']['buttonBorderColor']['#suffix'] .= '</ul></div>';


























/**
 * Primary configuration for SCSS variables related to fonts.
 */

$form['variables']['fonts'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('variables', 'fonts', 'clearfix')),
  '#title' => t('Font Variables'),
  '#weight' => -899,
  //'#group' => 'omega',
  '#open' => FALSE,
  '#tree' => FALSE,
);

$form['variables']['fonts']['fontParagraph'] = array(
  '#prefix' => '<div class="sample-font-content clearfix">',
  '#markup' => '<h2>Bacon ipsum dolor sit amet  </h2>',
  '#suffix' => '</div>',
  '#weight' => 999,
);

$form['variables']['fonts']['fontParagraph']['#markup'] .= '<p>Drumstick ham hock tri-tip meatloaf tongue, ball tip pork chop tenderloin. Meatball prosciutto ham hock, flank pork chop swine turducken boudin tenderloin. Leberkas spare ribs tenderloin, sirloin beef ham hock short ribs tri-tip corned beef shoulder ham biltong doner. Chicken turkey short loin hamburger, doner strip steak t-bone salami tongue frankfurter. Pork fatback jowl tri-tip pastrami spare ribs. Pork loin brisket prosciutto short loin swine bresaola leberkas meatball t-bone strip steak porchetta pork belly ball tip. Beef spare ribs short loin pig ground round corned beef.</p>';
$form['variables']['fonts']['fontParagraph']['#markup'] .= '<p>Tri-tip ribeye flank biltong strip steak. Doner leberkas meatball short ribs salami. Shank pancetta pork belly ground round cow meatloaf short loin t-bone. Shankle turducken strip steak corned beef prosciutto. Hamburger pancetta beef ribs jerky flank fatback short ribs bacon drumstick porchetta.</p>';

$fontStyles = array(
  'georgia' => 'Georgia',
  'times' => 'Times',
  'palatino' => 'Palatino',
  'arial' => 'Arial',
  'helvetica' => 'Helvetica Neue',
  'arialBlack' => 'Arial Black',
  'comicSans' => 'Comic Sans (Woot!!)',
  'impact' => 'Impact',
  'lucidaSans' => 'Lucida Sans',
  'tahoma' => 'Tahoma',
  'trebuchet' => 'Trebuchet MS',
  'verdana' => 'Verdana',
  'courier' => 'Courier New',
  'lucidaConsole' => 'Lucida Console',
);

$fontStyleValues = array(
  'georgia' => 'Georgia, serif',
  'times' => '"Times New Roman", Times, serif',
  'palatino' => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
  'arial' => 'Arial, Helvetica, sans-serif',
  'helvetica' => '"Helvetica Neue", Helvetica, Arial, sans-serif',
  'arialBlack' => '"Arial Black", Gadget, sans-serif',
  'comicSans' => '"Comic Sans MS", cursive, sans-serif',
  'impact' => 'Impact, Charcoal, sans-serif',
  'lucidaSans' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
  'tahoma' => 'Tahoma, Geneva, sans-serif',
  'trebuchet' => '"Trebuchet MS", Helvetica, sans-serif',
  'verdana' => 'Verdana, Geneva, sans-serif',
  'courier' => '"Courier New", Courier, monospace',
  'lucidaConsole' => '"Lucida Console", Monaco, monospace',
);

$defaultHeaderFont = isset($variables['fonts']['defaultHeaderFont']) ? $variables['fonts']['defaultHeaderFont'] : 'georgia';
$form['variables']['fonts']['defaultHeaderFont'] = array(
  '#type' => 'select',
  '#attributes' => array(
    'class' => array(
      'font-variable', 
      'clearfix'
    ),
  ),
  '#title' => 'Default Header Font',
  '#options' => $fontStyles,
  '#default_value' => $defaultHeaderFont,
  '#parents' => array('variables', 'fonts', 'defaultHeaderFont'), // don't nest the variables in the resulting array
);

$defaultBodyFont = isset($variables['fonts']['defaultBodyFont']) ? $variables['fonts']['defaultBodyFont'] : 'helvetica';
$form['variables']['fonts']['defaultBodyFont'] = array(
  '#type' => 'select',
  '#attributes' => array(
    'class' => array(
      'font-variable', 
      'clearfix'
    ),
  ),
  '#title' => 'Default Body Font',
  '#options' => $fontStyles,
  '#default_value' => $defaultBodyFont,
  '#parents' => array('variables', 'fonts', 'defaultBodyFont'), // don't nest the variables in the resulting array
);

$form['variables']['breakpoints'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('variables breakpoints')),
  '#title' => t('Breakpoint Variables'),
  '#weight' => -898,
  //'#group' => 'omega',
  '#open' => FALSE,
);

$form['variables']['breakpoints']['breakpointInfo'] = array(
  '#prefix' => '<div class="messages messages--warning breakpoint-info clearfix">',
  '#markup' => '',
  '#suffix' => '</div>',
  '#weight' => -9999,
);
$form['variables']['breakpoints']['breakpointInfo']['#markup'] .= t('<h3>Information about SCSS Breakpoints</h3>');
$form['variables']['breakpoints']['breakpointInfo']['#markup'] .= t('<p>The SCSS/CSS Breakpoints are setup to be the same as the breakpoints defined in breakpoint.breakpoint_group.theme.THEME.THEME.yml. <em>However</em>, it is possible to configure these to be different if a need arises.</p>');
$form['variables']['breakpoints']['breakpointInfo']['#markup'] .= t('<p>Essentially, the Drupal defined breakpoints are used in the layout.scss/css and the layout switching. The SCSS Breakpoint variables defined here, will apply to the way CSS is applied throughout pre-configured style rules. Any changes to these breakpoints that do not match with your layout breakpoints could cause unexpected behavior. <strong>Edit these at your own risk...</strong>.</p>');

$form['variables']['elements'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('variables elements')),
  '#title' => t('Various Element Variables'),
  '#weight' => -799,
  //'#group' => 'omega',
  '#open' => FALSE,
);