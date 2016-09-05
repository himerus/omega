[logo]: readme/assets/logo-24.png "Omega Five Logo"

# ![alt text][logo] Omega Five 

## Description
The `Omega Five` theme is a powerful and flexible base theme for Drupal 8. 
If you are searching for a base theme for your next Drupal project, look no further. 
Omega has always been built for the average user to have control over the visual appearance and layout though an intuitive interface. 

## Features
* **Subtheme Generation** - Create _subthemes_ and _clone_ themes on the fly that inherit or override various theme features of the parent.
* **Responsive Layout Interface** - Fully Responsive layout(s) and a _highly configurable interface_ presentation of that layout in each responsive breakpoint to make editing your layouts a breeze.
* **Layout Inheritance** - Allows you can create a base theme for your project that controls layout and base styles. This allows subthemes to inherit ALL layout configurations from the parent.
* **Layout Override** - Allows a custom subtheme to override all layout settings, and provide a unique layout unlike its parent theme.
* **Layout Selection** - Ability for single or multiple layout configurations. You can then select which layout to use on the homepage, individual node types, and for taxonomy vocabularies.
* **Advanced Theme Developer Friendly** - Features for advanced themers like adding a config.rb or Gemfile to generated subthemes.
* **Starterkit Samples** - Multiple starterkits to choose from when creating a starting point for your subtheme.
* **SCSS for Everyone** - Advanced themers have the ability to control your SCSS <small>(via Compass, etc.)</small>, or allow Omega to handle it for you for those just wanting to keep it simple.
* **Custom SCSS Colors & Variables** - A custom interface <small>(when enabled)</small> for colors, fonts and other styles that allows you to manipulate SCSS variables on the fly used to represent your look & feel.   

## Online Resources
* [Omega Project Page](http://drupal.org/project/omega)
* [Omega Issue Queue](http://drupal.org/project/issues/omega)
* [Omega Usage Statistics](http://drupal.org/project/usage/omega)
* Maintainer(s):
  * Jake Strawn
	* [Jake on Drupal.org](https://www.drupal.org/u/himerus)
	* [Jake on Twitter](http://twitter.com/himerus)

## Installing Omega

### Downloading Omega Five via Composer
You can download Omega using [Composer](https://getcomposer.org/) by running the command `composer require drupal/omega`. 
This command should be ran from the appropriate root of your project.
This will usually be the root Drupal directory.
However, if you are using a custom Composer setup that calls for drupal/drupal as a requirement, you'd run the command from the appropriate root of your project.

### Downloading Omega via Tarball
From the [Omega Project Page](http://drupal.org/project/omega), download the appropriate `8.x-5.*` tarball and extract in into your `/themes` folder, or optionally `/themes/contrib` 

___

Once you have Omega downloaded properly via one of the above methods, visit `admin/appearance` while logged in as an Administrator. 
Under the `Uninstalled Themes` section, you can now find Omega, and select either `Install` or `Install and set as default`.

## Creating your Omega Sub Theme

### Subtheme Generator <small>(Easy Way)</small>
Omega Five includes a powerful subtheme generator. 
It will allow you to make a clone of any Omega subtheme, 
or will allow you to create a subtheme based upon any subtheme.

If you have followed the steps under Installing Omega (above), when you visit `/admin/appearance`, find Omega and click the link for `Settings`. 
If you have the **Omega Tools** module enabled, this link will instead read `Create Subtheme`.

Once on the Subtheme Generator page, you can fill out the following fields:

#### Subtheme Information

##### **Theme Name**

##### **Description**

##### **Version**

#### Subtheme Options

##### Subtheme Type

##### **Clone**
<small>Creating a clone of a sub-theme will create a direct clone with minimal options for customization. The process will clone the entire directory, and search and replace machine names where appropriate to create a newly named theme identical in every way to the previous theme. This provides great potential for quick testing of a theme patch on your installation without risking any adverse effects on the primary theme.</small>

##### **Subtheme** 
<small>Creating a sub-theme will allow you to create a highly customized new theme based on another theme. The options available here will allow you to customize items like layout inheritance, template overrides, SCSS support and more. Each option includes a detailed description that should clarify exactly what will happen when selecting/deselecting an option.</small>




### Manually <small>(Hard Way)</small>
As a matter of last resort, you can create a subtheme manually for Omega Five. 
Attempting to create a subtheme manually is likely to result in an error or oversight, 
leaving your new theme unusable without major debugging. 

It is much easier to create a subtheme through the interface. 
But, should you choose to try the manual method, here you go:

* [./readme/creating-subthemes--manually.md](./readme/creating-subthemes--manually.md)

