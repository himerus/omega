(function ($, Drupal, drupalSettings) {

  "use strict";

  drupalSettings.omegaAdmin = {
    // autoUpdate: true,
  };
  
  
  
  Drupal.behaviors.updateLayoutForm = {
    attach: function (context) {
      // insert the region title because the formAPI doesn't let you have a title for a container
      
      $('.region-settings').each(function() {
        //var regionTitle = $(this).attr('data-omega-region-title');
        //$(this).prepend('<h4>' + regionTitle + '</h4');
      });
      
      // adjust the push value
      $('select.push-controller').on('change', function(){
        var push = $(this).val();
        console.log("-data-omega-push updated to: " + push);
        $(this).parents('.region-settings').attr('data-omega-push', push);
      });
      
      // adjust the prefix value
      $('select.prefix-controller').on('change', function(){
        var prefix = $(this).val();
        console.log("-data-omega-prefix updated to: " + prefix);
        $(this).parents('.region-settings').attr('data-omega-prefix', prefix);
      });
      
      
      // adjust the width value
      $('select.width-controller').on('change', function(){
        var width = $(this).val();
        console.log("-data-omega-width updated to: " + width);
        $(this).parents('.region-settings').attr('data-omega-width', width);
      });      
      
      // adjust the suffix value
      $('select.suffix-controller').on('change', function(){
        var suffix = $(this).val();
        console.log("-data-omega-suffix updated to: " + suffix);
        $(this).parents('.region-settings').attr('data-omega-suffix', suffix);
      });
      
      // adjust the pull value
      $('select.pull-controller').on('change', function(){
        var pull = $(this).val();
        console.log("-data-omega-pull updated to: " + pull);
        $(this).parents('.region-settings').attr('data-omega-pull', pull);
      });
    }
  };
  
})(jQuery, Drupal, drupalSettings);
