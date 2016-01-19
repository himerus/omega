(function ($, Drupal, drupalSettings) {

  "use strict";

  drupalSettings.omegaAdmin = {
    // autoUpdate: true,
  };
  
  function hexToRGB(hex) {
    // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
    var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
    hex = hex.replace(shorthandRegex, function(m, r, g, b) {
        return r + r + g + g + b + b;
    });

    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
  }
  
  function hexFromRGB(r, g, b) {
    var hex = [
      r.toString( 16 ),
      g.toString( 16 ),
      b.toString( 16 )
    ];
    $.each( hex, function( nr, val ) {
      if ( val.length === 1 ) {
        hex[ nr ] = "0" + val;
      }
    });
    return hex.join( "" ).toUpperCase();
  }
  
  function refreshRGBSlider(elem, rgb, hex) {
    var parent = elem.closest('.form-item');
    // update the background color of the swatch to match
    parent.find( ".swatch" ).css( "background-color", "#" + hex );
    // update the rgb values as well
    parent.find( ".red" ).slider( "value", rgb.r );
    parent.find( ".green" ).slider( "value", rgb.g );
    parent.find( ".blue" ).slider( "value", rgb.b );
  }
  
  function refreshSwatch(e, ui) {
    var parent = $(e.target).closest('.form-item');
    var red = parent.find( ".red" ).slider( "value" );
    var green = parent.find( ".green" ).slider( "value" );
    var blue = parent.find( ".blue" ).slider( "value" );
    var hex = hexFromRGB( red, green, blue );
    
    // update the background color of the swatch to match
    parent.find( ".swatch" ).css( "background-color", "#" + hex );
    // update the rgb values as well
    parent.find( ".red input" ).val( red );
    parent.find( ".green input" ).val( green );
    parent.find( ".blue input" ).val( blue );
    // update the form value to match
    parent.find('input.color-slider').val(hex);
    //console.log(hexToRGB(hex));
  }
  
  Drupal.behaviors.addFontPreview = {
    attach: function(context) {
      // font styles to use in preview.
      var fontStyleValues = {
        'georgia': 'Georgia, serif',
        'times': '"Times New Roman", Times, serif',
        'palatino': '"Palatino Linotype", "Book Antiqua", Palatino, serif',
        'arial': 'Arial, Helvetica, sans-serif',
        'helvetica': '"Helvetica Neue", Helvetica, Arial, sans-serif',
        'arialBlack': '"Arial Black", Gadget, sans-serif',
        'comicSans': '"Comic Sans MS", cursive, sans-serif',
        'impact': 'Impact, Charcoal, sans-serif',
        'lucidaSans': '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
        'tahoma': 'Tahoma, Geneva, sans-serif',
        'trebuchet': '"Trebuchet MS", Helvetica, sans-serif',
        'verdana': 'Verdana, Geneva, sans-serif',
        'courier': '"Courier New", Courier, monospace',
        'lucidaConsole': '"Lucida Console", Monaco, monospace',
      };
      
      $('#edit-variables-fonts-defaultbodyfont').on('change keyup', function(){
        var fontVal = $(this).val();
        var fontFam = fontStyleValues[fontVal];
        //console.log(fontFam);
        $('.sample-font-content p').css('font-family', fontFam);
      });
      $('#edit-variables-fonts-defaultheaderfont').on('change keyup', function(){
        var fontVal = $(this).val();
        var fontFam = fontStyleValues[fontVal];
        //console.log(fontVal);
        $('.sample-font-content h2').css('font-family', fontFam);
      });
      // handle the same thing on page load for the preview
      $(window).on('ready', function() {
        var bodyFontVal = $('#edit-variables-fonts-defaultbodyfont').val();
        var bodyFontFam = fontStyleValues[bodyFontVal];
        var headerFontVal = $('#edit-variables-fonts-defaultheaderfont').val();
        var headerFontFam = fontStyleValues[headerFontVal];
        $('.sample-font-content p').css('font-family', bodyFontFam);
        $('.sample-font-content h2').css('font-family', headerFontFam);
      });
    }
  };
  
  Drupal.behaviors.addColorSliders = {
    attach: function(context) {
      
      var sliderElements = $('input.color-slider');
      sliderElements.each(function(){
        $(this)
          .closest('.form-item') // find the parent form item
          .addClass('color-slider-controller')
          .prepend('<div class="controls"><a href="#" class="reset">reset</a></div>')
          .append('<div class="color-slider clearfix"><div class="red rgb-slider"><input type="text" class="rgb" maxlength="3" /></div><div class="green rgb-slider"><input type="text" class="rgb" maxlength="3" /></div><div class="blue rgb-slider"><input type="text" class="rgb" maxlength="3" /></div><div class="swatch"></div>');
          
        //$(this).find('.red, .green, .blue');
      });
      
      
      $( ".red, .green, .blue" ).slider({
        orientation: "horizontal",
        range: "min",
        max: 255,
        value: 0,
        slide: function( event, ui ) {
          refreshSwatch(event, ui);
        },
        change: function( event, ui ) {
          refreshSwatch(event, ui);
        },
      });
      
      $('.color-slider-controller .controls .reset').click(function() {
        
        var elem = $(this).closest('.form-item').find('input.color-slider');
        var hexValue = elem.attr('data-original-color-value');
        var rgbValues = hexToRGB(hexValue);
        refreshRGBSlider(elem, rgbValues, hexValue);
        return false;
      });
      
      // listen for changed to the RGB form fields to adjust the slider
      $('.red input, .green input, .blue input').on('change', function(){
        var relatedSlider = $(this).closest('.rgb-slider');
        var relatedValue = $(this).val();
        relatedSlider.slider( "value", relatedValue);
      });
      
      // listen for changes to the HEX value
      $('input.color-slider').on('change', function(){
        var elem = $(this);
        var hexValue = elem.val();
        var rgbValues = hexToRGB(hexValue);
        refreshRGBSlider(elem, rgbValues, hexValue);
      });
      
      $(window).on('load', function(){
        $('input.color-slider').each(function(){
          var elem = $(this);
          console.log(elem);
          var hexValue = elem.val();
          var rgbValues = hexToRGB(hexValue);
          refreshRGBSlider(elem, rgbValues, hexValue);  
        });
        
        
        
      });
    }
  };
  
  
  
  
  Drupal.behaviors.watchMaxWidthValues = {
    attach: function(context) {
      
      $('input.row-max-width').on('change', function(){
        var newVal = $(this).val();
        var newType = $(this).closest('.details-wrapper').find('.row-max-width-type');
        var percentBox = newType.find('input[value="percent"]');
        var pixelBox = newType.find('input[value="pixel"]');
        // assume it is a pixel value and change the radio accordingly        
        if (newVal > 100) {
          pixelBox.prop("checked", true);
          percentBox.prop("checked", false);
        }
        // assume it is a percent value and change the radio accordingly        
        else {
          percentBox.prop("checked", true);
          pixelBox.prop("checked", false);
        }
      });
      
    }
  };
  
  Drupal.behaviors.addZindexButtons = {
    attach: function(context) {
      
      $('.region-settings > .details-wrapper').each(function(){
        $(this).prepend('<div class="region-controls clearfix"><a href="#" title="Send to Back" class="send-to-back"></a><a href="#" title="Send to Front" class="send-to-front"></a></div>');
      }); 
      
      
      $('.send-to-back').on('click', function(){
        var element = $(this).closest('.region-settings');
        element.css('z-index', 0);
        return false;
      });
      $('.send-to-front').on('click', function(){
        var element = $(this).closest('.region-settings');
        element.css('z-index', 1000);
        return false;
      });
    }
  };
  
  Drupal.behaviors.addToggleStyles = {
    attach: function(context) {
      $('a.toggle-styles-on').on('click', function(){
        var element = $(this).parents('#edit-styles');
        element.find(':checkbox:not(:disabled)').prop('checked', true);
        return false;
      });
      $('a.toggle-styles-off').on('click', function(){
        var element = $(this).parents('#edit-styles');
        element.find(':checkbox:not(:disabled)').prop('checked', false);
        return false;
      });
    }
  };
  
  Drupal.behaviors.alternateSelectSliders = {
    attach: function(context) {
      
      
      // SORTA WORKING SLIDER INTERFACE
      $('.width-controller, .push-controller, .prefix-controller, .suffix-controller, .pull-controller').each(function(){
        var select = $(this);
        var selectWrapper = $(this).closest('.form-item');
        var slider = $( '<div class="slider clearfix"><div class="data-value"></div></div>' ).prependTo( selectWrapper ).slider({
          min: 1,
          max: 13,
          range: "min",
          value: select[ 0 ].selectedIndex + 1,
          create: function( event, ui ) {
            
            var currentValue = $(event.target).slider( "value" ) - 1;
            //console.log(currentValue);
            $(event.target).find('.data-value').html(currentValue);
          },
          slide: function( event, ui ) {
            select[ 0 ].selectedIndex = ui.value - 1;
            //console.log(ui.value - 1);
            //console.log(ui);
            var nextValue = ui.value - 1;
            $(ui.handle).closest('.slider').find('.data-value').html(nextValue);
          },
          stop: function( event, ui ) {
            //console.log(event, ui);
            select.change();
          }
        });
        selectWrapper.find('select').hide();
      });
    }
  };
  
  Drupal.behaviors.toggleRegionSettingDisplay = {
    attach: function(context) {

      // hide push/pull by default
      $(".region-settings .form-item[class$='-pull']").hide();
      $(".region-settings .form-item[class$='-push']").hide();
      
      // hide prefix/suffix by default 
      $(".region-settings .form-item[class$='-prefix']").hide();
      $(".region-settings .form-item[class$='-suffix']").hide();
      
      // open up any push/pull items if they are alredy in use and not a value of zero.
      $(".push-controller, .pull-controller").each(function(){
        if ($(this).val() > 0) {
          $(this).parents('.layout-breakpoint-regions').addClass('push-pull-active').find(".form-item[class$='-pull'], .form-item[class$='-push']").show();
        }
        else {
          $(this).parents('.layout-breakpoint-regions').addClass('push-pull-inactive');
        }
      });
      
      // open up any prefix/suffix items if they are alredy in use and not a value of zero.
      $(".prefix-controller, .suffix-controller").each(function(){
        if ($(this).val() > 0) {
          $(this).parents('.layout-breakpoint-regions').find(".form-item[class$='-prefix'], .form-item[class$='-suffix']").show();
        }
      });
      
      // push/pull toggle
      $('.push-pull-toggle').on('click', function(){
        var group = $(this).closest('.details-wrapper');
        // show/hide the send to back/send to front buttons that are only ever needed
        // if you use push pull, so that when you adjust the positioning, you can fix an 
        // overlap of regions to configure the one you need to move. 
        $(this).parents('.layout-breakpoint-regions').toggleClass('push-pull-active');
        //push-pull-active
        group.find(".form-item[class$='-pull'], .form-item[class$='-push']").toggle();
        return false;
      });
      
      // prefix/suffix toggle
      $('.prefix-suffix-toggle').on('click', function(){
        var group = $(this).closest('.details-wrapper');
        group.find(".form-item[class$='-prefix'], .form-item[class$='-suffix']").toggle();
        return false;
      });
      
    }
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
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-push', push);
      });
      
      // adjust the prefix value
      $('select.prefix-controller').on('change', function(){
        var prefix = $(this).val();
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-prefix', prefix);
      });
      
      
      // adjust the width value
      $('select.width-controller').on('change', function(){
        var width = $(this).val();
        //var sliderWidth = width;
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-width', width);
      });      
      
      // adjust the suffix value
      $('select.suffix-controller').on('change', function(){
        var suffix = $(this).val();
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-suffix', suffix);
      });
      
      // adjust the pull value
      $('select.pull-controller').on('change', function(){
        var pull = $(this).val();
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-pull', pull);
      });
    }
  };
  
  Drupal.behaviors.trimLayoutForm = {
    attach: function (context) {
      // Add in a layer of protection on the form, allowing only a single $layout to be sent through the form
      // The php.ini default for max_input_vars is 1000, and Drupal core hasn't addressed the issue.
      // This will try to stay under that limit, by not submitting ALL the layouts present on the settings page
      // but will only send the portion specified by the "Select layout to edit" select option.
      
      $('#system-theme-settings').on('submit', function(){
        var editLayout = $('#edit-edit-this-layout .layout-select');
        //console.log(editLayout);
        // cycle each of the available layouts to edit
        editLayout.each(function(){
          //console.log(this);
          var lname = $(this).attr('value');
          if ($(this).prop('checked')) {
            //console.log(lname + " selected.");
          }
          else {
            //console.log(lname + " NOT selected.");
            var lpattern = "#layout-" + lname + "-config";
            //console.log(lpattern);
            $(lpattern).remove();
          }
          
        });
        //.attr("disabled", "disabled");
        //return false;
      });
    }
  };
  
/*
  Drupal.behaviors.omegaSubthemeGenerationForce = {
    attach: function (context) {
      // Ensure that the "Export" checkbox is checked when the "Force Subtheme Creation" option is on
      // Drupal #states don't quite do the trick on this portion
      $('input[name="force_subtheme_creation"]').on('ready change', function(){
        var forceSubtheme = $(this).prop('checked');
        // the "force subtheme" option is selected, either by default, or by user action
        if (forceSubtheme) {
          var createSubtheme = $('input[name="export[export_new_subtheme]"]').prop('checked');
          if (!createSubtheme) {
            // The create subtheme option is unchecked, likely by an odd set of clicks to trick the form
            // We will force a click to ensure everything is fired through states as it should be.
            $('input[name="export[export_new_subtheme]"]').click();
          }
        }
      });
    }
  };
*/
  
})(jQuery, Drupal, drupalSettings);
