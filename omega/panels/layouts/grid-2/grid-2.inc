<?php

/**
 * @file
 * Defines a simple 2-column grid layout.
 */

// Plugin definition.
$plugin = array(
  'title' => t('Grid (2)'),
  'icon' => 'preview.png',
  'category' => t('Omega content layouts'),
  'theme' => 'grid-2',
  'css' => 'grid-2.css',
  'regions' => array(
    'top-left' => t('Top Left'),
    'top-right' => t('Top Right'),
    'middle-left' => t('Middle Left'),
    'middle-right' => t('Middle Right'),
    'bottom-left' => t('Bottom Left'),
    'bottom-right' => t('Bottom Right'),
  ),
);
