<?php if (!empty($content)): ?>
  <div<?php print $attributes; ?>>
    <a id="main-content"></a>
    <?php if ($title): ?>
    <?php print render($title_prefix); ?>
    <h1 class="title" id="page-title"><?php print $title; ?></h1>
    <?php print render($title_suffix); ?>
    <?php endif; ?>
    <?php if ($tabs): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
    <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>
    <?php print $content; ?>
  </div>
<?php endif; ?>