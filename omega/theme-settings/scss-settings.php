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

$form['variables']['variables_status']['#markup'] .= '<p><strong>NOTICE:</strong>While the settings in this form are saved, the functionality is not currently tied into regenerating the SCSS and CSS files. This will be completed soon.</p>';


$form['variables']['variables_info'] = array(
  '#prefix' => '<div class="messages messages--warning omega-variables-info">',
  '#markup' => '',
  '#suffix' => '</div>',
  '#weight' => -9999,
);

$form['variables']['variables_info']['#markup'] .= '<p><strong>The variables represented here are SCSS variables</strong>. When they are changed, the theme must be recompiled and appropriate SCSS and CSS files overwritten.</p>';
$form['variables']['variables_info']['#markup'] .= '<p class="description"><em>While this is ridiculously cool, use at your own risk as it IS modifying files in your theme directly. I can only be responsible for so much... <strong>WHEN IN DOUBT, MAKE A BACKUP!!!</strong></em> <strong>\o/</strong></p>';

$form['variables']['colors'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('variables colors')),
  '#title' => t('Color Variables'),
  '#weight' => -999,
  //'#group' => 'omega',
  '#open' => TRUE,
);

$variables = theme_get_setting('variables', $theme);
//dsm($variables);
$primaryColor1 = isset($variables['colors']['primaryColor1']) ? $variables['colors']['primaryColor1'] : '3a3a3a';
$form['variables']['colors']['primaryColor1'] = array(
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
);

$primaryColor2 = isset($variables['colors']['primaryColor2']) ? $variables['colors']['primaryColor2'] : '5a5a5a';
$form['variables']['colors']['primaryColor2'] = array(
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
);

$primaryColor3 = isset($variables['colors']['primaryColor3']) ? $variables['colors']['primaryColor3'] : 'CCCCCC';
$form['variables']['colors']['primaryColor3'] = array(
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
);

$primaryColor4 = isset($variables['colors']['primaryColor4']) ? $variables['colors']['primaryColor4'] : '1a1a1a';
$form['variables']['colors']['primaryColor4'] = array(
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
);

$primaryColor5 = isset($variables['colors']['primaryColor5']) ? $variables['colors']['primaryColor5'] : '9a9a9a';
$form['variables']['colors']['primaryColor5'] = array(
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
);



$form['variables']['fonts'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('variables', 'fonts', 'clearfix')),
  '#title' => t('Font Variables'),
  '#weight' => -899,
  //'#group' => 'omega',
  '#open' => TRUE,
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
);

$form['variables']['breakpoints'] = array(
  '#type' => 'details',
  '#attributes' => array('class' => array('variables breakpoints')),
  '#title' => t('Breakpoint Variables'),
  '#weight' => -898,
  //'#group' => 'omega',
  '#open' => TRUE,
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
  '#open' => TRUE,
);