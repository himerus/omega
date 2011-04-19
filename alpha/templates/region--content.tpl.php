<?php if (!empty($content)): ?>
  <div<?php print $attributes; ?>>
    <a id="main-content"></a>
    <?php print render($title_prefix); ?>
    <?php if ($title): ?><h1 class="title" id="page-title"><?php print $title; ?></h1><?php endif; ?>
    <?php print render($title_suffix); ?>
    
    <?php print $content; ?>
  </div>
<?php endif; ?>