<div class="page">
  <header role="banner">
    <?php if ($logo): ?>
      <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" class="header--logo"><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" class="header--logo-image" /></a>
    <?php endif; ?>

    <?php if ($site_name || $site_slogan): ?>
      <hgroup class="header--name-and-slogan">
        <?php if ($site_name): ?>
          <h1 class="header--site-name">
            <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><?php print $site_name; ?></a>
          </h1>
        <?php endif; ?>

        <?php if ($site_slogan): ?>
          <h2 class="header--site-slogan"><?php print $site_slogan; ?></h2>
        <?php endif; ?>
      </hgroup>
    <?php endif; ?>

    <?php if (render($page['navigation'])): ?>
      <div class="header--navigation">
        <?php print render($page['navigation']); ?>
      </div>
    <?php endif; ?>
  </header>

  <div role="main">
    <div class="main-content">
      <?php print render($page['preface']); ?>

      <a id="main-content-anchor"></a>
      <?php print render($title_prefix); ?>
      <?php if ($title): ?>
        <h1 class="page--title"><?php print $title; ?></h1>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php print $messages; ?>
      <?php print render($tabs); ?>
      <?php if ($action_links): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>

      <?php print render($page['content']); ?>
      <?php print render($page['postscript']); ?>
    </div>

    <?php if ($sidebar_first = render($page['sidebar_first'])): ?>
      <aside class="sidebar-first">
        <?php print $sidebar_first; ?>
      </aside>
    <?php endif; ?>

    <?php if ($sidebar_second = render($page['sidebar_second'])): ?>
      <aside class="sidebar-second">
        <?php print $sidebar_second; ?>
      </aside>
    <?php endif; ?>
  </div>

  <?php if ($footer = render($page['footer'])): ?>
    <footer role="contentinfo">
      <?php print $footer; ?>
    </div>
  <?php endif; ?>
</div>
