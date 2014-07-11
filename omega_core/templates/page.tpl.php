<?php

/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * The doctype, html, head and body tags are not in this template. Instead they
 * can be found in the html.tpl.php template in this directory.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 * @see html.tpl.php
 *
 * @ingroup themeable
 */
?>
<div id="page-wrapper" class="clearfix">
  <div id="page" class="clearfix <?php print $region_classes; ?>">

    <header id="header-outer-wrapper" class="outer-wrapper clearfix" role="banner">
      <div id="header-layout" class="inner-wrapper clearfix">
        
        <?php if ($page['header']): ?>
        <div id="header" class="clearfix">
          <?php print render($page['header']); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($logo): ?>
          <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" id="logo">
            <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" />
          </a>
        <?php endif; ?>
  
        <?php if ($site_name || $site_slogan): ?>
          <div id="name-and-slogan">
            <?php if ($site_name): ?>
              <?php if ($title): ?>
                <div id="site-name"><strong>
                  <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><span><?php print $site_name; ?></span></a>
                </strong></div>
              <?php else: /* Use h1 when the content title is empty */ ?>
                <h1 id="site-name">
                  <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><span><?php print $site_name; ?></span></a>
                </h1>
              <?php endif; ?>
            <?php endif; ?>
  
            <?php if ($site_slogan): ?>
              <div id="site-slogan"><?php print $site_slogan; ?></div>
            <?php endif; ?>
          </div> <!-- /#name-and-slogan -->
        <?php endif; ?>
        
        <?php if($main_menu || $secondary_menu): ?>
          <div id="menus" class="clearfix">
            <?php if ($main_menu): ?>
              <nav id ="main-menu" class="navigation clearfix" role="navigation">
                <?php print theme('links__system_main_menu', array('links' => $main_menu, 'attributes' => array('id' => 'main-menu', 'class' => array('links', 'inline', 'clearfix')))); ?>
              </nav> <!-- /#main-menu -->
            <?php endif; ?>
            <?php if($secondary_menu): ?>
              <nav id="secondary-menu" class="navigation clearfix" role="navigation">
                <?php print theme('links__system_secondary_menu', array('links' => $secondary_menu, 'attributes' => array('id' => 'secondary-menu', 'class' => array('links', 'inline', 'clearfix')))); ?>
              </nav> <!-- /#secondary-menu -->
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </header> <!-- /.section, /#header-->

  <?php if ($page['preface_first'] || $page['preface_second'] || $page['preface_third'] || $page['preface_fourth']): ?>
    <section id="preface-outer-wrapper" class="outer-wrapper clearfix">
      <div id="preface-layout" class="inner-wrapper clearfix">
        <?php if ($page['preface_first']): ?>
          <div id="preface-first" class=""><?php print render($page['preface_first']); ?></div>
        <?php endif; ?>
        <?php if ($page['preface_second']): ?>
          <div id="preface-second" class=""><?php print render($page['preface_second']); ?></div>
        <?php endif; ?>
        <?php if ($page['preface_third']): ?>
          <div id="preface-third" class=""><?php print render($page['preface_third']); ?></div>
        <?php endif; ?>
        <?php if ($page['preface_fourth']): ?>
          <div id="preface-fourth" class=""><?php print render($page['preface_fourth']); ?></div>
        <?php endif; ?>
      </div><!-- /#preface-layout -->
    </section> <!-- /#preface-wrapper -->
  <?php endif; ?>
  
  
  <?php
    // need some good cleanup on these default Drupal elements
  ?>
  
  <section id="core-outer-wrapper" class="outer-wrapper clearfix">
    <div id="core-layout" class="inner-wrapper clearfix">
      <div id="highlighted" class="column" role="main">
        <?php if ($breadcrumb): ?>
        <div id="breadcrumb"><?php print $breadcrumb; ?></div>
        <?php endif; ?>
    
        <?php print $messages; ?>
        <a id="main-content"></a>
        <?php print render($title_prefix); ?>
        <?php if ($title): ?><h1 class="title" id="page-title"><?php print $title; ?></h1><?php endif; ?>
        <?php print render($title_suffix); ?>
        <?php if ($tabs): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
        <?php print render($page['help']); ?>
        <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>
      
        <?php if ($page['highlighted']): ?>
          <?php print render($page['highlighted']); ?>
        <?php endif; ?>
      </div>
    </div>
  </section> 

  <section id="content-outer-wrapper" class="outer-wrapper clearfix">
    <div id="main-layout" class="inner-wrapper clearfix">
      <main id="content" class="column" role="main">
        <?php print render($page['content']); ?>
        <?php print $feed_icons; ?>
      </main> <!-- /.section, /#content -->
  
      <?php if ($page['sidebar_first']): ?>
        <div id="sidebar-first" class="column sidebar">
          <aside class="section">
            <?php print render($page['sidebar_first']); ?>
          </aside>
        </div><!-- /.section, /#sidebar-first -->
      <?php endif; ?>
  
      <?php if ($page['sidebar_second']): ?>
        <div id="sidebar-second" class="column sidebar">
          <aside class="section">
            <?php print render($page['sidebar_second']); ?>
          </aside>
        </div><!-- /.section, /#sidebar-second -->
      <?php endif; ?>
  
    </div><!-- /#main -->
  </section><!-- /#main-outer-wrapper -->

  <?php if ($page['postscript_first'] || $page['postscript_second'] || $page['postscript_third'] || $page['postscript_fourth']): ?>
    <section id="postscript-outer-wrapper" class="outer-wrapper clearfix">
      <div id="postscript-layout" class="inner-wrapper clearfix">
        <?php if ($page['postscript_first']): ?>
          <div id="postscript-first" class=""><?php print render($page['postscript_first']); ?></div>
        <?php endif; ?>
        <?php if ($page['postscript_second']): ?>
          <div id="postscript-second" class=""><?php print render($page['postscript_second']); ?></div>
        <?php endif; ?>
        <?php if ($page['postscript_third']): ?>
          <div id="postscript-third" class=""><?php print render($page['postscript_third']); ?></div>
        <?php endif; ?>
        <?php if ($page['postscript_fourth']): ?>
          <div id="postscript-fourth" class=""><?php print render($page['postscript_fourth']); ?></div>
        <?php endif; ?>
      </div><!-- /#postscript-layout -->
    </section> <!-- /#postscript-wrapper -->
  <?php endif; ?>
  
  <?php if ($page['sidebar_second']): ?>
    <footer id="footer-outer-wrapper" class="outer-wrapper clearfix">
      <div id="footer-layout" role="contentinfo" class="inner-wrapper clearfix">
        <div id="footer">
          <?php print render($page['footer']); ?>
        </div>
      </div> <!-- /#footer -->
    </footer> <!-- /#footer-outer-wrapper -->
  <?php endif; ?>
  </div> <!-- /#page -->
</div> <!-- /#page-wrapper -->
