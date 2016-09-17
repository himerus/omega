<?php

namespace Drupal\omega\Export;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Asset\Exception\InvalidLibraryFileException;

/**
 * OmegaExport declares methods used to build a new subtheme
 *
 * @todo: Refactor OmegaExport and all the things.
 */
class OmegaExport implements OmegaExportInterface {

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
   * The data of the pending export/theme build.
   *
   * @var array
   */
  protected $export;

  /**
   * The build info for the theme.
   *
   * @var array
   */
  protected $build;

  /**
   * The build info for the theme.
   *
   * @var array
   */
  public $themes;

  /**
   * An array of default theme directories
   *
   * @var array
   */
  public $themeDirectories;

  /**
   * @var array of options including
   * - kit_directory: full path to kit directory
   * - kitName:
   */
  public $kitData;
  /**
   * An array of files/directories that would be excluded when generating
   * a subtheme.
   *
   * @var array
   */
  public $omegaExcludedThemeFiles;

  /**
   * Constructs an export object.
   *
   * @param ThemeHandlerInterface $theme_handler
   * @param FileSystemInterface $file_handler
   */
  public function __construct(ThemeHandlerInterface $theme_handler, FileSystemInterface $file_handler) {
    $this->themeHandler = $theme_handler;
    $this->fileHandler = $file_handler;
    $this->themes = $this->themeHandler->rebuildThemeData();
    $this->themeDirectories = [
      'themes',
      'themes/custom',
      'themes/contrib',
      'core/themes',
    ];
    $this->omegaExcludedThemeFiles = [
      'export-exclude', // administrative templates
      'theme-settings', // theme-settings directory in omega
      //'', // custom template files for theme generation
      'images', // any top level images directory
      'src', // primary classes from Omega (or any source)
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExport($export) {
    $this->export = $export;

    $this->build = array(
      // Global Options
      // User provided friendly name of the new theme.
      'name' => $this->getFriendlyName(),
      // User provided machine name of the new theme.
      'machine' => $this->getMachineName(),
      // User provided description for the new theme.
      'description' => $this->getDescription(),
      // User provided version number for the new theme.
      'version' => $this->getVersion(),
      // Path where the new theme will be installed.
      'destination_path' => $this->getBuildPath(),
      // Path of the parent theme for the new theme.
      'parent_path' => $this->getParentPath(),
      // Type of export (clone, subtheme).
      'type' => $this->getExportType(),
      // Parent theme for the new theme, either the base theme or the theme being cloned.
      'parent' => $this->getOptions('export_theme_base'),
      // An array of items to search/replace when creating a subtheme.
      // @todo: Refine 'replace' options array to actually do things.
      'replace' => array(
        // The system_name could be either OMEGA_SUBTHEME if we are creating a subtheme
        // with the .kit functionality, or the parent theme's system name if we're creating a clone
        'system_name' => $this->getExportType() == 'subtheme' ? 'OMEGA_SUBTHEME' : $this->getOptions('export_theme_base'),
        'friendly_name' => '',
      ),
      // If the new theme should be installed by default.
      'install' => $this->getOptions('export_install_auto') ? TRUE : FALSE,
      // If the new theme should be set as default theme upon installation.
      'install_default' => $this->getOptions('export_install_default') ? TRUE : FALSE,
      // Subtheme Options
      // If block positions should be exported.
      'block_positions' => $this->getOptions('export_include_block_positions') ? TRUE : FALSE,
      // If a blank library should be included in the export.
      'blank_library' => $this->getOptions('export_include_blank_library') ? TRUE : FALSE,
      // If a 'blank' theme-settings.php file should be included.
      'theme_settings_php' => $this->getOptions('export_include_theme_settings_php') ? TRUE : FALSE,
      // If templates should be copied over to the new theme.
      'theme_theme_templates' => $this->getOptions('export_include_templates') ? TRUE : FALSE,
      // If the layout should be inherited only, or customizable in the new theme.
      'theme_inherit_layout' => $this->getOptions('export_inherit_layout') ? TRUE : FALSE,
      // If the layout should be inherited only, or customizable in the new theme.
      'theme_scss_support' => $this->getOptions('export_enable_scss_support') ? TRUE : FALSE,
      // If config.rb support should be included.
      'theme_configrb_create' => $this->getOptions('export_enable_configrb') ? TRUE : FALSE,
      // If Gemfile support should be included.
      'theme_gemfile_create' => $this->getOptions('export_enable_gemfile') ? TRUE : FALSE,
      // If Gruntfile.js support should be included.
      'theme_gruntfile_create' => $this->getOptions('export_enable_gruntfile') ? TRUE : FALSE,
    );
    $this->kitData = [
      'clone_directory' => DRUPAL_ROOT . '/' . drupal_get_path('theme', $this->build['parent']),
      'kit_directory' => $this->getKitPath(),
    ];
    return $this->build;
  }

  /**
   * {@inheritdoc}
   */
  public function saveExport(FormStateInterface $form_state) {
    // Prepare the directory for operations and create copy.
    $this->directoryPrepare();
    /// Grab the info file data.
    $info = $this->retrieveInfoFile();
    // Update the Friendly Name.
    $info['name'] = $this->build['name'];
    // Update the Description.
    $info['description'] = $this->build['description'];
    // Update the Version.
    $info['version'] = $this->build['version'];
    // Update the force export value to false so we can edit this new theme regardless of what the other theme was set to.
    $info['force_export'] = FALSE;
    // Update scss_support.
    $info['scss_support'] = $this->build['theme_scss_support'];
    // If this subtheme is inheriting its layout from parent.
    $info['inherit_layout'] = $this->build['theme_inherit_layout'];

    // Perform varying operations based on the type of theme
    // that we have selected to create.
    switch ($this->build['type']) {
      case "clone":

        break;
      case "subtheme":
        // Update the Base theme
        $info['base theme'] = $this->build['parent'];
        //$this->generateThemeFile();
        $this->generateThemeSettingsFile();
        //$this->destroyLibraries();
        $this->generateBlankLibrary();
        $this->generateTemplateFiles();
        $this->generateConfigrb();
        $this->generateGemfile();
        $this->generateGruntfile();
        break;
    }

    // Save the $info data to the new info file.
    $this->updateInfoFile($info);

    // Enable overrides of Omega scss libraries.
    $this->generateScssSupport();

    // Throw a useful message that lets a user know their theme has been created
    drupal_set_message(t('New theme created: <strong>' . $this->build['name'] . '</strong>'));

    // Now we check to see if we've opted to install or install and set as default theme and redirect accordingly
    // @todo - Creating an install link or redirect seem to cause odd issues.

    // $install_link = Link::createFromRoute('Install', 'system.theme_install', array('theme' => $this->build['machine']));
    // $install_default_link = Link::createFromRoute('Install and set as default', 'system.theme_set_default', array('theme' => $this->build['machine']));
    // drupal_set_message(t($install_link->toString() . ' or ' . $install_default_link->toString() . ' your new theme now.'));


    /*
    $form_state->setRedirect(
      'block.admin_display_theme',
      array(
        'theme' => $form_state->getValue('theme'),
      ),
      array('query' => array('block-placement' => Html::getClass($this->entity->id())))
    );
    */
    if ($this->build['install_default']) {
      // we should install the theme and set it as the default theme and enable any dependencies
      //$form_state->setRedirect('system.theme_set_default', array('query' => array('theme' => $this->build['machine'],'token' => '')));
    }
    elseif ($this->build['install']) {
      // Still failing this based on the token or the system not thinking the theme exists yet
      // we should just install the theme and any dependencies
      $form_state->setRedirect(
        'system.theme_install',
        array(),
        array(
          'query' => array(
            'theme' => $this->build['machine'],
          )
        ));
    }
    else {

    }

    // Redirect to the main appearance listing page after creating a new theme.
    // Currently this is needed, as a proper installation and redirect TO the new theme
    // attempted above has been difficult to accomplish.
    $form_state->setRedirect('system.themes_page');
  }


  /**
   * Function to handle the discovery and copying of any css/scss files that should
   * be overridden for a particular theme being created
   * @param string $theme Theme System Name
   * @return array of libraries-override to be injected into .info.yml.
   *
   * This has an issue when multiple chained sub-themes attempt to apply/provide
   * overrides of the 'same' file.
   * @see https://www.drupal.org/node/2642122
   * @see http://cgit.drupalcode.org/drupal/tree/core/modules/system/tests/themes/test_theme/test_theme.info.yml
   * Once a subtheme of Omega has opted to override/clone the library CSS files
   * Then the path/key to that library asset is a full path
   * We need a discovery of any library overrides already in place in parent themes
   * of the theme we are creating to ensure the libraries-override array is keyed properly
   * for drupal to understand which one we want to use
   *
   * Let's assume the following theme structure:
   * Omega (primary base theme controller)
   * Omega Subtheme (direct subtheme of Omega, potentially a base theme for user/site)
   * Omega Subtheme Subtheme (subtheme of the subtheme, a subtheme of the 'base' for site)
   *
   * Assume now that both the subtheme and subtheme's subtheme BOTH implement the SCSS
   * overrides. "Omega Subtheme" has copies of all the CSS that Omega provides with the option
   * to override those styles. Now when we create "Omega Subtheme Subtheme" we must properly
   * be overriding the override and not the original in Omega. #confusing.
   *
   * The following must be how the $librariesOverride array is constructed:
   *
   * libraries-override:
   *   omega/omega_html_elements:
   *     css:
   *       component:
   *         /themes/THEME_THAT_PROVIDED_LAST_OVERRIDE/style/css/html-elements.css: style/css/html-elements.css
   *                ^                                                                 ^
   *                absolute path to original overriding theme                        relative path to new override
   */

  protected function processStyleOverrides($theme) {

    $themeObject = $this->themes[$theme];

    // The library sources start as the base themes of the theme submitted
    $librarySources = $themeObject->base_themes;

    // if this is a clone (not subtheme) then we wouldn't include the 'parent'
    // as an override since it is copied by the clone operation
    if ($this->build['type'] == 'subtheme') {
      // Now add in the current theme to the librarySources since we will pass the
      // parent theme during operation, and not the current/newly created theme
      $librarySources[$theme] = $themeObject->info['name'];
    }
    // setup some themes to skip.
    // Essentially trimming this down to only Omega and any Omega subthemes.
    $ignore_base_themes = array(
      'stable',
      'classy'
    );

    $libraries = array();
    $declaredOverrides = array();
    // cycle our sources, and load up data
    foreach ($librarySources as $themeKey => $themeName) {
      if (!in_array($themeKey, $ignore_base_themes)) {
        // path to theme
        $sourcePath = DRUPAL_ROOT . '/' . drupal_get_path('theme', $themeKey);
        // path to libraries.yml file for theme
        $library_file = $sourcePath . '/' . $themeKey . '.libraries.yml';
        if (file_exists($library_file)) {
          try {
            $libraries[$themeKey] = Yaml::decode(file_get_contents($library_file));
          }
          catch (InvalidDataTypeException $e) {
            // Rethrow a more helpful exception to provide context.
            throw new InvalidLibraryFileException(sprintf('Invalid library definition in %s: %s', $library_file, $e->getMessage()), 0, $e);
          }
        }

        // grab the .info data for the current theme
        $themeInfo = $this->themes[$themeKey]->info;
        // assign any declared overrides via libraries-override
        $declaredOverrides[$themeKey] = isset($themeInfo['libraries-override']) ? $themeInfo['libraries-override'] : FALSE;
      }
    }
    // we now have the libraries for all the relevant parent themes keyed by theme name
    // as well as all the declared overrides for any libraries keyed by theme name
    // now we should cycle those libraries to create an array with appropriate data
    // so that we can define the libraries-override in the .info.yml file for the theme
    // we are creating. This is going to do like 4 billion foreach loops.
    $librariesOverride = array();
    foreach ($libraries AS $libraryTheme => $themeLibraries) {
      // now cycle the libraries available for that theme
      foreach ($themeLibraries as $libraryKey => $libraryData) {

        $libraryName = $libraryTheme . '/' . $libraryKey;
        // make sure we are 'allowed' to clone this library's css
        if ($libraryData['omega']['allow_clone_for_subtheme']) {

          // We need to check here before cycling the CSS array if another theme
          // has overridden this library. If the $libraryName key exists in any of
          // the themes in $declaredOverrides, we should use THAT theme's version
          // of the file when performing the copy operation as well as providing the
          // proper path (now absolute rather than relative) to declare it in the overrides
          // for this new theme.
          // @see https://www.drupal.org/node/2642122

          // now we will cycle the potential groups of CSS files
          foreach ($libraryData['css'] AS $cssGroup => $cssFiles) {
            // now we will cycle any files listed in the group
            foreach ($cssFiles AS $cssFile => $null) {
              $previousOverrideProvider = FALSE;
              // run a loop through the declared overrides and look for a duplicate
              // or previously overridden version of this css file.
              foreach ($declaredOverrides AS $overridingTheme => $overridingLibraries) {

                // Let's check to see if this file has previously been overridden.
                // Problem is that if this is the SECOND (or subsequent) time an asset is being
                // overridden, then the path used as the key is not the same as the relative path from the
                // original.

                // @todo - The order of the if/elseif/else should likely be reversed so the changes cascade appropriately.

                // FEELS LIKE HERE I MAY NEED TO FOREACH AGAIN IN ORDER TO DETERMINE THE FOLLOWING:
                // $overridingTheme isn't the same as the theme path in the library override, but instead
                // the path for a parent theme (not necessarily the base theme of this theme) but could be
                // any of the themes in the base themes
                // ANOTHER FOREACH THOUGH SEEMS WRONG, THERE HAS TO BE A WAY TO DETERMINE THIS WITHOUT CYCLING
                // THROUGH THE $overridingLibraries.

                // THIS MAY NEED TO BE EXTRAPOLATED TO A FUNCTION CALL SO THAT IT CAN LOOP AS MANY TIMES
                // AS NEEDED TO FIND A MATCH.

                if ($previousOverrideProvider) {
                  // this path would represent an absolute pathed override meaning an override of an override
                  $previouslyOverriddenFilePath = '/' . drupal_get_path('theme', $previousOverrideProvider) . '/' . $cssFile;
                }
                // A CSS file that has been overridden multiple times
                if ($previousOverrideProvider && isset($declaredOverrides[$overridingTheme][$libraryName]['css'][$cssGroup][$previouslyOverriddenFilePath])) {
                  // this means this theme HAS overrides.
                  // Let's store that theme name for use in next iteration
                  $previousOverrideProvider = $overridingTheme;
                  // path to overriding theme location of library asset
                  $providerPath = DRUPAL_ROOT . '/' . drupal_get_path('theme', $previousOverrideProvider);
                  // full system path to previously overriden CSS file
                  $cssSource = $providerPath . '/' . $declaredOverrides[$overridingTheme][$libraryName]['css'][$cssGroup][$previouslyOverriddenFilePath];
                  // provide an absolute path from drupal root to the file that has already been overridden.
                  $cssFilePath = '/' . drupal_get_path('theme', $previousOverrideProvider) . '/' . $cssFile;
                }
                // A CSS file that has been overridden for the FIRST time because the key is still the relative path
                elseif (isset($declaredOverrides[$overridingTheme][$libraryName]['css'][$cssGroup][$cssFile])) {
                  // we've found an original (first) override of the primary library's assets
                  // this shares the same relative path as the key as the original.

                  // this means this theme HAS overrides.
                  // Let's store that theme name for use in next iteration
                  $previousOverrideProvider = $overridingTheme;
                  // path to overriding theme location of library asset
                  $providerPath = DRUPAL_ROOT . '/' . drupal_get_path('theme', $overridingTheme);
                  // full system path to previously overriden CSS file
                  $cssSource = $providerPath . '/' . $declaredOverrides[$overridingTheme][$libraryName]['css'][$cssGroup][$cssFile];
                  // provide an absolute path from drupal root to the file that has already been overridden.
                  $cssFilePath = '/' . drupal_get_path('theme', $overridingTheme) . '/' . $cssFile;
                }
                // a CSS file that has never been overridden.
                else {
                  // no previous overrides found for this library asset
                  // path to original theme location of library asset
                  $providerPath = DRUPAL_ROOT . '/' . drupal_get_path('theme', $libraryTheme);
                  // full system path to original CSS file
                  $cssSource = $providerPath . '/' . $cssFile;
                  // provide the default relative path for the first override
                  $cssFilePath = $cssFile;
                }
              }
              // DESTINATION AND COPY CALL SHOULDN'T CHANGE
              // full system path to destination CSS file
              $cssDestination = $this->build['destination_path'] . '/' . $cssFile;
              // copy the CSS file to the new location
              $this->styleCopy($cssSource, $cssDestination);

              // also handle the SCSS copy too if it exists
              // We will need to look for an alternate version of this scss file
              // if a parent theme had already overridden it.
              if (isset($libraryData['omega']['scss'][$cssFile])) {
                // the scss file is the original path declared by the defining library
                $scssFile = $libraryData['omega']['scss'][$cssFile];
                // full system path to SCSS file
                // this could be either from the original theme, OR the overriding theme
                // depending on the value of $providerPath defined/discovered during the
                // copying of the CSS asset related to this item.
                // This means that essentially this may not need further adjustment once
                // the CSS copying/overriding method is perfected.
                $scssSource = $providerPath . '/' . $scssFile;
                // full system path to destination SCSS file
                $scssDestination = $this->build['destination_path'] . '/' . $scssFile;
                // copy the CSS file to the new location
                $this->styleCopy($scssSource, $scssDestination);
              }
              else {

              }
              // assign the appropriate data to the returned array
              // NEEDS TO BE ALTERED IN CASE IT NEEDS ABSOLUTE PATH
              $librariesOverride[$libraryName]['css'][$cssGroup][$cssFilePath] = $cssFile;
            }
          }
        }
      }
    }
    // at this point $librariesOverride is the exact array we need to use for the
    // libraries-override section in the new .info.yml.
    // Now this function should be passed back to OmegaExport
    return $librariesOverride;
  }

  /**
   * Function to act as a wrapper for copy() that ensures a target destination
   * directory exists before performing the copy
   * This function should negate the need for $this->createStyleDirectories()
   * @param string $source Full path to source theme
   * @param string $destination Full path to destination theme
   */
  protected function styleCopy($source, $destination) {
    if (file_exists($source)) {
      $destinationRoot = $this->build['destination_path'] . '/';
      // first, strip out the core theme path from the destination file
      // so that we are left with only the relative path to the file in the theme
      $absoluteDestinationDir = pathinfo($destination, PATHINFO_DIRNAME);
      $relativeDestination = str_replace($destinationRoot, '', $absoluteDestinationDir);

      $subDirectories = explode('/', $relativeDestination);

      foreach ($subDirectories AS $directory) {
        if (!is_dir($destinationRoot . $directory)) {
          mkdir($destinationRoot . $directory);
        }
        // add the newly created (or previously existing) directory to the $destinationRoot
        // so that the next loop in the foreach checks the right place
        $destinationRoot .= $directory . '/';
      }
      // now we should be sure that the full path exists in order to copy file.
      copy($source, $destination);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function destroyLibraries() {
    // clear out all css files
    $this->directoryPurgeFileType($this->build['destination_path'] . '/js', 'js', '/^(\.(\.)?|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/');
    // @todo: Ensure destoryLibraries() still functions properly with kit/clone.
    // clear out all css files
    $this->directoryPurgeFileType($this->build['destination_path'] . '/style/css', 'css', '/^(\.(\.)?|CVS|\.sass-cache|.*layout.*.css|\.svn|\.git|\.DS_Store)$/');
    // clear out all scss files
    $this->directoryPurgeFileType($this->build['destination_path'] . '/style/scss', 'scss', '/^(\.(\.)?|CVS|\.sass-cache|_omega-style-vars.scss|.*layout.*.scss|\.svn|\.git|\.DS_Store)$/');
    // clear out the libraries.yml file
    $library_file = $this->build['destination_path'] . '/' . $this->build['machine'] . '.libraries.yml';
    if (file_exists($library_file)) {
      unlink($library_file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createLibrary() {

    // Process the CSS file for the new library
    $source = $this->getKitPath() . '/style/css/OMEGA_SUBTHEME.css';
    $destination = $this->build['destination_path'] . '/style/css/' . $this->build['machine'] . '.css';
    // copy the default file
    $cssFile = $this->fileCopy($source, $destination);

    if ($cssFile) {
      // make it usable by injecting the correct theme name for the functions
      $this->fileStrReplace($destination, 'OMEGA_SUBTHEME', $this->build['machine']);
      //drupal_set_message('CSS sample file created successfully...', 'status');
    }
    else {
      //drupal_set_message(t('Error copying CSS File... <strong><small>'. $destination . '</small></strong>'), 'error');
    }

    // Process the SCSS file for the new library
    $source = $this->getKitPath() . '/style/scss/OMEGA_SUBTHEME.scss';
    $destination = $this->build['destination_path'] . '/style/scss/' . $this->build['machine'] . '.scss';
    // copy the default file
    $scssFile = $this->fileCopy($source, $destination);
    if ($scssFile) {
      // make it usable by injecting the correct theme name for the functions
      $this->fileStrReplace($destination, 'OMEGA_SUBTHEME', $this->build['machine']);
      //drupal_set_message('SCSS sample file created successfully...', 'status');
    }
    else {
      //drupal_set_message(t('Error copying SCSS File... <strong><small>'. $destination . '</small></strong>'), 'error');
    }

    // Process the JS file for the new library
    $source = $this->getKitPath() . '/js/OMEGA_SUBTHEME.js';
    $destination = $this->build['destination_path'] . '/js/' . $this->build['machine'] . '.js';
    // copy the default file
    $jsFile = $this->fileCopy($source, $destination);
    if ($jsFile) {
      // make it usable by injecting the correct theme name for the functions
      $this->fileStrReplace($destination, 'OMEGA_SUBTHEME', $this->build['machine']);
      //drupal_set_message('JS sample file created successfully...', 'status');
    }
    else {
      //drupal_set_message(t('Error copying JS File... <strong><small>'. $destination . '</small></strong>'), 'error');
    }

    // Process the library file for the new library
    $source = $this->getKitPath() . '/OMEGA_SUBTHEME.libraries.yml';
    $destination = $this->build['destination_path'] . '/' . $this->build['machine'] . '.libraries.yml';
    // copy the default file
    $libraryFile = $this->fileCopy($source, $destination);
    if ($libraryFile) {
      // make it usable by injecting the correct theme name for the functions
      $this->fileStrReplace($destination, 'OMEGA_SUBTHEME', $this->build['machine']);
      //drupal_set_message('Library file created successfully...', 'status');
    }
    else {
      //drupal_set_message(t('Error copying Library File... <strong><small>'. $destination . '</small></strong>'), 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getKitPath() {
    // @todo: Refactor for other themes to provide a .kit.
    // Default location for Omega .kit directory.
    return DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega') . '/../.kit';
  }

  /**
   * {@inheritdoc}
   */
  public function getBuildPath() {
    // Destination path for newly created theme
    return DRUPAL_ROOT . '/' . $this->getOptions('export_destination_path') . '/' . $this->getMachineName();
  }

  /**
   * {@inheritdoc}
   */
  public function getParentPath() {
    switch ($this->build['type']) {
      case "clone":
        // Path to theme we will clone.
        return $this->kitData['clone_directory'];
        break;
      case "subtheme":
        // Path to .kit directory we will create subtheme from.
        return $this->kitData['kit_directory'];
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getInfo($key = '') {
    if (!empty($key)) {
      return $this->export['export_details'][$key];
    }
    return $this->export['export_details'];
  }

  /**
   * {@inheritdoc}
   */
  public function getInfoFile() {
    return $this->getBuildPath() . '/' . $this->build['machine'] . '.info.yml';
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions($key = '') {
    if (!empty($key)) {
      return $this->export['export_options'][$key];
    }
    return $this->export['export_options'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFriendlyName() {
    return $this->getInfo('theme_friendly_name');
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineName() {
    return $this->getInfo('theme_machine_name');

  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->getInfo('export_description');
  }

  /**
   * {@inheritdoc}
   */
  public function getVersion() {
    return $this->getInfo('export_version');
  }

  /**
   * {@inheritdoc}
   */
  public function getExportType() {
    return $this->getOptions('export_type');
  }


  public function directoryPurgeFileType($directory, $filetype, $ignore = '/^(\.(\.)?|.*OMEGA_SUBTHEME.*|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
    $dir = opendir($directory);
    while ($file = readdir($dir)) {
      if (!preg_match($ignore, $file)) {
        // directory found, call function again on this directory to scan deeper
        if (is_dir($directory . '/' . $file)) {
          $this->directoryPurgeFileType($directory . '/' . $file, $filetype, $ignore);
        }
        else {
          $extension = substr(strrchr($file, "."), 1);
          if ($extension == $filetype) {
            unlink($directory . '/' . $file);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function omegaThemeClone($source, $target, $ignore = '/^(\.(\.)?|CVS|\.node-modules|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
    $dir = opendir($source);
    $this->fileHandler->mkdir($target);
    while ($file = readdir($dir)) {
      if (!preg_match($ignore, $file) && !in_array($file, $this->omegaExcludedThemeFiles)) {
        // directory found, call function again on this directory to scan deeper
        if (is_dir($source . '/' . $file)) {
          $this->omegaThemeClone($source . '/' . $file, $target . '/' . $file, $ignore);
        }
        else {
          // copy the file to new location
          $fileLocation = $target . '/' . $file;
          copy($source . '/' . $file, $fileLocation);

          // if the file name itself has the machine name of the original theme
          // let's rename it to the new machine name
          if (strpos($file, $this->build['replace']['system_name']) !== FALSE) {
            $fileLocation = $target . '/' . str_replace($this->build['replace']['system_name'], $this->build['machine'], $file);
            rename($target . '/' . $file, $fileLocation);
          }

          // open any files and search for things to replace
          $this->fileStrReplace($fileLocation, $this->build['replace']['system_name'], $this->build['machine']);
        }
      }
    }
    closedir($dir);
    // at the end of this function call, we have a new theme built, files renamed and search/replaced
    // we should be ready to reset system to recognize the new theme for new operations
    //$this->refreshThemeData();
  }


  /**
   * {@inheritdoc}
   */

  public function retrieveInfoFile() {
    return $this->yamlDecode(file_get_contents($this->getInfoFile()));
  }

  /**
   * {@inheritdoc}
   */
  public function updateInfoFile($info) {
    // Update the .info.yml
    $new_info = $this->yamlEncode($info);
    $infoUpdated = file_put_contents($this->getInfoFile(), $new_info);
    if (!$infoUpdated) {
      drupal_set_message("Could not save " . $this->build['machine'] . ".info.yml", "error");
    }
  }

  /**
   * Prepare and create a subtheme.
   *
   * @todo: Need to refactor the omegaThemeClone for clones/subthemes
   */
  public function directoryPrepare() {
    // Ensure any directories we might need exist.
    $this->themeDirectoryPrepare();
    switch ($this->build['type']) {
      case "clone":
        // copy the parent theme to new theme's location
        $this->omegaThemeClone($this->kitData['clone_directory'], $this->build['destination_path']);
        break;
      case "subtheme":
        // copy the parent theme to new theme's location
        $this->omegaThemeClone($this->kitData['kit_directory'], $this->build['destination_path']);
        break;
    }
  }

  /**
   * Ensure all theme directories defined in $this->themeDirectories
   */
  public function themeDirectoryPrepare() {
    foreach ($this->themeDirectories as $path) {
      $dir = DRUPAL_ROOT . '/' . $path;
      if (!is_dir($dir)) {
        drupal_set_message(t('Directory: <strong>' . $path . '</strong> does not exist. <strong>Creating it now...</strong>'));
        $this->fileHandler->mkdir($dir, 0777);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fileCopy($source, $destination) {
    if (file_exists($source)) {
      $copy = copy($source, $destination);
      if ($copy) {
        // Silently return.
        return TRUE;
      }
      else {
        drupal_set_message(t('File copy failed on function: <strong><small>copy(' . $source . ', ' . $destination . ');</small></strong>'), 'error');
        return FALSE;
      }
    }
    drupal_set_message(t('Source file not found: <strong><small>' . $source . '</small></strong>'), 'error');
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function fileRemove($file) {
    if (file_exists($file)) {
      unlink($file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fileStrReplace($file_path, $find, $replace) {
    if (file_exists($file_path)) {
      $file_contents = file_get_contents($file_path);
      $file_contents = str_replace($find, $replace, $file_contents);
      file_put_contents($file_path, $file_contents);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function yamlEncode(array $php) {
    $yaml = Yaml::encode($php);
    return $yaml;
  }

  /**
   * {@inheritdoc}
   */
  public function yamlDecode($yaml) {
    $php = Yaml::decode($yaml);
    return $php;
  }


  /**
   * {@inheritdoc}
   */
  public function refreshThemeData() {

    //$this->themes = \Drupal::service('theme_handler')->listInfo();
    //\Drupal::service('theme_handler')->refreshInfo();

    // In case the active theme gets requested later in the same request we need
    // to reset the theme manager.
    //\Drupal::theme()->resetActiveTheme();

  }


  /**
   * {@inheritdoc}
   */
  protected function generateTemplateFiles() {
    // @todo: This needs to change with update for subtheme kit/clones
    if ($this->build['theme_theme_templates']) {
      // leave template folder as is since we already copied it
      // we may however need to do some search and replace operations?
    }
    else {
      // we should remove all the template files in the template folder
      // since we want this to be a subtheme and have template inheritance from the parent theme
      $this->directoryPurgeFileType($this->build['destination_path'] . '/templates', 'twig');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function generateThemeSettingsFile() {
    // @todo, the logic should change to remove it if it's NOT enabled since it is in the .kit by default
    // If a theme-settings.php file was requested, let's create a default
    // Since this file was originally copied fully from the parent theme (assuming the parent theme had one)
    // We either need to create the default theme-settings.php with basic examples, OR remove the file since
    // any theme settings from the parent theme will already be present for this new subtheme
    $destination = $this->build['destination_path'] . '/theme-settings.php';
    if ($this->build['theme_settings_php'] && !file_exists($destination)) {
      $source = $this->getKitPath() . '/theme-settings.php';
      // copy the theme settings file
      $themeSettingsFile = $this->fileCopy($source, $destination);
      if ($themeSettingsFile) {
        // make it usable by injecting the correct theme name for the functions
        $this->fileStrReplace($destination, $this->build['replace']['system_name'], $this->build['machine']);
      }
    }
    else {
      $this->fileRemove($destination);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function generateThemeFile() {
    // We will copy over and replace any .theme file since this is going to be a subtheme rather than a clone
    // In a subtheme, you wouldn't want the same logic running again.
    // This setup determines if to include default samples, or basically just make the .theme file empty
    $source = $this->getKitPath() . '/OMEGA_SUBTHEME.theme';

    $destination = $this->build['destination_path'] . '/' . $this->build['machine'] . '.theme';
    // copy the theme settings file
    $themeFile = $this->fileCopy($source, $destination);
    if ($themeFile) {
      // Make it usable by injecting the correct theme name for the functions.
      $this->fileStrReplace($destination, 'OMEGA_SUBTHEME', $this->build['machine']);
    }
    else {
      drupal_set_message("Could not save " . $this->build['machine'] . ".theme file", "error");
    }

  }

  /**
   * We have selected to allow this theme to customize the SCSS variables.
   * This means that for all the available CSS files with corresponding SCSS,
   * we should create a copy of those files to this new theme, and implement them as
   * a library-override in the .info file.
   * This will also create the _omega-style-vars.scss file that will be configurable via the
   * interface under SCSS Variables.
   * Anytime in the future when saving theme settings, assuming the "Compile SCSS Directly"
   * under General Options is enabled, the individual SCSS files will be recompiled after
   * the _omega-style-vars.scss is saved. If you are handling the SCSS compiling yourself, then only
   * the variable file would be changed, and your compass watch would handle any of the related files
   * that need to be rewritten.
   */
  protected function generateScssSupport() {
    if ($this->build['theme_scss_support']) {
      // open info file
      $info = $this->retrieveInfoFile();
      // Let's find all the CSS files available to us from our parent themes.
      $library_overrides = $this->processStyleOverrides($this->build['parent']);
      // assign the overrides to the .info array
      // @todo - what happens if a subtheme manually created overrides? Need some merge here.
      $info['libraries-override'] = $library_overrides;
      $this->updateInfoFile($info);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function generateBlankLibrary() {
    // Declare some important files
    $info_file = $this->getInfoFile();
    // open info file
    $info = $this->yamlDecode(file_get_contents($info_file));
    if ($this->build['blank_library']) {

      // then we will build an empty library/js/css include if requested during build
      $this->createLibrary();

      // add the empty library to the .info.yml array
      $info['libraries'] = array(
        $this->build['machine'] . '/' . $this->build['machine'],
      );
      // encode the info array to yaml
      $new_info = $this->yamlEncode($info);
      // save the new yaml to file
      $infoUpdated = file_put_contents($info_file, $new_info);
      if (!$infoUpdated) {
        drupal_set_message("Could not save " . $this->build['machine'] . ".info.yml", "error");
      }
    }
  }

  /**
   * {@inheritdoc}
   * @todo The default config.rb needs to be adjusted, and setup to include the possibility for multiple additional_import_paths for both Omega and ALl parent themes?
   */
  protected function generateConfigrb() {
    // destination FOR the new config.rb file
    $destination = $this->build['destination_path'] . '/config.rb';
    if ($this->build['theme_configrb_create']) {
      // source of the config.rb file
      $source = $this->getKitPath() . '/config.rb';

      // copy the config.rb
      $configRbFile = $this->fileCopy($source, $destination);
      if ($configRbFile) {
        // make it usable by injecting the correct theme name for the functions
        $omega_scss_path = DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega') . '/style/scss';
        $rel_path_to_omega_scss = $this->find_relative_path($this->build['destination_path'], $omega_scss_path);
        $this->fileStrReplace($destination, 'OMEGA_SCSS_PATH', $rel_path_to_omega_scss);
      }
      else {
        drupal_set_message("Error saving config.rb.", "error");
      }
    }
    else {
      $this->fileRemove($destination);
    }
  }


  /**
   * {@inheritdoc}
   */
  protected function generateGemfile() {
    $destination = $this->build['destination_path'] . '/Gemfile';
    if ($this->build['theme_gemfile_create']) {
      $source = $this->getKitPath() . '/Gemfile';
      // copy the Gemfile
      $Gemfile = $this->fileCopy($source, $destination);
      if (!$Gemfile) {
        drupal_set_message("Error saving Gemfile.", "error");
      }
    }
    else {
      // remove the default Gemfile if it exists
      $this->fileRemove($destination);
      // update the destination var to Gemfile.lock
      $destination = $this->build['destination_path'] . '/Gemfile.lock';
      // remove the default Gemfile.lock if it exists
      $this->fileRemove($destination);
    }
  }

  /**
   * {@inheritdoc}
   * @todo: generateConfigrb(), generateGemfile() and generateGruntfile() could/should be combined to a single method.
   */
  protected function generateGruntfile() {
    $destination = $this->build['destination_path'] . '/Gruntfile.js';
    if ($this->build['theme_gruntfile_create']) {
      $source = $this->getKitPath() . '/Gruntfile.js';
      // copy the Gruntfile
      $Gruntfile = $this->fileCopy($source, $destination);
      if (!$Gruntfile) {
        drupal_set_message("Error saving Gruntfile.", "error");
      }
    }
    else {
      // remove the default Gemfile if it exists
      $this->fileRemove($destination);
    }
  }

  /**
   *
   * Find the relative file system path between two file system paths
   *
   * @param  string $frompath Path to start from
   * @param  string $topath Path we want to end up in
   *
   * @return string             Path leading from $frompath to $topath
   */
  public function find_relative_path($frompath, $topath) {
    $from = explode(DIRECTORY_SEPARATOR, $frompath); // Folders/File
    $to = explode(DIRECTORY_SEPARATOR, $topath); // Folders/File
    $relpath = '';

    $i = 0;
    // Find how far the path is the same
    while (isset($from[$i]) && isset($to[$i])) {
      if ($from[$i] != $to[$i]) {
        break;
      }
      $i++;
    }
    $j = count($from) - 1;
    // Add '..' until the path is the same
    while ($i <= $j) {
      if (!empty($from[$j])) {
        $relpath .= '..' . DIRECTORY_SEPARATOR;
      }
      $j--;
    }
    // Go to folder from where it starts differing
    while (isset($to[$i])) {
      if (!empty($to[$i])) {
        $relpath .= $to[$i] . DIRECTORY_SEPARATOR;
      }
      $i++;
    }

    // Strip last separator
    return substr($relpath, 0, -1);
  }
}
