<div<?php print $attributes; ?>>
  <div class="branding-data clearfix">
    <?php if (isset($linked_logo_img)) : ?>
    <div class="logo-img">
      <?php print $linked_logo_img; ?>
    </div>
    <?php endif; ?>
    
    <hgroup class="site-name-slogan">
      <?php if ($is_front): ?>
      <h1<?php print $site_name_attributes; ?>><?php print $site_name_linked; ?></h1>
      <?php else: ?>
      <h2<?php print $site_name_attributes; ?>><?php print $site_name_linked; ?></h2>
      <?php endif; ?>
      
      <?php if (isset($site_slogan)) : ?>
      <h6<?php print $site_slogan_attributes; ?>><?php print $site_slogan; ?></h6>
      <?php endif; ?>
    </hgroup>
  </div>
</div>