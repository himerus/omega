# `js` Directory
The `js` directory is intended to contain any project specific JavaScript/jQuery/etc.

## JavaScript Resources
* [JavaScript API in Drupal 8](https://www.drupal.org/node/2269515)
* [JavaScript Coding Standards](https://www.drupal.org/node/172169)
* [jQuery Documentation](http://learn.jquery.com/)
* [Mozilla JavaScript Reference](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference)


### Drupal.behaviors

When using jQuery it is standard for a large majority of situations to wrap almost all code inside the `$(document).ready()` function, like this:

```
$(document).ready(function () {
  // Do some fancy stuff.
});
```

This ensures that our code will only run after the DOM has loaded and all elements are available. 
However with Drupal there is an alternate better method; using the functionality of `Drupal.behaviors` and `once()`. 
If used properly this will ensure that your code runs both on normal page loads and when data is loaded by AJAX (or BigPipe!). 
The Drupal.behaviors object is itself a property of the Drupal object, and when we want our module/theme to add new jQuery behaviors, the best method is to simply extend this object.

A basic example of `Drupal.behaviors`:
```
Drupal.behaviors.myBehavior = {
  attach: function (context, settings) {
    // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
    $(context).find('input.myCustomBehavior').once('myCustomBehavior').addClass('processed');

    // Using once() with more complexity.
    $(context).find('input.myCustom').once('mySecondBehavior').each(function () {
      if ($(this).visible()) {
          $(this).css('background', 'green');
      }
      else {
        $(this).css('background', 'yellow').show();
      }
    });
  }
};
```

Since Drupal uses [jQuery.noConflict()](http://learn.jquery.com/using-jquery-core/avoid-conflicts-other-libraries/) and only loads JavaScript files when required, to use jQuery and the $ shortcode for jQuery you must include jQuery and Drupal as dependencies in the [library definition](https://www.drupal.org/developing/api/8/assets#library) in your MODULE.libraries.yml and add a wrapper around your function. So the whole JavaScript file would look something like this:

Sample from `js/omega_subtheme.js`
```
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.myCustomSubthemeBehavior = {
          attach: function (context, settings) {
              $(context).find('input.css-class').once('myCustomSubthemeBehavior').each(function () {
                  // Apply the myCustomSubthemeBehavior effect to the elements only once.
              });
          }
      };
})(jQuery, Drupal, drupalSettings);
```

Sample from `omega_subtheme.libraries.yml`

```
dependencies:
  - core/jquery
  - core/drupal
  - core/drupalSettings
```
