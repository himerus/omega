<?php

namespace Drupal\omega\Layout;

use Drupal\omega\Theme\OmegaSettingsInfo;
use Drupal\omega\Style\OmegaStyle;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;

class OmegaLayout implements OmegaLayoutInterface {

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The file system handler service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileHandler;

  /**
   * An array of Drupal themes, each an array of information about that theme.
   *
   * @var array
   */
  public $themes;

  /**
   * Constructs a layout object.
   *
   * @param ThemeHandlerInterface $theme_handler
   * @param FileSystemInterface $file_handler
   */
  public function __construct(ThemeHandlerInterface $theme_handler, FileSystemInterface $file_handler) {
    $this->themeHandler = $theme_handler;
    $this->fileHandler = $file_handler;
    $this->themes = $this->themeHandler->rebuildThemeData();
  }

  /**
   * @inheritdoc
   */
  public static function saveLayoutData() {
    // TODO: Implement saveLayout() method.
  }

  /**
   * @inheritdoc
   */
  public static function saveLayoutFiles() {

  }
  /**
   * @inheritdoc
   */
  public static function exportLayout() {
    // TODO: Implement exportLayout() method.
  }

  /**
   * @inheritdoc
   */
  public static function compileLayout() {
    // TODO: Implement compileLayout() method.
  }

  /**
   * @inheritdoc
   */
  public static function compileLayoutScss() {
    // TODO: Implement compileLayoutScss() method.
  }

  /**
   * @inheritdoc
   */
  public static function compileLayoutCss() {
    // TODO: Implement compileLayoutCss() method.
  }

  /**
   * @inheritdoc
   */
  public static function generateLayout() {
    // TODO: Implement generateLayout() method.
  }

  /**
   * @inheritdoc
   */
  public static function getAvailableLayouts($theme) {
    // grab the defined layouts in config/install/$theme.layouts.yml
    $layouts = \Drupal::config($theme . '.omega-layouts')->get();
    foreach ($layouts AS $layout => $null) {
      // grab the configuration for the requested layout
      $layout_config_object = \Drupal::config($theme . '.layout.' . $layout);
      // assign the values to our array
      $layouts[$layout] = $layout_config_object->get();
      unset($layouts[$layout]['_core']);
    }
    unset($layouts['_core']);
    return $layouts;
  }

  /**
   * @inheritdoc
   */
  public static function getActiveLayout() {
    // The active theme being used
    $theme = \Drupal::theme()->getActiveTheme()->getName();
    // Is this page the front page?
    $front = \Drupal::service('path.matcher')
      ->isFrontPage() ? \Drupal::service('path.matcher')->isFrontPage() : FALSE;
    // Is this page a node?
    $nid = \Drupal::routeMatch()->getRawParameter('node') ? \Drupal::routeMatch()
      ->getRawParameter('node') : FALSE;
    // Is this page a taxonomy term?
    $term = \Drupal::routeMatch()
      ->getParameter('taxonomy_term') ? \Drupal::routeMatch()
      ->getParameter('taxonomy_term') : FALSE;
    // Is this page a view?
    $view_id = \Drupal::routeMatch()
      ->getParameter('view_id') ? \Drupal::routeMatch()
      ->getParameter('view_id') : FALSE;
    // All parameters for the page
    $params = \Drupal::routeMatch()->getParameters();

    $layoutProvider = OmegaLayout::getLayoutProvider($theme);
    // setup default layout
    $defaultLayout = theme_get_setting('default_layout', $layoutProvider);
    $layout = $defaultLayout;

    // if it is a node, check for and assign alternate layout
    if ($nid) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = \Drupal\node\Entity\Node::load($nid);
      $type = $node->getType();
      $nodeLayout = theme_get_setting('node_type_' . $type . '_layout', $layoutProvider);
      $layout = $nodeLayout ? $nodeLayout : $defaultLayout;
    }

    // if it is a views page, check for and assign alternate layout

    if ($view_id) {
      // @todo: Ensure this views integration is flexible.
      // Grab the string value of the display_id parameter
      $view_display_id = \Drupal::routeMatch()->getParameter('display_id');
      $view_layout_id = 'views_view_' . $view_id . '_' . $view_display_id . '_layout';
      $viewLayout = theme_get_setting($view_layout_id);
      $layout = $viewLayout ? $viewLayout : $defaultLayout;
    }

    // if it is a term page, check for and assign alternate layout
    // @todo: Since this view is more specific than the VIEW taxonomy_term_page_1, we should remove that view from the options in the theme settings.
    if ($term) {
      $vocab = $term->getVocabularyId();
      $vocabLayout = theme_get_setting('taxonomy_' . $vocab . '_layout');
      $layout = $vocabLayout ? $vocabLayout : $defaultLayout;
    }

    // if it is the front page, check for an alternate layout
    // this should come AFTER all other adjustments
    // This ensures if someone has set an individual node page, term page, etc.
    // as the front page, the front page setting has more priority
    if ($front) {
      $homeLayout = theme_get_setting('home_layout', $layoutProvider);
      $layout = $homeLayout ? $homeLayout : $defaultLayout;
    }

