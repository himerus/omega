(function ($, Drupal) {

  /**
   * Adds a 'close' link on messages that allows them to be discarded.
   */
  Drupal.behaviors.omegaCloseableMessages = {
    attach: function (context, settings) {
      $('.messages', context).once('closeable-messages', function () {
        $('<a href="#" class="close-message"></a>').click(function () {
          $(this).closest('.messages').fadeOut(function () {
            $(this).remove();
          });

          return false;
        }).appendTo(this);
      });
    }
  }

})(jQuery, Drupal);
