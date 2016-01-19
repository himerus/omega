# Omega Five

### Description
The Omega Five theme is the most powerful and flexible base theme available for Drupal 8. Omega includes HTML5, CSS3 responsive layouts, customizable grids based on Omega.gs, advanced preprocess functionality, and a vastly enhanced performance profile from Omega 3.x. In Omega Five all the processing of layout data/vars is handled by the backend, and SCSS/CSS is generated when theme settings/layouts have been updated. This means all that is loaded on the front end is valid CSS that handles the layout.


### Resources
* Project Page: [drupal.org/project/omega](http://drupal.org/project/omega)
* Issue Queue: [drupal.org/project/issues/omega](http://drupal.org/project/issues/omega)
* Usage Stats: [drupal.org/project/usage/omega](http://drupal.org/project/usage/omega)
* Maintainer(s):
  * Jake Strawn
	* [drupal.org/user/159141](http://drupal.org/user/159141)
	* [twitter.com/himerus](http://twitter.com/himerus)

### Creating your Omega Sub Theme (Manually)

* Copy the appropriate starterkit from omega/starterkits
  * move copy to /themes
* Rename the folder
  * Rename the copied folder to **YOUR_THEME**
* Renaming the appropriate files
  -- Any instance below of *STARTERKIT* below could be *omega_starterkit*, *omega_simple_starterkit*, etc.
  -- Any instance of **YOUR_THEME** should be the new machine name of your theme
  * Rename the *STARTERKIT*.info.yml file to **YOUR_THEME**.info.yml
  * Rename the *STARTERKIT*.breakpoints.yml to **YOUR_THEME**.breakpoints.yml
  * Rename the *STARTERKIT*.libraries.yml to **YOUR_THEME**.libraries.yml
  * Rename the *STARTERKIT*.theme to **YOUR_THEME**.theme
  * Rename the *STARTERKIT*.schema.yml to **YOUR_THEME**.schema.yml in the config/schema folder
  * Rename the *STARTERKIT*.settings.yml to **YOUR_THEME**.settings.yml in the config/install folder
  * Rename the *STARTERKIT*.region_groups.yml to **YOUR_THEME**.region_groups.yml in the config/install folder
  * Rename the *STARTERKIT*.layouts.yml to **YOUR_THEME**.layouts.yml in the config/install folder
  * Rename ALL *STARTERKIT*.layout.LAYOUT_ID.yml to **YOUR_THEME**.layout.LAYOUT_ID.yml in the config/install folder
  * Rename ALL *STARTERKIT*.layout.LAYOUT_ID.generated.yml to **YOUR_THEME**.layout.LAYOUT_ID.generated.yml in the config/install folder
  * Rename the *STARTERKIT*.js to **YOUR_THEME**.js in the js folder
  * Rename the *STARTERKIT*.css to YOURTHEME.css in the style/css folder
* Edit **YOUR_THEME**.info.yml
  * Change the following lines to suit your needs
    * name = My Custom Theme
    * description = My own custom Omega Five subtheme
    * libraries 
      * Rename STARTERKIT/STARTERKIT to **YOUR_THEME**/**YOUR_THEME**
* Edit **YOUR_THEME**.libraries.yml
  * Change the following
    * Replace all instances of STARTERKIT with **YOUR_THEME** (there should be 3)
* More details to follow
* More details to follow
* More details to follow
* More details to follow
* Turn on your subtheme
  * visit /admin/appearance
  * Click “Install and set default” on the appropriate subtheme you’ve created.
  * Profit.