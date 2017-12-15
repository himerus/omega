<?php

namespace Drupal\omega\Export;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Asset\Exception\InvalidLibraryFileException;

/**
 * OmegaExport declares methods used to build a new subtheme.
 *
 * @todo: Refactor OmegaExport and all the things.
 * @todo: Remove OmegaExport in favor of themeBuilder
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
   * An array of Drupal themes, each an array of information about that theme.
   *
   * @var array
   */
  public $themes;

  /**
   * An array of default theme directories.
   *
   * @var array
   */
  public $themeDirectories;

  /**
   * Array of information regarding subtheme kit.
   *
   * - clone_directory: full path to clone directory.
   * - kit_directory: full path to kit directory.
   *
   * @var array
   */
  public $kitData;

  /**
   * An array of files/directories to exclude when generating a subtheme.
   *
   * @var array
   */
  public $omegaExcludedThemeFiles;

  /**
   * Constructs an export object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Drupal Theme Handler interface.
   * @param \Drupal\Core\File\FileSystemInterface $file_handler
   *   Drupal File interface.
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
      // Administrative templates to exclude.
      'export-exclude',
      // Theme-settings directory in omega should be excluded.
      'theme-settings',
      // Any top level images directory.
      'images',
      // Primary classes from Omega. (or any source)
      'src',
    ];
  }

  /**
   * Returns the information array on the export.
   *
   * {@inheritdoc}
   */
  public function buildExport($export) {
    $this->export = $export;

    $this->build = [
      // Global Options:
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
      // Parent theme for the new theme, either the base theme or the
      // theme being cloned.
      'parent' => $this->getOptions('export_theme_base'),
      // An array of items to search/replace when creating a subtheme.
      // @todo: Refine 'replace' options array to actually do things.
      'replace' => [
        // The system_name could be either omega_subtheme if
        // we are creating a subtheme.
        // With the .kit functionality, or the parent theme's system name
        // if we are creating a clone.
        'system_name' => $this->getExportType() == 'subtheme' ? 'omega_subtheme' : $this->getOptions('export_theme_base'),
        'friendly_name' => '',
      ],
      // If the new theme should be installed by default.
      'install' => $this->getOptions('export_install_auto') ? TRUE : FALSE,
      // If the new theme should be set as default theme upon installation.
      'install_default' => $this->getOptions('export_install_default') ? TRUE : FALSE,
      // Subtheme Options:
      // If block positions should be exported.
      'block_positions' => $this->getOptions('export_include_block_positions') ? TRUE : FALSE,
      // If a blank library should be included in the export.
      'blank_library' => $this->getOptions('export_include_blank_library') ? TRUE : FALSE,
      // If a 'blank' theme-settings.php file should be included.
      'theme_settings_php' => $this->getOptions('export_include_theme_settings_php') ? TRUE : FALSE,
      // If templates should be copied over to the new theme.
      'theme_theme_templates' => $this->getOptions('export_include_templates') ? TRUE : FALSE,
      // If the layout should be inherited, or customizable in the new theme.
      'theme_inherit_layout' => $this->getOptions('export_inherit_layout') ? TRUE : FALSE,
      // If the layout should be inherited, or customizable in the new theme.
      'theme_scss_support' => $this->getOptions('export_enable_scss_support') ? TRUE : FALSE,
      // If config.rb support should be included.
      'theme_configrb_create' => $this->getOptions('export_enable_configrb') ? TRUE : FALSE,
      // If Gemfile support should be included.
      'theme_gemfile_create' => $this->getOptions('export_enable_gemfile') ? TRUE : FALSE,
      // If Grunt support should be included.
      'theme_gruntfile_create' => $this->getOptions('export_enable_gruntfile') ? TRUE : FALSE,
      // If Gulp support should be included.
      'theme_gulpfile_create' => $this->getOptions('export_enable_gulpfile') ? TRUE : FALSE,
    ];
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
    // Grab the info file data.
    $info = $this->retrieveInfoFile();
    // Update the Friendly Name.
    $info['name'] = $this->build['name'];
    // Update the Description.
    $info['description'] = $this->build['description'];
    // Update the Version.
    $info['version'] = $this->build['version'];
    // Update the force export value to false so we can edit this new
    // theme regardless of what the other theme was set to.
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
        // Update the Base theme.
        $info['base theme'] = $this->build['parent'];
        $this->generateThemeSettingsFile();
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

    // Throw useful message that lets a user know their theme has been created.
    drupal_set_message(t('New theme created: <strong>@themeName</strong>', ['@themeName' => $this->build['name']]));

    // Now we check to see if we've opted to install or install and set
    // as default theme and redirect accordingly.
    if ($this->build['install_default']) {
      // We should install the theme and set it as the default theme and
      // enable any dependencies.
    }
    elseif ($this->build['install']) {
      // Still failing this based on the token or the system not thinking
      // the theme exists yet we should just install the theme and
      // any dependencies.
      $form_state->setRedirect(
        'system.theme_install',
        [],
        [
          'query' => [
            'theme' => $this->build['machine'],
          ],
        ]);
    }
    else {

    }

    // Redirect to the main appearance listing page after creating a new theme.
    // Currently this is needed, as a proper installation and redirect
    // TO the new theme attempted above has been difficult to accomplish.
    $form_state->setRedirect('system.themes_page');
  }

  /**
   * Function to handle the discovery and copying of css/scss files.
   *
   * Function to handle the discovery and copying of any css/scss files that
   * should be overridden for a particular theme being created.
   *
   * This has an issue when multiple chained sub-themes attempt to
   * apply/provide overrides of the 'same' file.
   *
   * Once a subtheme of Omega has opted to override/clone the library CSS files
   * Then the path/key to that library asset is a full path
   * We need a discovery of any library overrides already in place in
   * parent themes of the theme we are creating to ensure the
   * libraries-override array is keyed properly for drupal to understand
   * which one we want to use.
   *
   * Let's assume the following theme structure:
   * Omega
   * - (primary base theme controller)
   * Omega Subtheme
   * - (direct subtheme of Omega, potentially a base theme for user/site)
   * Omega Subtheme Subtheme
   * - (subtheme of the subtheme, a subtheme of the 'base' for site)
   *
   * Assume now that both the subtheme and subtheme's subtheme BOTH implement
   * the SCSS overrides. "Omega Subtheme" has copies of all the CSS that
   * Omega provides with the option to override those styles. Now when we
   * create "Omega Subtheme Subtheme" we must properly be overriding the
   * override and not the original in Omega. #confusing.
   *
   * The following must be how the $librariesOverride array is constructed:
   *
   * @codingStandardsIgnoreStart
   * libraries-override:
   *   omega/omega_html_elements:
   *     css:
   *       component:
   *         /themes/THEME_THAT_PROVIDED_LAST_OVERRIDE/style/css/html-elements.css: style/css/html-elements.css
   *                ^                                                                ^
   *                absolute path to original overriding theme                       relative path to new override
   * @codingStandardsIgnoreEnd
   *
   * @param string $theme
   *   Theme System Name.
   *
   * @see https://www.drupal.org/node/2642122
   * @see http://cgit.drupalcode.org/drupal/tree/core/modules/system/tests/themes/test_theme/test_theme.info.yml
   *
   * @return array
   *   Array of libraries-override to be injected into .info.yml.
   */
  protected function processStyleOverrides($theme) {

    $themeObject = $this->themes[$theme];

    // The library sources start as the base themes of the theme submitted.
    $librarySources = $themeObject->base_themes;

    // If this is a clone (not subtheme) then we wouldn't include the 'parent'
    // as an override since it is copied by the clone operation.
    if ($this->build['type'] == 'subtheme') {
      // Now add in the current theme to the librarySources since we will
      // pass the parent theme during operation, and not the current/newly
      // created theme.
      $librarySources[$theme] = $themeObject->info['name'];
    }
    // Setup some themes to skip.
    // Essentially we trim this down to only Omega and any Omega subthemes.
    $ignore_base_themes = [
      'stable',
      'classy',
    ];

    $libraries = [];
    $declaredOverrides = [];
    // Cycle our sources, and load up data.
    foreach ($librarySources as $themeKey => $themeName) {
      if (!in_array($themeKey, $ignore_base_themes)) {
        // Path to theme.
        $sourcePath = DRUPAL_ROOT . '/' . drupal_get_path('theme', $themeKey);
        // Path to libraries.yml file for theme.
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

        // Grab the .info data for the current theme.
        $themeInfo = $this->themes[$themeKey]->info;
        // Assign any declared overrides via libraries-override.
        $declaredOverrides[$themeKey] = isset($themeInfo['libraries-override']) ? $themeInfo['libraries-override'] : FALSE;
      }
    }
    // We now have the libraries for all the relevant parent themes keyed by
    // theme name as well as all the declared overrides for any libraries
    // keyed by theme name now we should cycle those libraries to create
    // an array with appropriate data so that we can define the
    // libraries-override in the .info.yml file for the theme we are creating.
    // This is going to do like 4 billion foreach loops.
    $librariesOverride = [];
    foreach ($libraries as $libraryTheme => $themeLibraries) {
      // Now cycle the libraries available for that theme.
      foreach ($themeLibraries as $libraryKey => $libraryData) {

        $libraryName = $libraryTheme . '/' . $libraryKey;
        // Make sure we are 'allowed' to clone this library's css.
        if ($libraryData['omega']['allow_clone_for_subtheme']) {

          // We need to check here before cycling the CSS array if another
          // theme has overridden this library. If the $libraryName key
          // exists in any of the themes in $declaredOverrides, we should
          // use THAT theme's version of the file when performing the copy
          // operation as well as providing the proper path (now absolute
          // rather than relative) to declare it in the overrides for this
          // new theme.
          // @see https://www.drupal.org/node/2642122
          // Now we will cycle the potential groups of CSS files.
          foreach ($libraryData['css'] as $cssGroup => $cssFiles) {
            // Now we will cycle any files listed in the group.
            foreach ($cssFiles as $cssFile => $null) {
              $previousOverrideProvider = FALSE;
              // Run a loop through the declared overrides and look for a
              // duplicate or previously overridden version of this css file.
              foreach ($declaredOverrides as $overridingTheme => $overridingLibraries) {

                // Let's check to see if this file has previously been
                // overridden. Problem is that if this is the SECOND
                // (or subsequent) time an asset is being overridden,
                // then the path used as the key is not the same as the
                // relative path from the original.
                // @todo - The order of the if/elseif/else should likely be reversed so the changes cascade appropriately.
                // FEELS LIKE HERE I MAY NEED TO FOREACH AGAIN IN ORDER TO
                // DETERMINE THE FOLLOWING:
                // $overridingTheme isn't the same as the theme path in the
                // library override, but instead the path for a parent theme
                // (not necessarily the base theme of this theme) but could be
                // any of the themes in the base themes ANOTHER FOREACH THOUGH
                // SEEMS WRONG, THERE HAS TO BE A WAY TO DETERMINE THIS
                // WITHOUT CYCLING THROUGH THE $overridingLibraries.
                // THIS MAY NEED TO BE EXTRAPOLATED TO A FUNCTION CALL SO
                // THAT IT CAN LOOP AS MANY TIMES AS NEEDED TO FIND A MATCH.
                if ($previousOverrideProvider) {
                  // This path would represent an absolute pathed override
                  // meaning an override of an override.
                  $previouslyOverriddenFilePath = '/' . drupal_get_path('theme', $previousOverrideProvider) . '/' . $cssFile;
                }
                // A CSS file that has been overridden multiple times.
                if ($previousOverrideProvider && isset($declaredOverrides[$overridingTheme][$libraryName]['css'][$cssGroup][$previouslyOverriddenFilePath])) {
                  // This means this theme HAS overrides.
                  // Let's store that theme name for use in next iteration.
                  $previousOverrideProvider = $overridingTheme;
                  // Path to overriding theme location of library asset.
                  $providerPath = DRUPAL_ROOT . '/' . drupal_get_path('theme', $previousOverrideProvider);
                  // Full system path to previously overriden CSS file.
                  $cssSource = $providerPath . '/' . $declaredOverrides[$overridingTheme][$libraryName]['css'][$cssGroup][$previouslyOverriddenFilePath];
                  // Provide an absolute path from drupal root to the file
                  // that has already been overridden.
                  $cssFilePath = '/' . drupal_get_path('theme', $previousOverrideProvider) . '/' . $cssFile;
                }
                // A CSS file that has been overridden for the FIRST time
                // because the key is still the relative path.
                elseif (isset($declaredOverrides[$overridingTheme][$libraryName]['css'][$cssGroup][$cssFile])) {
                  // We've found an original (first) override of the
                  // primary library's assets this shares the same relative
                  // path as the key as the original.
                  // This means this theme HAS overrides.
                  // Let's store that theme name for use in next iteration.
                  $previousOverrideProvider = $overridingTheme;
                  // Path to overriding theme location of library asset.
                  $providerPath = DRUPAL_ROOT . '/' . drupal_get_path('theme', $overridingTheme);
                  // Full system path to previously overriden CSS file.
                  $cssSource = $providerPath . '/' . $declaredOverrides[$overridingTheme][$libraryName]['css'][$cssGroup][$cssFile];
                  // Provide an absolute path from drupal root to the file
                  // that has already been overridden.
                  $cssFilePath = '/' . drupal_get_path('theme', $overridingTheme) . '/' . $cssFile;
                }
                // A CSS file that has never been overridden.
                else {
                  // No previous overrides found for this library asset.
                  // Path to original theme location of library asset.
                  $providerPath = DRUPAL_ROOT . '/' . drupal_get_path('theme', $libraryTheme);
                  // Full system path to original CSS file.
                  $cssSource = $providerPath . '/' . $cssFile;
                  // Provide the default relative path for the first override.
                  $cssFilePath = $cssFile;
                }
              }
              // DESTINATION AND COPY CALL SHOULDN'T CHANGE.
              // Full system path to destination CSS file.
              $cssDestination = $this->build['destination_path'] . '/' . $cssFile;
              // Copy the CSS file to the new location.
              $this->styleCopy($cssSource, $cssDestination);

              // Also handle the SCSS copy too if it exists.
              // We will need to look for an alternate version of this scss file
              // if a parent theme had already overridden it.
              if (isset($libraryData['omega']['scss'][$cssFile])) {
                // The scss file is the original path declared by the
                // defining library.
                $scssFile = $libraryData['omega']['scss'][$cssFile];
                // Full system path to SCSS file.
                // This could be either from the original theme, OR the
                // overriding theme depending on the value of $providerPath
                // defined/discovered during the copying of the CSS asset
                // related to this item. This means that essentially this
                // may not need further adjustment once the CSS
                // copying/overriding method is perfected.
                $scssSource = $providerPath . '/' . $scssFile;
                // Full system path to destination SCSS file.
                $scssDestination = $this->build['destination_path'] . '/' . $scssFile;
                // Copy the CSS file to the new location.
                $this->styleCopy($scssSource, $scssDestination);
              }
              else {

              }
              // Assign the appropriate data to the returned array.
              // NEEDS TO BE ALTERED IN CASE IT NEEDS ABSOLUTE PATH.
              $librariesOverride[$libraryName]['css'][$cssGroup][$cssFilePath] = $cssFile;
            }
          }
        }
      }
    }
    // At this point $librariesOverride is the exact array we need to use
    // for the libraries-override section in the new .info.yml.
    // Now this function should be passed back to OmegaExport.
    return $librariesOverride;
  }

  /**
   * Wrapper function for copy().
   *
   * Function to act as a wrapper for copy() that ensures a target destination
   * directory exists before performing the copy
   * This function should negate the need for $this->createStyleDirectories()
   *
   * @param string $source
   *   Full path to source theme.
   * @param string $destination
   *   Full path to destination theme.
   */
  protected function styleCopy($source, $destination) {
    if (file_exists($source)) {
      $destinationRoot = $this->build['destination_path'] . '/';
      // First, strip out the core theme path from the destination file so that
      // we are left with only the relative path to the file in the theme.
      $absoluteDestinationDir = pathinfo($destination, PATHINFO_DIRNAME);
      $relativeDestination = str_replace($destinationRoot, '', $absoluteDestinationDir);

      $subDirectories = explode('/', $relativeDestination);

      foreach ($subDirectories as $directory) {
        if (!is_dir($destinationRoot . $directory)) {
          $this->fileHandler->mkdir($destinationRoot . $directory);
        }
        // Add the newly created (or previously existing) directory to the
        // $destinationRoot so that the next loop in the foreach checks the
        // right place.
        $destinationRoot .= $directory . '/';
      }
      // Now we should be sure that the full path exists in order to copy file.
      copy($source, $destination);
    }
  }

  /**
   * Function to wipe any copied libraries for a subtheme.
   *
   * {@inheritdoc}
   */
  protected function destroyLibraries() {
    // Clear out all css files.
    $this->directoryPurgeFileType($this->build['destination_path'] . '/js', 'js', '/^(\.(\.)?|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/');
    // @todo: Ensure destoryLibraries() functions properly with kit/clone.
    // Clear out all css files.
    $this->directoryPurgeFileType($this->build['destination_path'] . '/style/css', 'css', '/^(\.(\.)?|CVS|\.sass-cache|.*layout.*.css|\.svn|\.git|\.DS_Store)$/');
    // Clear out all scss files.
    $this->directoryPurgeFileType($this->build['destination_path'] . '/style/scss', 'scss', '/^(\.(\.)?|CVS|\.sass-cache|_omega-style-vars.scss|.*layout.*.scss|\.svn|\.git|\.DS_Store)$/');
    // Clear out the libraries.yml file.
    $library_file = $this->build['destination_path'] . '/' . $this->build['machine'] . '.libraries.yml';
    if (file_exists($library_file)) {
      unlink($library_file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createLibrary() {
    // Array of source => destination mappings to create a new Library.
    $libraryFiles = [
      $this->getKitPath() . '/style/css/omega_subtheme.css' => $this->build['destination_path'] . '/style/css/' . $this->build['machine'] . '.css',
      $this->getKitPath() . '/style/scss/omega_subtheme.scss' => $this->build['destination_path'] . '/style/scss/' . $this->build['machine'] . '.scss',
      $this->getKitPath() . '/js/omega_subtheme.js' => $this->build['destination_path'] . '/js/' . $this->build['machine'] . '.js',
      $this->getKitPath() . '/omega_subtheme.libraries.yml' => $this->build['destination_path'] . '/' . $this->build['machine'] . '.libraries.yml',
    ];

    // Cycle the array and create files as needed.
    foreach ($libraryFiles as $source => $destination) {
      $file = $this->fileCopy($source, $destination);
      if ($file) {
        // Make it usable by injecting the correct theme name for the functions.
        $this->fileStrReplace($destination, 'omega_subtheme', $this->build['machine']);
      }
      else {
        drupal_set_message(t('Error copying file: <strong><small>@destination</small></strong>', [
          '@destination' => $destination,
        ]), 'error');
      }
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
    // Destination path for newly created theme.
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

      case "subtheme":
        // Path to .kit directory we will create subtheme from.
        return $this->kitData['kit_directory'];
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

  /**
   * Function to purge a specific type of file from a directory.
   *
   * @param string $directory
   *   Directory to purge from.
   * @param string $filetype
   *   Type of files to purge from $directory.
   * @param string $ignore
   *   Regular expression of files to ignore.
   */
  public function directoryPurgeFileType($directory, $filetype, $ignore = '/^(\.(\.)?|.*omega_subtheme.*|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
    $dir = opendir($directory);
    while ($file = readdir($dir)) {
      if (!preg_match($ignore, $file)) {
        // Directory found, call function on this directory to scan deeper.
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
   * Copy a set of files with specific extension from one directory to another.
   *
   * @param string $source
   *   Path to source directory.
   * @param string $target
   *   Path to target directory.
   * @param string $filetype
   *   File extension to copy.
   * @param string $ignore
   *   Regular expression of files to ignore.
   */
  public function directoryCopyFileType($source, $target, $filetype, $ignore = '/^(\.(\.)?|.*omega_subtheme.*|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
    $dir = opendir($source);
    while ($file = readdir($dir)) {
      if (!preg_match($ignore, $file)) {
        // Directory found.
        if (is_dir($source . '/' . $file)) {
          // Ensure target directory path exists.
          if (!is_dir($target . '/' . $file)) {
            // Create target directory.
            $this->fileHandler->mkdir($target . '/' . $file);
          }
          // Call directoryCopyFileType() function again on this directory
          // to scan deeper.
          $this->directoryCopyFileType($source . '/' . $file, $target . '/' . $file, $filetype, $ignore);
        }
        else {
          // Get the file extension.
          $extension = substr(strrchr($file, "."), 1);
          // See if it matches the types of files we want to create.
          if ($extension == $filetype) {
            // Copy file.
            $this->fileCopy($source . '/' . $file, $target . '/' . $file);
          }
        }
      }
    }
  }

  /**
   * Create a clone of an Omega theme.
   *
   * {@inheritdoc}
   */
  public function omegaThemeClone($source, $target, $ignore = '/^(\.(\.)?|CVS|\.node-modules|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
    $dir = opendir($source);
    $this->fileHandler->mkdir($target);
    while ($file = readdir($dir)) {
      if (!preg_match($ignore, $file) && !in_array($file, $this->omegaExcludedThemeFiles)) {
        // Directory found, call function again to scan deeper.
        if (is_dir($source . '/' . $file)) {
          $this->omegaThemeClone($source . '/' . $file, $target . '/' . $file, $ignore);
        }
        else {
          // Copy the file to new location.
          $fileLocation = $target . '/' . $file;
          copy($source . '/' . $file, $fileLocation);

          // If the file name itself has the machine name of the original theme
          // let's rename it to the new machine name.
          if (strpos($file, $this->build['replace']['system_name']) !== FALSE) {
            $fileLocation = $target . '/' . str_replace($this->build['replace']['system_name'], $this->build['machine'], $file);
            rename($target . '/' . $file, $fileLocation);
          }

          // Open any files and search for things to replace.
          $this->fileStrReplace($fileLocation, $this->build['replace']['system_name'], $this->build['machine']);
        }
      }
    }
    closedir($dir);
    // At the end of this function call, we have a new theme built,
    // files renamed and search/replaced we should be ready to reset
    // system to recognize the new theme for new operations
    // $this->refreshThemeData(); // Implement.
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
  public function updateInfoFile(array $info) {
    // Update the .info.yml.
    $new_info = $this->yamlEncode($info);
    $infoUpdated = file_put_contents($this->getInfoFile(), $new_info);
    if (!$infoUpdated) {
      drupal_set_message(t('Could not save @machine.info.yml', [
        '@machine' => $this->build['machine'],
      ]), "error");
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
        // Copy the parent theme to new theme's location.
        $this->omegaThemeClone($this->kitData['clone_directory'], $this->build['destination_path']);
        break;

      case "subtheme":
        // Copy the parent theme to new theme's location.
        $this->omegaThemeClone($this->kitData['kit_directory'], $this->build['destination_path']);
        break;
    }
  }

  /**
   * Ensure all theme directories defined in $this->themeDirectories.
   */
  public function themeDirectoryPrepare() {
    foreach ($this->themeDirectories as $path) {
      $dir = DRUPAL_ROOT . '/' . $path;
      if (!is_dir($dir)) {
        drupal_set_message(t('Directory: <strong>@path</strong> does not exist. <strong>Creating it now...</strong>', [
          '@path' => $path,
        ]));
        $this->fileHandler->mkdir($dir, 0777);
      }
    }
  }

  /**
   * Wrapper for copy().
   *
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
        drupal_set_message(t('File copy failed on function: <strong><small>copy(@source, @destination);</small></strong>', [
          '@source' => $source,
          '@destination' => $destination,
        ]), 'error');
        return FALSE;
      }
    }
    drupal_set_message(t('Source file not found: <strong><small>@source</small></strong>', [
      '@source' => $source,
    ]), 'error');
    return FALSE;
  }

  /**
   * Wrapper for unlink().
   *
   * {@inheritdoc}
   */
  public function fileRemove($file) {
    if (file_exists($file)) {
      unlink($file);
    }
  }

  /**
   * String replacement in a file.
   *
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
   * Turn PHP array into YAML.
   *
   * {@inheritdoc}
   */
  public function yamlEncode(array $php) {
    $yaml = Yaml::encode($php);
    return $yaml;
  }

  /**
   * Turn YAML into PHP array.
   *
   * {@inheritdoc}
   */
  public function yamlDecode($yaml) {
    $php = Yaml::decode($yaml);
    return $php;
  }

  /**
   * Function to attempt to refresh theme listing data.
   *
   * {@inheritdoc}
   */
  public function refreshThemeData() {

  }

  /**
   * {@inheritdoc}
   */
  protected function generateTemplateFiles() {
    // @todo: This needs to change with update for subtheme kit/clones.
    if ($this->build['theme_theme_templates']) {
      /*
       * Now that we've switched to the .kit setup for initial subtheme
       * creation, we need to determine the templates to copy. Initially,
       * that would be by copying ALL the templates from Omega.
       *
       * However, if we're creating a subtheme of a subtheme, then we will need
       * to assume different logic:
       * Scan all parent themes and find most recent parent with overrides,
       * or Omega as default.
       */
      $this->directoryCopyFileType($this->kitData['clone_directory'] . '/templates', $this->build['destination_path'] . '/templates', 'twig');
    }
    else {
      // We should remove all the template files in the template folder
      // since we want this to be a subtheme and have template inheritance
      // from the parent theme.
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function generateThemeSettingsFile() {
    // Provide an empty (with samples) theme-settings.php.
    $destination = $this->build['destination_path'] . '/theme-settings.php';
    if ($this->build['theme_settings_php'] && !file_exists($destination)) {
      $source = $this->getKitPath() . '/theme-settings.php';
      // Copy the theme settings file.
      $themeSettingsFile = $this->fileCopy($source, $destination);
      if ($themeSettingsFile) {
        // Make it usable by injecting the correct theme name for the functions.
        $this->fileStrReplace($destination, $this->build['replace']['system_name'], $this->build['machine']);
      }
    }
    elseif (!$this->build['theme_settings_php'] && file_exists($destination)) {
      $this->fileRemove($destination);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function generateThemeFile() {
    // @todo: generateThemeFile() should ONLY be used for clones to replace any current .theme file. Otherwise, the .kit provides it by default.
    $source = $this->getKitPath() . '/omega_subtheme.theme';
    $destination = $this->build['destination_path'] . '/' . $this->build['machine'] . '.theme';
    // Copy the theme settings file.
    $themeFile = $this->fileCopy($source, $destination);
    if ($themeFile) {
      // Make it usable by injecting the correct theme name for the functions.
      $this->fileStrReplace($destination, 'omega_subtheme', $this->build['machine']);
    }
  }

  /**
   * Generate SCSS support for a new theme.
   *
   * We have selected to allow this theme to customize the SCSS variables.
   * This means that for all the available CSS files with corresponding SCSS,
   * we should create a copy of those files to this new theme, and implement
   * them as a library-override in the .info file.
   *
   * This will also create the _omega-style-vars.scss file that will be
   * configurable via the interface under SCSS Variables.
   *
   * Anytime in the future when saving theme settings, assuming the
   * "Compile SCSS Directly" under General Options is enabled, the individual
   * SCSS files will be recompiled after the _omega-style-vars.scss is saved.
   *
   * If you are handling the SCSS compiling yourself, then only the variable
   * file would be changed, and your compass watch would handle any of the
   * related files that need to be rewritten.
   */
  protected function generateScssSupport() {
    if ($this->build['theme_scss_support']) {
      // Open info file.
      $info = $this->retrieveInfoFile();
      // Let's find all the CSS files available to us from our parent themes.
      $library_overrides = $this->processStyleOverrides($this->build['parent']);
      // Assign the overrides to the .info array.
      // @todo - what happens if a subtheme manually created overrides? Need some merge here.
      $info['libraries-override'] = $library_overrides;
      // Save .info file.
      $this->updateInfoFile($info);
    }
  }

  /**
   * Create a Drupal library stub.
   *
   * {@inheritdoc}
   */
  protected function generateBlankLibrary() {
    if ($this->build['blank_library']) {
      // Open info file.
      $info = $this->retrieveInfoFile();
      // We will build an empty library include if requested during build.
      $this->createLibrary();
      // Add the empty library to the .info.yml array.
      $info['libraries'] = [
        $this->build['machine'] . '/' . $this->build['machine'],
      ];
      // Save .info file.
      $this->updateInfoFile($info);
    }
  }

  /**
   * Do it.
   */
  protected function generateSubthemeScss() {
    // Generate the SCSS from the layout data.
    // _omega_compile_layout($layout, $layout_id, $theme); // Unused?
  }

  /**
   * {@inheritdoc}
   *
   * @todo The default config.rb needs to be adjusted, and setup to include the possibility for multiple additional_import_paths for both Omega and ALl parent themes?
   */
  protected function generateConfigrb() {
    // Destination FOR the new config.rb file.
    $destination = $this->build['destination_path'] . '/config.rb';
    if ($this->build['theme_configrb_create']) {
      // Source of the config.rb file.
      $source = $this->getKitPath() . '/config.rb';

      // Copy the config.rb.
      $configRbFile = $this->fileCopy($source, $destination);
      if ($configRbFile) {
        // Make it usable by injecting the correct theme name for the functions.
        $omega_scss_path = DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega') . '/style/scss';
        $rel_path_to_omega_scss = $this->findRelativePath($this->build['destination_path'], $omega_scss_path);
        $this->fileStrReplace($destination, 'OMEGA_SCSS_PATH', $rel_path_to_omega_scss);
      }
      else {
        drupal_set_message(t('Error saving config.rb.'), 'error');
      }
    }
    else {
      $this->fileRemove($destination);
    }
  }

  /**
   * Generate a Gemfile.
   *
   * {@inheritdoc}
   *
   * @todo: generateConfigrb(), generateGemfile() and generateGruntfile() could/should be combined to a single method.
   */
  protected function generateGemfile() {
    $destination = $this->build['destination_path'] . '/Gemfile';
    if ($this->build['theme_gemfile_create']) {
      $source = $this->getKitPath() . '/Gemfile';
      // Copy the Gemfile.
      $gemfile = $this->fileCopy($source, $destination);
      if (!$gemfile) {
        drupal_set_message(t('Error saving Gemfile.'), 'error');
      }
    }
    else {
      // Remove the default Gemfile if it exists.
      $this->fileRemove($destination);
      // Update the destination var to Gemfile.lock.
      $destination = $this->build['destination_path'] . '/Gemfile.lock';
      // Remove the default Gemfile.lock if it exists.
      $this->fileRemove($destination);
    }
  }

  /**
   * Generate a Gruntfile.js.
   *
   * {@inheritdoc}
   *
   * @todo: generateConfigrb(), generateGemfile() and generateGruntfile() could/should be combined to a single method.
   */
  protected function generateGruntfile() {
    $destination = $this->build['destination_path'] . '/Gruntfile.js';
    if ($this->build['theme_gruntfile_create']) {
      $source = $this->getKitPath() . '/Gruntfile.js';
      // Copy the Gruntfile.
      $gruntfile = $this->fileCopy($source, $destination);
      if (!$gruntfile) {
        drupal_set_message(t('Error saving Gruntfile.'), 'error');
      }
    }
    else {
      // Remove the default Gruntfile.js if it exists.
      $this->fileRemove($destination);
    }
  }

  /**
   * Generate gulpfile.js.
   *
   * {@inheritdoc}
   *
   * @todo: generateConfigrb(), generateGemfile() and generateGruntfile() could/should be combined to a single method.
   */
  protected function generateGulpSupport() {
    $destination = $this->build['destination_path'] . '/gulpfile.js';
    if ($this->build['theme_gulpfile_create']) {
      $source = $this->getKitPath() . '/gulpfile.js';
      // Copy the gulpfile.js.
      $gulpfile = $this->fileCopy($source, $destination);
      if (!$gulpfile) {
        drupal_set_message(t('Error saving gulpfile.js.'), 'error');
      }
    }
    else {
      // Remove the default gulpfile.js if it exists.
      $this->fileRemove($destination);
    }

    $destination = $this->build['destination_path'] . '/gulpconfig.js';
    if ($this->build['theme_gulpfile_create']) {
      $source = $this->getKitPath() . '/gulpconfig.js';
      // Copy the gulpconfig.js.
      $gulpfile = $this->fileCopy($source, $destination);
      if (!$gulpfile) {
        drupal_set_message(t('Error saving gulpconfig.js.'), 'error');
      }
    }
    else {
      // Remove the default Gulpfile if it exists.
      $this->fileRemove($destination);
    }
  }

  /**
   * Find the relative file system path between two file system paths.
   *
   * @param string $frompath
   *   Path to start from.
   * @param string $topath
   *   Path we want to end up in.
   *
   * @return string
   *   Path leading from $frompath to $topath
   */
  public function findRelativePath($frompath, $topath) {
    $from = explode(DIRECTORY_SEPARATOR, $frompath);
    $to = explode(DIRECTORY_SEPARATOR, $topath);
    $relpath = '';

    $i = 0;
    // Find how far the path is the same.
    while (isset($from[$i]) && isset($to[$i])) {
      if ($from[$i] != $to[$i]) {
        break;
      }
      $i++;
    }
    $j = count($from) - 1;
    // Add '..' until the path is the same.
    while ($i <= $j) {
      if (!empty($from[$j])) {
        $relpath .= '..' . DIRECTORY_SEPARATOR;
      }
      $j--;
    }
    // Go to folder from where it starts differing.
    while (isset($to[$i])) {
      if (!empty($to[$i])) {
        $relpath .= $to[$i] . DIRECTORY_SEPARATOR;
      }
      $i++;
    }

    // Strip last separator.
    return substr($relpath, 0, -1);
  }

}
