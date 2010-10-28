<?php
// $Id$

/**
 * @file
 * Theme implementation to display a single Drupal page.
 */
//krumo($zones);
?>
<div id="page" class="clearfix">
  <?php if(isset($zones_above)): ?>
  <?php print $zones_above; ?>
  <?php endif; ?>
  
  <?php if (isset($messages)): ?>
  <div class="container-<?php print $default_container_width; ?> clearfix">
    <div class="grid-<?php print $default_container_width; ?>">
      <?php print $messages; ?>
    </div>
  </div><!-- /.container-xx -->
  <?php endif; ?>

  <?php print $content_zone; ?>
  
  <?php if(isset($zones_above)): ?>
  <?php print $zones_above; ?>
  <?php endif; ?>
</div><!-- /#page -->