    return array(
      'theme' => $layoutProvider,
      'layout' => $layout,
    );
  }

  /**
   * @inheritdoc
   */
  public static function getLayoutProvider($theme) {
    // Create Omega Settings Object
    $omegaSettings = new OmegaSettingsInfo($theme);

    // get the default settings for the current theme
    $themeSettings = $omegaSettings->getThemeInfo();

    // get the value of 'inherit_layout' from THEME.info.yml
    $inherit_layout = isset($themeSettings->info['inherit_layout']) ? $themeSettings->info['inherit_layout'] : FALSE;

    // we have encountered a theme that inherits layout from a base theme
    // now we will scan the array of applicable base themes looking for the
    // closest parent providing layout and not inheriting it
    if ($inherit_layout) {
      // grab the base themes
      $baseThemes = $themeSettings->base_themes;
      // remove the core themes from the list
      unset($baseThemes['stable'], $baseThemes['classy']);
      // put the base themes in the proper order to traverse for layouts
      $baseThemes = array_reverse($baseThemes);

      foreach ($baseThemes AS $baseKey => $baseName) {
        $baseThemeSettings = $omegaSettings->getThemeInfo($baseKey);
        $base_inherit_layout = $baseThemeSettings->info['inherit_layout'];

        if (!$base_inherit_layout) {
          // we've found the first base theme in the chain that does provide its own layout
          // so we will return the key of that theme to use.
          return $baseKey;
        }
      }

    }
    // this theme provides its own layout, so just return the appropriate theme name
    else {
      return $theme;
    }
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public static function getAvailableBreakpoints($theme) {
    // Check for breakpoints module and set a warning and a flag to disable much of the theme settings if its not available
    $breakpoints_module = \Drupal::moduleHandler()->moduleExists('breakpoint');
    $breakpoint_groups = array();
    $breakpoint_options = array();
    if ($breakpoints_module == TRUE) {
      // get all the breakpoint groups available to Drupal
      $all_breakpoint_groups = \Drupal::service('breakpoint.manager')
        ->getGroups();
      // get all the base themes of this theme
      $baseThemes = \Drupal::theme()->getActiveTheme()->getBaseThemes();

      $theme_ids = array(
        $theme => \Drupal::theme()->getActiveTheme()->getExtension()->info['name']
      );
      foreach ($baseThemes AS $theme_key => $data) {
        // create/add to array with base themes as values
        $clean_theme_name = $data->getExtension()->info['name'];
        $theme_ids[$theme_key] = $clean_theme_name;
      }

      // cycle all the breakpoint groups and see if they are a part of this theme or its base theme(s)
      foreach ($all_breakpoint_groups as $group_key => $group_values) {
        // get the theme name that provides this breakpoint group
        $breakpoint_theme = \Drupal::service('breakpoint.manager')
          ->getGroupProviders($group_key);
        // see if the theme providing the breakpoint group is part of our base theme structure
        $breakpoint_theme_name = key($breakpoint_theme);
        if (array_key_exists($breakpoint_theme_name, $theme_ids)) {
          $breakpoint_groups[$group_key] = \Drupal::service('breakpoint.manager')
            ->getBreakpointsByGroup($group_key);
        }
      }

      foreach ($breakpoint_groups as $group => $breakpoint_values) {
        if ($breakpoint_values !== array()) {
          // get the theme name that provides this breakpoint group
          $breakpoint_theme = \Drupal::service('breakpoint.manager')
            ->getGroupProviders($group);
          // see if the theme providing the breakpoint group is part of our base theme structure
          $breakpoint_theme_id = key($breakpoint_theme);
          $breakpoint_theme_name = $theme_ids[$breakpoint_theme_id];
          $breakpoint_options[$breakpoint_theme_name][$group] = $group;
        }
      }
    }
    else {
      drupal_set_message(t('Omega requires the <b>Breakpoint module</b>. Open the <a href="@extendpage" target="_blank">Extend</a> page and enable Breakpoint.', array('@extendpage' => base_path() . 'admin/modules')), 'warning');
    }
    return $breakpoint_options;
  }

  /**
   * @inheritdoc
   */
  public static function getActiveBreakpoints($layout, $theme) {
    // get the default layout and convert to name for breakpoint group
    $breakpointGroupId = theme_get_setting('breakpoint_group_' . $layout, $theme);
    $breakpointGroup = \Drupal::service('breakpoint.manager')
      ->getBreakpointsByGroup($breakpointGroupId);
    if ($breakpointGroup) {
      // custom theme breakpoints
      return $breakpointGroup;
    }
    else {
      // default omega breakpoints
      drupal_set_message('The breakpoint group for your theme could not be found. Using default Omega version instead.', 'warning');
      return \Drupal::service('breakpoint.manager')
        ->getBreakpointsByGroup('omega.standard');
    }
  }

  /**
   * @inheritdoc
   */
  public static function layoutAdjust() {
    // TODO: Implement layoutAdjust() method.
  }

  /**
   * @inheritdoc
   */
  public static function cleanBreakpointId(\Drupal\breakpoint\Breakpoint $breakpoint) {
    return str_replace($breakpoint->getGroup() . '.', "", $breakpoint->getBaseId());
  }
}
