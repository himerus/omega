<?php

/**
 * @file
 * Default theme implementation to display a single Drupal page while offline.
 *
 * All the available variables are mirrored in html.tpl.php and page.tpl.php.
 * Some may be blank but they are provided for consistency.
 *
 * @see template_preprocess()
 * @see template_preprocess_maintenance_page()
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language ?>" lang="<?php print $language->language ?>" dir="<?php print $language->dir ?>">

<head>
  <title><?php print $head_title; ?></title>
  <?php print $head; ?>
  <?php print $styles; ?>
  <?php print $scripts; ?>
</head>
<body class="<?php print $classes; ?>">
  <div id="page" class="clearfix">
    <?php if (isset($zones_above)): ?>
    <div id="zones-above" class="clearfix"><?php print $zones_above; ?></div>
    <?php endif; ?>
    <div id="zones-content" class="clearfix">
      <?php if (isset($action_links)): ?>
        <div id="actions-container" class="container-<?php print $default_container_width; ?> clearfix">
          <div class="grid-<?php print $default_container_width; ?>">
            <ul class="action-links">
              <?php print render($action_links); ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>
      <?php if (isset($messages)): ?>
      <div id="message-container" class="container-<?php print $default_container_width; ?> clearfix">
        <div class="grid-<?php print $default_container_width; ?>">
          <?php print $messages; ?>
        </div>
      </div><!-- /.container-xx -->
      <?php endif; ?>
    
      <?php print $content_zone; ?>
    </div>
    
    <?php if (isset($zones_below)): ?>
    <div id="zones-below" class="clearfix"><?php print $zones_below; ?></div>
    <?php endif; ?>
  </div><!-- /#page -->

</body>
</html>
