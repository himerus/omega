# `style/css` Directory
The `style/css` directory contains all compiled CSS for your theme. 
This is where the actual styles live that are loaded when this theme is used to render a Drupal page.
Note the relevant portion of `omega_subtheme.libraries.yml`:

```
omega_subtheme:
  js:
    js/omega_subtheme.js: {}
  css:
    theme:
      style/css/omega_subtheme.css: {}
```
