<?php

/**
 * Shows a notice when Google Chrome Frame is not installed.
 */
function omega_omega_chrome($variables) {
  $message = t('You are using an outdated browser! <a href="!upgrade" target="_blank">Upgrade your browser today</a> or <a href="!install" target="_blank">install Google Chrome Frame</a> to better experience this site.', array(
    '!upgrade' => url('http://browsehappy.com'),
    '!install' => url('http://www.google.com/chromeframe', array(
      'query' => array('redirect' => 'true')
    )),
  ));

  return '<p class="chromeframe">' . $message . '</p>';
}
