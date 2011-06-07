/**
 * Equal Heights Plugin
 * Equalize the heights of elements. Great for columns or any elements
 * that need to be the same size (floats, etc).
 * 
 * Version 1.0
 * Updated 12/10/2008
 *
 * Copyright (c) 2008 Rob Glazebrook (cssnewbie.com)
 * 
 * Modified for Omega by Sebastian Siemssen (fubhy)
 *
 * Usage: $(object).equalHeights([minHeight], [maxHeight]);
 * 
 * Example 1: $('.cols').equalHeights(); Sets all columns to the same height.
 * Example 2: $('.cols').equalHeights(400); Sets all cols to at least 400px tall.
 * Example 3: $('.cols').equalHeights(100,300); Cols are at least 100 but no more
 * than 300 pixels tall. Elements with too much content will gain a scrollbar.
 */

(function($) {
  $.fn.equalHeights = function(minHeight, maxHeight) {
    var tallest = (minHeight) ? minHeight : 0;

    this.each(function() {
      if ($(this).height() > tallest)
        tallest = $(this).height();
    });
    
    if ((maxHeight) && tallest > maxHeight) 
      tallest = maxHeight;

    return this.each(function() {
      if (tallest < $(this).height())
        $(this).css('overflow', 'scroll');
      
      $(this).height(tallest);
    });
  }
  
  $.fn.bindHeights = function(minHeight, maxHeight) {
    var elements = $(this);
    
    $(elements).equalHeights(minHeight, maxHeight).each(function() {
      $(this).resize(function() {
        var height = $(this).height();
        
        $(elements).unbind('resize').height('auto');
        $(this).height(height);
        $(elements).bindHeights(minHeight, maxHeight);
      });
    });
  }
  
  $(window).load(function() {
    $($('.equal-height-container').get().reverse()).each(function() {
      $(this).children('.equal-height-element').bindHeights();
    });
  });
})(jQuery);