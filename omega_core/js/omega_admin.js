/**
 * @todo
 */

Drupal.omega = Drupal.omega || {};

(function ($) {
  
  Drupal.behaviors.watchMaxWidthValues = {
    attach: function(context) {
      
      $('input.row-max-width').change( function(){
        //console.log('Hello!!');
        var newVal = $(this).attr('value');
        //console.log(newVal);
        var newType = $(this).parents('.region-group-layout-settings').find('.row-max-width-type');
        //console.log(newType);
        var percentBox = newType.find('input[value="percent"]');
        var pixelBox = newType.find('input[value="pixel"]');
        // assume it is a pixel value and change the radio accordingly        
        if (newVal > 100) {
          pixelBox.attr("checked", true);
          percentBox.attr("checked", false);
        }
        // assume it is a percent value and change the radio accordingly        
        else {
          percentBox.attr("checked", true);
          pixelBox.attr("checked", false);
        }
      });
      
    }
  };
  
  Drupal.behaviors.addZindexButtons = {
    attach: function(context) {
      
      $('.region-settings > .fieldset-wrapper').each(function(){
        $(this).prepend('<div class="region-controls clearfix"><a href="#" title="Send to Back" class="send-to-back"></a><a href="#" title="Send to Front" class="send-to-front"></a></div>');
      }); 
      
      
      $('.send-to-back').click( function(){
        var element = $(this).closest('.region-settings');
        element.css('z-index', 0);
        return false;
      });
      $('.send-to-front').click( function(){
        var element = $(this).closest('.region-settings');
        element.css('z-index', 1000);
        return false;
      });
    },
    detach: function(context) {
      $('.region-settings .region-controls').remove();
    }
  };
  
  
  Drupal.behaviors.addToggleStyles = {
    attach: function(context) {
      $('a.toggle-styles-on').click( function(){
        $(this).parents('#edit-styles').find(':checkbox').attr('checked', true);
        return false;
      });
      $('a.toggle-styles-off').click( function(){
        $(this).parents('#edit-styles').find(':checkbox').attr('checked', false);
        return false;
      });
    }
  };
  
  Drupal.behaviors.alternateSelectSliders = {
    attach: function(context) {
      // SORTA WORKING SLIDER INTERFACE
      
      $('.width-controller, .push-controller, .prefix-controller, .suffix-controller, .pull-controller').once(function(){
        var select = $(this);
        var selectWrapper = $(this).closest('.form-item');
        var slider = $( '<div class="slider clearfix"><div class="data-value"></div></div>' ).prependTo( selectWrapper ).slider({
          min: 1,
          max: 13,
          range: "min",
          value: select[ 0 ].selectedIndex + 1,
          create: function( event, ui ) {
            //console.log('Serving up some sliders for current layout...');
            var currentValue = $(event.target).slider( "value" ) - 1;
            //console.log(currentValue);
            $(event.target).find('.data-value').html(currentValue);
            select.hide();
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
      });
    },
    detach: function(context) {
      
      $('.width-controller, .push-controller, .prefix-controller, .suffix-controller, .pull-controller').once(function(){
      //$('.slider').each(function(){
        //console.log('Destroying sliders...');
        $('.slider').slider('destroy').remove();
        $(this).remove();
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
        if ($(this).attr('value') > 0) {
          $(this).parents('.layout-breakpoint-regions').addClass('push-pull-active').find(".form-item[class$='-pull'], .form-item[class$='-push']").show();
        }
        else {
          $(this).parents('.layout-breakpoint-regions').addClass('push-pull-inactive');
        }
      });
      
      // open up any prefix/suffix items if they are alredy in use and not a value of zero.
      $(".prefix-controller, .suffix-controller").each(function(){
        if ($(this).attr('value') > 0) {
          $(this).parents('.layout-breakpoint-regions').find(".form-item[class$='-prefix'], .form-item[class$='-suffix']").show();
        }
      });
      
      // push/pull toggle
      $('.push-pull-toggle').click( function(){
        var group = $(this).closest('fieldset');
        // show/hide the send to back/send to front buttons that are only ever needed
        // if you use push pull, so that when you adjust the positioning, you can fix an 
        // overlap of regions to configure the one you need to move. 
        $(this).parents('.layout-breakpoint-regions').toggleClass('push-pull-active');
        //push-pull-active
        group.find(".form-item[class$='-pull'], .form-item[class$='-push']").toggle();
        return false;
      });
      
      // prefix/suffix toggle
      $('.prefix-suffix-toggle').click( function(){
        var group = $(this).closest('fieldset');
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
      $('select.push-controller').change( function(){
        var push = $(this).attr('value');
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-push', push);
      });
      
      // adjust the prefix value
      $('select.prefix-controller').change( function(){
        var prefix = $(this).attr('value');
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-prefix', prefix);
      });
      
      
      // adjust the width value
      $('select.width-controller').change( function(){
        var width = $(this).attr('value');
        //var sliderWidth = width;
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-width', width);
      });      
      
      // adjust the suffix value
      $('select.suffix-controller').change( function(){
        var suffix = $(this).attr('value');
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-suffix', suffix);
      });
      
      // adjust the pull value
      $('select.pull-controller').change( function(){
        var pull = $(this).attr('value');
        $(this).next('.slider').slider("value", this.selectedIndex + 1)
        $(this).parents('.region-settings').attr('data-omega-pull', pull);
      });
    }
  };
  
  Drupal.behaviors.omegaJSONlayout = {
    attach: function (context) {
      $('#layout-editor-select select').change(function(){
        //$('#layout-configuration-wrapper').hide('fast');
        //console.log('Altering the layout builder view...');
        //$('#layout-configuration-wrapper').show('slow');
      });
    },
    detach: function (context) {
      
    }
  };
  
})(jQuery);
