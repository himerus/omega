<?php
// $Id: aggregator-wrapper.tpl.php,v 1.1.2.2 2010/11/16 16:25:04 himerus Exp $

/**
 * @file
 * Default theme implementation to wrap aggregator content.
 *
 * Available variables:
 * - $content: All aggregator content.
 * - $page: Pager links rendered through theme_pager().
 *
 * @see template_preprocess()
 * @see template_preprocess_aggregator_wrapper()
 */
?>
<div id="aggregator">
  <?php print $content; ?>
  <?php print $pager; ?>
</div>
