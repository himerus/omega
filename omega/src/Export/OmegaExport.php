<?php

/**
 * @file
 * Contains \Drupal\omega\Theme\OmegaExport
 */

namespace Drupal\omega\Export;

//use Drupal\omega\Theme\OmegaSettingsInfo;
//use Drupal\Core\Extension\ThemeHandler;
//use Symfony\Component\Yaml\Parser;

//use Drupal\Core\Extension\ThemeHandler;

use Drupal\Core\Extension\ThemeHandlerInterface;

use Drupal\Component\Serialization\Yaml;
use Symfony\Component\HttpFoundation\Response;

/**
 * OmegaExport declares methods used to build a new subtheme
 */
class OmegaExport implements OmegaExportInterface {
  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;
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
   * Constructs an export object.
   *
   * @param array $export
   *  The details of the export to build
   */
  public function __construct(ThemeHandlerInterface $theme_handler) {
    $this->themeHandler = $theme_handler;
    $this->themes = $this->themeHandler->rebuildThemeData();
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildExport($export) {
    $this->export = $export;
    
    $this->build = array(
      // GLobal Options
      // User provided friendly name of the new theme
      'name' => $this->getFriendlyName(), 
      // User provided machine name of the new theme
      'machine' => $this->getMachineName(), 
      // User provided description for the new theme
      'description' => $this->getDescription(), 
      // User provided version number for the new theme
      'version' => $this->getVersion(), 
      // Path where the new theme will be installed
      'destination_path' => $this->getBuildPath(), 
      // Path of the parent theme for the new theme
      'parent_path' => $this->getParentPath(), 
      // type of export (clone, subtheme)
      'type' => $this->getExportType(), 
      // Parent theme for the new theme
      'parent' => $this->getOptions('export_theme_base'), 
      // If the new theme should be installed by default
      'install' => $this->getOptions('export_install_auto') ? TRUE : FALSE, 
      // If the new theme should be set as default theme upon installation
      'install_default' => $this->getOptions('export_install_default') ? TRUE : FALSE, 
      // install token
      'install_token' => '',
      // Subtheme Options
      // If block positions should be exported
      'block_positions' => $this->getOptions('export_include_block_positions') ? TRUE : FALSE,
      // If a blank library should be included in the export
      'blank_library' => $this->getOptions('export_include_blank_library') ? TRUE : FALSE,
      // If a 'blank' theme-settings.php file should be included
      'theme_settings_php' => $this->getOptions('export_include_theme_settings_php') ? TRUE : FALSE,
      // If sample functions should be created in .theme or not
      'theme_theme_samples' => $this->getOptions('export_include_themefile_samples') ? TRUE : FALSE,
      // If templates should be copied over to the new theme.
      'theme_theme_templates' => $this->getOptions('export_include_templates') ? TRUE : FALSE,
    );

    return $this->build;
  }
  
  /**
   * {@inheritdoc}
   */
  public function saveExport($form_state) {
    
    $theme = array();
    
    switch ($this->build['type']) {
      case "clone":
        // copy the parent theme to new theme's location
        $this->directoryCloneCopy($this->build['parent_path'], $this->build['destination_path']);
        // Declare some important files
        $info_file = DRUPAL_ROOT . '/themes/' . $this->build['machine'] . '/' . $this->build['machine'] . '.info.yml';
        // open info file
        $info = $this->yamlDecode(file_get_contents($info_file));
        // Update the Friendly Name
        $info['name'] = $this->build['name'];
        // Update the Description
        $info['description'] = $this->build['description'];
        // Update the Version
        $info['version'] = $this->build['version'];
        // Update the Version
        $info['force_export'] = false;
        //dsm($info);
        $new_info = $this->yamlEncode($info);
        $infoUpdated = file_put_contents($info_file, $new_info);
        // Now we check to see if we've opted to install or install and set as default theme and redirect accordingly
        // @todo - make this work. :/
        if ($this->build['install_default']) {
          // we should install the theme and set it as the default theme and enable any dependencies
          //$form_state->setRedirect('system.theme_set_default', array('query' => array('theme' => $this->build['machine'],'token' => '')));
        } 
        elseif ($this->build['install']) {
          // we should just install the theme and any dependencies          
          //$form_state->setRedirect('system.theme_install', array('query' => array('theme' => $this->build['machine'],'token' => '')));
        }
      break;
      case "subtheme":
        // The first thing we are doing when creating a subtheme is STILL making a full copy
        // of the original parent theme. After we've made a copy, we make adjustments to the 
        // .info.yml file, and remove files as needed/requested in the build. So we do some 
        // extra processing, however, we get to work on a segregated copy and avoid repeated 
        // file operations against the parent theme in case anything goes haywire. This also 
        // makes sure we keep any standardized folder structures and README files intact in 
        // the appropriate locations.
        
        // copy the parent theme to new theme's location
        $this->directoryCloneCopy($this->build['parent_path'], $this->build['destination_path']);
        // Declare some important files
        $info_file = DRUPAL_ROOT . '/themes/' . $this->build['machine'] . '/' . $this->build['machine'] . '.info.yml';
        // open info file
        $info = $this->yamlDecode(file_get_contents($info_file));
        // Update the Friendly Name
        $info['name'] = $this->build['name'];
        // Update the Description
        $info['description'] = $this->build['description'];
        // Update the Version
        $info['version'] = $this->build['version'];
        // Update the Base theme
        $info['base theme'] = $this->build['parent'];
        // Update the force export value to false so we can edit this new theme regardless of what the other theme was set to
        $info['force_export'] = false;
        //dsm($info);
        
        
        
        // We will copy over and replace any .theme file since this is going to be a subtheme rather than a clone
        // In a subtheme, you wouldn't want the same logic running again.
        // This setup determines if to include default samples, or basically just make the .theme file empty
        if ($this->build['theme_theme_samples']) {
          $source = DRUPAL_ROOT . '/' .drupal_get_path('theme', 'omega') . '/subtheme-samples/OMEGA_SUBTHEME.theme';          
        }
        else {
          $source = DRUPAL_ROOT . '/' .drupal_get_path('theme', 'omega') . '/subtheme-samples/OMEGA_SUBTHEME.theme.blank';
        }
        
        $destination = $this->build['destination_path'] . '/' . $this->build['machine'] . '.theme';          
        // copy the theme settings file
        $themeFile = $this->fileCopy($source, $destination);
        if ($themeFile) {
          // make it usable by injecting the correct theme name for the functions
          $this->fileStrReplace($destination, 'OMEGA_SUBTHEME', $this->build['machine']);
        }
        
        // If a theme-settings.php file was requested, let's create a default
        // Since this file was originally copied fully from the parent theme (assuming the parent theme had one)
        // We either need to create the default theme-settings.php with basic examples, OR remove the file since
        // any theme settings from the parent theme will already be present for this new subtheme
        if ($this->build['theme_settings_php']) {
          $source = DRUPAL_ROOT . '/' .drupal_get_path('theme', 'omega') . '/subtheme-samples/OMEGA_SUBTHEME.theme-settings.php';
          $destination = $this->build['destination_path'] . '/theme-settings.php';
          
          // copy the theme settings file
          $themeSettingsFile = $this->fileCopy($source, $destination);
          if ($themeSettingsFile) {
            // make it usable by injecting the correct theme name for the functions
            $this->fileStrReplace($destination, 'OMEGA_SUBTHEME', $this->build['machine']);
          }
        }
        else {
          $destination = $this->build['destination_path'] . '/theme-settings.php';
          $this->fileRemove($destination);
        }
        
        
        // first, empty out all the styles and javascripts for the theme
        // all libraries from the parent theme will be inherited
        $this->destroyLibraries();
        
        if($this->build['blank_library']) {
          
          // then we will build an empty library/js/css include if requested during build
          $this->createLibrary();
          
          // add the empty library to the .info.yml array
          $info['libraries'] = array(
            $this->build['machine'] . '/' . $this->build['machine'],
          );
        }
        
        if ($this->build['theme_theme_templates']) {
          // leave template folder as is since we already copied it
          // we may however need to do some search and replace operations?
        }
        else {
          // we should remove all the template files in the template folder
          // since we want this to be a subtheme and have template inheritance from the parent theme
          $this->directoryPurgeFileType($this->build['destination_path'] . '/templates', 'twig');
        }
        
        // lastly, setup the info file for a final write including latest information.
        $new_info = $this->yamlEncode($info);
        $infoUpdated = file_put_contents($info_file, $new_info);
        
        
      break;
    } 
    
    // Redirect to the main appearance listing page after creating a new theme.
    // Currently this is needed, as a proper installation and redirect TO the new theme
    // attempted above has been difficult to accomplish.
    $form_state->setRedirect('system.themes_page');
  }
  
   /**
   * {@inheritdoc}
   */
  protected function destroyLibraries() {
    // clear out all css files
    $this->directoryPurgeFileType($this->build['destination_path'] . '/js', 'js', '/^(\.(\.)?|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/');
    // clear out all css files
    $this->directoryPurgeFileType($this->build['destination_path'] . '/style/css', 'css', '/^(\.(\.)?|CVS|\.sass-cache|.*layout.*.css|\.svn|\.git|\.DS_Store)$/');
    // clear out all scss files
    $this->directoryPurgeFileType($this->build['destination_path'] . '/style/scss', 'scss', '/^(\.(\.)?|CVS|\.sass-cache|.*layout.*.scss|\.svn|\.git|\.DS_Store)$/');
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
    $source = DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega') . '/subtheme-samples/OMEGA_SUBTHEME.css';
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
    $source = DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega') . '/subtheme-samples/OMEGA_SUBTHEME.scss';
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
    $source = DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega') . '/subtheme-samples/OMEGA_SUBTHEME.js';
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
    $source = DRUPAL_ROOT . '/' . drupal_get_path('theme', 'omega') . '/subtheme-samples/OMEGA_SUBTHEME.libraries.yml';
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
  public function getBuildPath() {
    return DRUPAL_ROOT . '/themes/' . $this->getMachineName();
  }
  
  /**
   * {@inheritdoc}
   */
  public function getParentPath() {
    return DRUPAL_ROOT . '/' . drupal_get_path('theme', $this->getOptions('export_theme_base'));
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
  
  
  public function directoryPurgeFileType($directory, $filetype, $ignore = '/^(\.(\.)?|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
    $dir = opendir($directory);
    while($file = readdir($dir)) {
      if (!preg_match($ignore, $file)) {
        // directory found, call function again on this directory to scan deeper
        if (is_dir($directory . '/' . $file)) {
          $this->directoryPurgeFileType($directory . '/' . $file, $filetype, $ignore);
        }
        else {
          $extension = substr(strrchr($file, "."), 1);
          //dpm($extension);
          if ($extension == $filetype) {
            //dpm('Deleting File: ' . $file);
            unlink($directory . '/' . $file);
          }
          else {
            //dpm('Ignoring delete command for: ' . $file);
          }
        }
      }
      else {
        //dpm('File ignored by $ignore: ' . $file);
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public function directoryCloneCopy($source, $target, $ignore = '/^(\.(\.)?|CVS|\.sass-cache|\.svn|\.git|\.DS_Store)$/') {
    $dir = opendir($source);
    @mkdir($target);
    while($file = readdir($dir)) {
      if (!preg_match($ignore, $file)) {
        // directory found, call function again on this directory to scan deeper
        if (is_dir($source . '/' . $file)) {
          $this->directoryCloneCopy($source . '/' . $file, $target . '/' . $file, $ignore);
        }
        else {
          // copy the file to new location
          $fileLocation = $target . '/' . $file;
          copy($source . '/' . $file, $fileLocation);
          
          // if the file name itself has the machine name of the original theme
          // let's rename it to the new machine name
          if (strpos($file, $this->build['parent']) !== FALSE) {
            $fileLocation = $target . '/' . str_replace($this->build['parent'], $this->build['machine'], $file);
            rename($target . '/' . $file, $fileLocation);
          }
          
          // open any files and search for things to replace
          $this->fileStrReplace($fileLocation, $this->build['parent'], $this->build['machine']);
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
  public function updateInfoFile($info) {
    
  }
  
  /**
   * {@inheritdoc}
   */
  public function fileCopy($source, $destination) {
    if (file_exists($source)) {
      $copy = copy($source, $destination);
      if ($copy) {
        // @debug
        //drupal_set_message(t('File successfully copied: <strong><small>' . $destination . '</small></strong>'), 'status');
        return true;
      }
      else {
        // @debug
        drupal_set_message(t('File copy failed on function: <strong><small>copy(' . $source . ', ' . $destination . ');</small></strong>'), 'error');
        return false;
      }
    }
    // @debug
    drupal_set_message(t('Source file not found: <strong><small>' . $source . '</small></strong>'), 'error');
    return false;
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
}
