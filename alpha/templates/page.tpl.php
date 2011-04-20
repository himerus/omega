<div<?php print $attributes; ?>>
  <?php if (isset($page['header_section'])) : ?>
    <?php print render($page['header_section']); ?>
  <?php endif; ?>
  
  <?php if (isset($page['content_section'])) : ?>
    <?php print render($page['content_section']); ?>
  <?php endif; ?>  
  
  <?php if (isset($page['footer_section'])) : ?>
    <?php print render($page['footer_section']); ?>
  <?php endif; ?>
</div>