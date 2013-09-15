<?php
/**
 * @file
 * Template for the Omega Grid 2 layout.
 *
 * Variables:
 * - $css_id: An optional CSS id to use for the layout.
 * - $content: An array of content, each item in the array is keyed to one
 * panel of the layout. This layout supports the following sections:
 */
?>
<div class="panel-display panel-display--grid-2 <?php if (!empty($class)) { print $class; } ?>" <?php if (!empty($css_id)) { print "id=\"$css_id\""; } ?>>
  <?php foreach($content as $item): ?>
    <?php if (!empty($item)): ?>
      <div class="grid-item">
        <?php print $item ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
