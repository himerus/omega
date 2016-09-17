# `OMEGA_SUBTHEME` Theme
><small>Welcome to `OMEGA_SUBTHEME`, your custom [**Omega Five**](https://drupal.org/project/omega) subtheme! 
This `README.md` file is here to help guide you through learning, understanding and customizing your Omega subtheme.</small> 

## Omega Resources
Your Omega Five subtheme is a highly configurable theme. 
These online resources should help you with any issues you run across.
* [Omega Project Page](https://drupal.org/project/omega)
* [Omega Issue Queue](https://drupal.org/project/issues/omega)
* [Omega on Github](https://github.com/himerus/omega)
* `README.md` in the root `omega` theme directory.

#### Other Omega Projects
* [Omega Installation Profile](https://drupal.org/project/omega_profile) - Drupal 8 Installation Profile
* [Omega.gs](https://github.com/himerus/omegags) - Omega Grid System
* [himerus/drupal](https://github.com/himerus/drupal) - Composer template for Drupal 8. 
## Inline Documentation
> You'll find that the `OMEGA_SUBTHEME` theme has a vast coverage of inline documentation. 

### `README.md` files 
It really _behooves_ you to read, at least once, any `README.md` you come across in your theme.
Many of these files could become, and likely should evolve from your own edits, into a resource you use frequently. 
Where appropriate and possible, the documentation is updated with your specific theme name and information.

> At the very minimum, each directory of your subtheme should contain a `README.md` file which should include:
* A brief overview of the intention of a directory 

> And can also include:
* Advanced Code Samples
* Example Usage Information
* Reference Links & Resources
* etc.

### Code Documentation
Where possible, and as a continuing effort, code for [**Omega Five**](https://drupal.org/project/omega) is well documented inline.

## Installing `OMEGA_SUBTHEME` 
Assuming the installation was not automated, on the `admin/appearance` page, scroll to the bottom for **Uninstalled themes**.
You will find links to **Install** and **Install and set as default** listed with `OMEGA_SUBTHEME`.

## Customizing Libraries in `OMEGA_SUBTHEME`
Omega provides a few options to add functionality to the libraries you define in your subtheme(s).

```
OMEGA_SUBTHEME:
  omega:
    allow_clone_for_subtheme: true
    allow_enable_disable: true
    title: 'Default library for the OMEGA_SUBTHEME theme'
    description: 'Default Omega subtheme library description. Please provide a more meaningful description (and title) for this library by editing the OMEGA_SUBTHEME.libraries.yml file.'
    scss:
      style/css/OMEGA_SUBTHEME.css: 'style/scss/OMEGA_SUBTHEME.scss'
  version: VERSION
  js:
    js/OMEGA_SUBTHEME.js: {}
  css:
    theme:
      style/css/OMEGA_SUBTHEME.css: {}
  dependencies:
    - core/modernizr
    - core/jquery
    ...
    ...

```

Above, you will see the library defined as: `OMEGA_SUBTHEME/OMEGA_SUBTHEME`. 
The `omega` section of the array is the portion we can customize for various Omega functionality.

### Available Options
* `omega`
  * `allow_clone_for_subtheme` - A boolean TRUE/FALSE that determines if the library should be cloned into a new theme. 
  * `allow_enable_disable` - A boolean TRUE/FALSE that determines if the library can be enabled/disabled through the Omega theme settings interface.
  * `title` - A descriptive title for your library.
  * `description` - A longer description for your library.
  * `scss` - An array of values that map CSS files to SCSS files for any clone/subtheme operation. This enables Omega to properly copy BOTH the relevant CSS and SCSS when cloning a library. 
