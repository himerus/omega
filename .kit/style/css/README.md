# `style/css` Directory
The `style/css` directory contains all compiled CSS for your theme. 
This is where the actual styles live that are loaded when this theme is used to render a Drupal page.
Note the relevant portion of `OMEGA_SUBTHEME.libraries.yml`:

```
OMEGA_SUBTHEME:
  js:
    js/OMEGA_SUBTHEME.js: {}
  css:
    theme:
      style/css/OMEGA_SUBTHEME.css: {}
```
