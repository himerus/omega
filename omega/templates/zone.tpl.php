<?php
// $Id$
//drupal_set_message('calling <strong>zone.tpl.php</strong>.');
?>


<?php if($enabled): ?>
	<?php if($wrapper): ?>
	  <div id="<?php print $zid;?>-outer-wrapper">
	<?php endif; ?>  
	  <div id="<?php print $zid;?>-container" class="container-<?php print $container_width; ?> clearfix">
	    <?php print render($regions); ?>
	  </div>
	<?php if($wrapper): ?>
	  </div>
	<?php endif; ?>
<?php endif; ?>