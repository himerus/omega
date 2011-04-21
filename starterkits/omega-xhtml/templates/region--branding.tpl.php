<div<?php print $attributes; ?>>
  <div class="branding-data clearfix">
    <?php if(isset($linked_logo_img)): ?>
    <div class="logo-img">
      <?php print $linked_logo_img; ?>
    </div>
    <?php endif; ?>
    <div class="site-name-slogan">
      <?php if ($is_front): ?>
      <h1 class="site-title"><?php print $linked_site_name; ?></h1>
      <?php else: ?>
      <h2 class="site-title"><?php print $linked_site_name; ?></h2>
      <?php endif; ?>
      <?php if(isset($site_slogan)): ?>
      <h6 class="site-slogan"><?php print $site_slogan; ?></h6>
      <?php endif; ?>
    </div>
  </div>
  <?php print $content; ?>
</div>