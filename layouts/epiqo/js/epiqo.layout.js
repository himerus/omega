(function ($) {

/**
 * Adds a button to the toolbar that scrolls the viewport to the top of the page
 * on click.
 */
Drupal.behaviors.epiqoScrollTop = {
  attach: function (context, settings) {
    $('body', context).once('scrolltop', function () {
      var $button = $('<a href="#page" class="scroll-button"></a>').appendTo($('#topbar', this));

      $(window).bind('scroll', function (e) {
        if ($(this).scrollTop() > 100) {
          $button.show();
        }
        else {
          $button.hide();
        }
      });
    });
  }
},

/**
 * Adds a toggle button for every block in the navigation region that reveals
 * that block on click.
 */
Drupal.behaviors.epiqoMobileNavigation = {
  attach: function (context, settings) {
    $('#header', context).once('mobile-navigation', function () {
      var $navigation = $('#navigation-container', this);
      var $blocks = $navigation.find('.block');
      var $topbar = $('#topbar', this);

      $blocks.each(function () {
        var $block = $(this);
        var $toggle = $('<a href="#page" class="block-toggle block-toggle-' + $block.attr('id') + '"></a>').appendTo($topbar);

        $toggle.click(function () {
          // Hide any other currently toggled block.
          $blocks.not($block).removeClass('block-toggled');

          if (!$block.hasClass('block-toggled')) {
            $block.addClass('block-toggled');
          }
          else {
            $block.removeClass('block-toggled');
          }
        });
      });
    });
  }
},

Drupal.behaviors.epiqoMobileSidebar = {
  attach: function (context, settings) {
    $('#sidebar-first-container').once('mobile-sidebar', function () {
      // Show the sidebar when a click is performed on its overlapping border.
      $(this).click(function (e) {
        if (!$('body').hasClass('show-sidebar') && $(e.srcElement).attr('id') != 'sidebar-first-toggle') {
          $('body').addClass('show-sidebar');
        }
        else if ($('body').hasClass('show-sidebar')) {
          var click = e.pageX - $(this).offset().left;
          if (click > $(this).innerWidth() && click < $(this).outerWidth()) {
            $('body').removeClass('show-sidebar');
          }
        }
      });

      // Hide the sidebar when the area outside of it is clicked.
      $('#main').click(function (e) {
        if ($('body').hasClass('show-sidebar') && $(e.srcElement).attr('id') != 'sidebar-first-container' && !$(e.srcElement).parents('#sidebar-first-container').length) {
          // Check if the clicked element is the sidebar or a child element of
          // the sidebar.
          $('body').removeClass('show-sidebar');
        }
      });

      // Toggle the sidebar with the toggle button.
      $('<a href="#page" id="sidebar-first-toggle" class="sidebar-first-toggle" />').click(function (e) {
        if (!$('body').hasClass('show-sidebar')) {
          $('body').addClass('show-sidebar');
        }
        else {
          $('body').removeClass('show-sidebar');
        }

        return false;
      }).prependTo(this);
    });
  }
}

})(jQuery);
