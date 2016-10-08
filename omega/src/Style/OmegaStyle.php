<?php

namespace Drupal\omega\Style;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Class OmegaStyle
 *
 * The OmegaStyle class offers a transition between original procedural
 * functions provided via including omega-functions.php, etc. and static
 * methods available in OmegaStyle.
 *
 * @todo: Eventually, the methods defined here should be refactored.
 * @package Drupal\omega\Style
 */
class OmegaStyle implements OmegaStyleInterface {

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
   * Constructs a style object.
   *
   * @param ThemeHandlerInterface $theme_handler
   * @param FileSystemInterface $file_handler
   */
  public function __construct(ThemeHandlerInterface $theme_handler, FileSystemInterface $file_handler) {
    $this->themeHandler = $theme_handler;
    $this->fileHandler = $file_handler;
  }

  /**
   * @inheritdoc
   */
  public static function themeStylesUpdate($source, $theme, $filetype = 'scss', $ignore = '/^(\.(\.)?|CVS|_omega-style-vars\.scss|layout|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
    /**
     * Require the phpsass library manually.
     * @todo: Avoid using require_once for phpsass.
     * @todo: Replace phpsass with either composer version or leafo/scssphp.
     */
    //$omegaRoot = DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega');
    //require_once($omegaRoot . '/src/phpsass/SassParser.php');
    //require_once($omegaRoot . '/src/phpsass/SassFile.php');

    $dir = opendir($source);

    while ($file = readdir($dir)) {
      if (!preg_match($ignore, $file)) {
        // directory found, call function again on this directory to scan deeper
        if (is_dir($source . '/' . $file)) {
          OmegaStyle::themeStylesUpdate($source . '/' . $file, $theme, $filetype, $ignore);
        }
        else {
          if (pathinfo($file, PATHINFO_EXTENSION) == $filetype) {


            $relativeSource = str_replace(realpath(".") . base_path() . drupal_get_path('theme', $theme), '', $source);


            $options = OmegaStyle::getScssOptions($relativeSource, $file, $theme);

            $parser = new SassParser($options);

            $fileLocation = $source . '/' . $file;
            $variableFile = new SassFile;
            $variableScss = '';
            $variableScss .= $variableFile->get_file_contents($fileLocation);
            $css = _omega_compile_css($variableScss, $options);

            // path to CSS file we're overriding
            $newCssFile = str_replace('scss', 'css', $fileLocation);
            // save the css file
            $cssfile = file_unmanaged_save_data($css, $newCssFile, FILE_EXISTS_REPLACE);
            // check for errors
            if ($cssfile) {
              drupal_set_message(t('CSS file saved: <strong>' . str_replace(realpath(".") . base_path(), "", $cssfile) . '</strong>'));
            }
            else {
              drupal_set_message(t('WTF005: CSS save error... : function scssDirectoryScan()'), 'error');
            }
          }
        }
      }
    }
    closedir($dir);
  }

  /**
   * @inheritdoc
   */
  public static function scssVariablesUpdate($styles, $theme) {
    // get a list of themes
    $themes = \Drupal::service('theme_handler')->listInfo();
    // get the current settings/info for the theme
    $themeSettings = $themes[$theme];

    //$styleVariables = new SassFile;
    // create full paths to the scss and css files we will be rendering.
    $styleFile = realpath(".") . base_path() . drupal_get_path('theme', $theme) . '/style/scss/_omega-style-vars.scss';
    $styleData = '@import "omega_mixins";
  
// Basic Color Variables 
';

    foreach ($styles['colors'] AS $variableName => $colorValue) {
      $styleData .= "$$variableName: #$colorValue;
";
    }

    // these are copied from the form api in scss-settings.php. needs to be pulled out
    // to a reusable variable that can be edited in one place
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

    $styleData .= '
// Basic Font Variables
';
    foreach ($styles['fonts'] AS $variableName => $fontValue) {
      $styleData .= "$$variableName: " . $fontStyleValues[$fontValue] . ";
";
    }

    // save the scss file
    $stylefile = file_unmanaged_save_data($styleData, $styleFile, FILE_EXISTS_REPLACE);
    // check for errors
    if ($stylefile) {
      drupal_set_message(t('SCSS file saved: <strong>' . str_replace(realpath(".") . base_path(), "", $styleFile) . '</strong>'));
    }
    else {
      drupal_set_message(t('WTF004: SCSS save error... : function _omega_update_style_scss()'), 'error');
    }

    // If compile is turned off, we'll only be writing the new variables file above.
    // Compass could also handle this process once the variables file is updated.
    // we will only convert them to css should we have the "Compile SCSS" enabled.
    $compile_scss = theme_get_setting('compile_scss', $theme);
    $compile = isset($compile_scss) ? $compile_scss : FALSE;
    if ($compile) {
      // find all our scss files and open/save them as they should include the _omega-style-vars.scss that we've already updated
      $source = realpath(".") . base_path() . drupal_get_path('theme', $theme) . '/style/scss';
      OmegaStyle::themeStylesUpdate($source, $theme, 'scss');
    }
  }

  /**
   * @inheritdoc
   */
  public static function compileScss() {
    // TODO: Implement compileScss() method.
  }

