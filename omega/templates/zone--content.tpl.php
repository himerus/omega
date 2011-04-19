<?php if ($wrapper) : ?><div<?php print $wrapper_attributes; ?>><?php endif; ?>  
  <div<?php print $attributes; ?>>
    <?php if ($main_menu || $secondary_menu): ?>
      <div id="navigation" class="grid-<?php print $columns; ?>">
        <?php print theme('links__system_main_menu', array('links' => $main_menu, 'attributes' => array('id' => 'main-menu', 'class' => array('links', 'inline', 'clearfix')), 'heading' => t('Main menu'))); ?>
        <?php print theme('links__system_secondary_menu', array('links' => $secondary_menu, 'attributes' => array('id' => 'secondary-menu', 'class' => array('links', 'inline', 'clearfix')), 'heading' => t('Secondary menu'))); ?>
      </div>
    <?php endif; ?>
    
    <?php if ($breadcrumb): ?>
      <div id="breadcrumb" class="grid-<?php print $columns; ?>"><?php print $breadcrumb; ?></div>
    <?php endif; ?>
    
    <?php if ($messages): ?>
      <div id="messages" class="grid-<?php print $columns; ?>"><?php print $messages; ?></div>
    <?php endif; ?>
    
    <?php print $content; ?>
  </div>
<?php if ($wrapper) : ?></div><?php endif; ?>