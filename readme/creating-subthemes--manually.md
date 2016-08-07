<small>Back to [`README.md`](../README.md)</small>

## Creating an Omega subtheme Manually

> Any instance of `starterkit` could be `omega_starterkit`, `omega_simple_starterkit`, etc.

> Any instance of `your_theme` should be the new machine name of your theme

* Copy the appropriate starterkit from `omega/starterkits`
  * Move copy to `/themes`, `/themes/custom`, or `/profiles/YOUR_PROFILE/themes` if you are building a custom install profile.
* Rename the folder
  * Rename the copied folder to `your_theme`
* Renaming the appropriate files
  * Rename the `starterkit.info.yml` file to `your_theme.info.yml`
  * Rename the `starterkit.breakpoints.yml` to `your_theme.breakpoints.yml`
  * Rename the `starterkit.libraries.yml` to `your_theme.libraries.yml`
  * Rename the `starterkit.theme` to `your_theme.theme`
  * Rename the `starterkit.schema.yml` to `your_theme.schema.yml` in the `config/schema` folder
  * Rename the `starterkit.settings.yml` to `your_theme.settings.yml` in the `config/install` folder
  * Rename the `starterkit.region_groups.yml` to `your_theme.region_groups.yml` in the `config/install` folder
  * Rename the `starterkit.layouts.yml` to `your_theme.layouts.yml` in the `config/install` folder
  * Rename ALL `starterkit.layout.LAYOUT_ID.yml` to `your_theme.layout.LAYOUT_ID.yml` in the `config/install` folder
  * Rename ALL `starterkit.layout.LAYOUT_ID.generated.yml` to `your_theme.layout.LAYOUT_ID.generated.yml` in the `config/install` folder
  * Rename the `starterkit.js` to `your_theme.js` in the `js` folder
  * Rename the `starterkit.css` to `your_theme.css` in the `style/css` folder
* Edit `your_theme.info.yml`
  * Change the following lines to suit your needs
    * `name` = My Custom Theme
    * `description` = My own custom Omega Five subtheme
    * `libraries` 
      * Rename `starterkit/starterkit` to `your_theme/your_theme`
* Edit `your_theme.libraries.yml`
  * Replace all instances of `starterkit` with `your_theme` (there should be 3)
* More details to follow
* More details to follow
* More details to follow
* More details to follow
* Enable your subtheme
  * Visit `/admin/appearance`
  * Click `Install` or `Install and set as default` on the appropriate subtheme you’ve created.
  * **Profit.**