  /**
   * @inheritdoc
   */
  public static function compileCss($scss, $options) {
    $css = _omega_compile_css($scss, $options);
    return $css;
  }

  /**
   * @inheritdoc
   * @todo: Ensure ALL parent theme paths are present to scan
   * @todo: Figure out when getImportPaths() is ever used.
   */
  public static function getImportPaths($theme) {
    $omega_path = drupal_get_path('theme', 'omega');
    $theme_path = drupal_get_path('theme', $theme);
    $scss_paths = array(
      $omega_path . '/style/scss',
      $omega_path . '/style/scss/grids',
      $theme_path . '/style/scss',
    );
    return $scss_paths;
  }

  /**
   * @inheritdoc
   */
  public static function setImportPaths() {
    // TODO: Implement setImportPaths() method.
  }

  /**
   * @inheritdoc
   */
  public static function getScssOptions($relativeSource, $file, $theme) {
    $omegaPath = realpath(".") . base_path() . drupal_get_path('theme', 'omega');
    $themePath = realpath(".") . base_path() . drupal_get_path('theme', $theme);
    // default options for richthegeek/phpsass
    return array(
      'style' => 'expanded',
      'cache' => FALSE,
      'debug' => FALSE,
      'filename' => array(
        'dirname' => $relativeSource,
        'basename' => $file
      ),
      'debug_info' => FALSE,
      'line_numbers' => FALSE,
      'load_paths' => array(
        $themePath . '/style/scss',
        $omegaPath . '/style/scss',
        $omegaPath . '/style/scss/grids',
      ),
      //'extensions'     =>  array('compass'=>array()),
      'syntax' => 'scss',
    );
  }

  /**
   * @inheritdoc
   */
  public static function getOptionalLibraries($theme) {
    $status = theme_get_setting('styles', $theme);
    $themeHandler = \Drupal::service('theme_handler');
    $library_discovery = \Drupal::service('library.discovery');
    $themes = $themeHandler->rebuildThemeData();
    $themeObject = $themes[$theme];
    $baseThemes = $themeObject->base_themes;

    $ignore_libraries = array(
      'omega/omega_admin',
      // removed as it is only used for theme admin page(s) and is required
    );

    // create a variable to hold the full library data
    $allLibraries = array();
    // create a variable to combine all the libraries we can select/desect in our form
    $returnLibraries = array();
    // the libraries for the primary theme
    $themeLibraries = $library_discovery->getLibrariesByExtension($theme);
    foreach ($themeLibraries as $libraryKey => $themeLibrary) {
      if (!in_array($theme . '/' . $libraryKey, $ignore_libraries)) {
        $allLibraries[$libraryKey] = $themeLibrary;
        $returnLibraries[$theme . '/' . $libraryKey] = array(
          'title' => isset($themeLibrary['omega']['title']) ? $themeLibrary['omega']['title'] : $theme . '/' . $libraryKey,
          'description' => isset($themeLibrary['omega']['description']) ? $themeLibrary['omega']['description'] : 'No Description Available. :(',
          'library' => $theme . '/' . $libraryKey,
          'status' => isset($status[$theme . '/' . $libraryKey]) ? $status[$theme . '/' . $libraryKey] : TRUE,
          'allow_disable' => isset($themeLibrary['omega']['allow_enable_disable']) ? $themeLibrary['omega']['allow_enable_disable'] : TRUE,
          'allow_clone' => isset($themeLibrary['omega']['allow_clone_for_subtheme']) ? $themeLibrary['omega']['allow_clone_for_subtheme'] : TRUE,
        );
      }
    }

    // setup some themes to skip.
    // Essentially trimming this down to only Omega and any Omega subthemes.
    $ignore_base_themes = array(
      'stable',
      'classy'
    );

    // the libraries for any parent theme
    foreach ($baseThemes as $baseKey => $baseTheme) {
      if (!in_array($baseKey, $ignore_base_themes)) {
        foreach ($library_discovery->getLibrariesByExtension($baseKey) as $libraryKey => $themeLibrary) {
          if (!in_array($baseKey . '/' . $libraryKey, $ignore_libraries)) {
            $allLibraries[$libraryKey] = $themeLibrary;
            $returnLibraries[$baseKey . '/' . $libraryKey] = array(
              'title' => isset($themeLibrary['omega']['title']) ? $themeLibrary['omega']['title'] : $baseKey . '/' . $libraryKey,
              'description' => isset($themeLibrary['omega']['description']) ? $themeLibrary['omega']['description'] : 'No Description Available. :(',
              'library' => $baseKey . '/' . $libraryKey,
              'status' => isset($status[$baseKey . '/' . $libraryKey]) ? $status[$baseKey . '/' . $libraryKey] : TRUE,
              'allow_disable' => isset($themeLibrary['omega']['allow_enable_disable']) ? $themeLibrary['omega']['allow_enable_disable'] : TRUE,
              'allow_clone' => isset($themeLibrary['omega']['allow_clone_for_subtheme']) ? $themeLibrary['omega']['allow_clone_for_subtheme'] : TRUE,
            );
          }
        }
      }
    }
    return $returnLibraries;
  }
}
