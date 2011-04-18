<?php $tag = $block->subject ? 'section' : 'div'; ?>
<<?php print $tag; ?> id="<?php print $block_html_id; ?>" class="<?php print $classes; ?>"<?php print $attributes; ?>>
  <div class="branding-data clearfix">
    <?php if(isset($linked_logo_img)): ?>
    <div class="logo-img">
      <?php print $linked_logo_img; ?>
    </div>
    <?php endif; ?>
    <hgroup class="site-name-slogan">
      <?php if ($is_front): ?>
      <h1 class="site-title<?php print $site_name_visibility; ?>"><?php print $linked_site_name; ?></h1>
      <?php else: ?>
      <h2 class="site-title<?php print $site_name_visibility; ?>"><?php print $linked_site_name; ?></h2>
      <?php endif; ?>
      <?php if(isset($site_slogan)): ?>
      <h6 class="site-slogan<?php print $site_slogan_visibility; ?>"><?php print $site_slogan; ?></h6>
      <?php endif; ?>
    </hgroup>
  </div>
</<?php print $tag; ?>>