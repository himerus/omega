The barebones CSS structure provided in this starterkit uses many of the ideas 
discussed in Jonathan Snook's SMACSS (http://smacss.com/) and is intended to
provide a starting point for building modular, scalable CSS using Sass and
Drupal.

Multiple Sass partials are used to help organise the styles, these are combined 
by including them in style.scss which is compiled into style.css in the css/ 
directory.

All styles are included in order of specificity, this means that  as you go 
down the document  each section builds upon and inherits sensibly from the 
previous ones. This results in less undoing of styles, less specificity 
problems and all-round better architected and lighter stylesheets.

The file structure contained in this folder looks something like this:
    
    *   style.scss
        This file shouldn't directly contain any CSS code, instead 
        it only serves to combine the CSS contained in other Sass partials 
        through @import directives. 
        
    *   _utils.scss 
        Global Sass variables and mixins should be defined here along with
        importing any Sass extentions required. These can then be accessed by 
        importing _utils.scss where required.

    *   _base.scss 
        These rules are the "Branding" of a site also describe how common HTML 
        and Drupal elements should look. Once this file is completed the site's 
        styleguide should be completely styled.
 
    *   _layout.scss
        The layout of the major regions (usually, but not necessarily Drupal 
        regions) that components will be added to.
 
    *   _components.scss
        Imports more partials that contain full components and their 
        sub-components ('modules' in SMACSS) that can be placed within the 
        layout provided by _layout.scss.
