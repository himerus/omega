<?php

/**
 * @file
 * Defines an asymetric Mondrian inspired panels layout.
 */

// Plugin definition.
$plugin = array(
  'title' => t('Mondrian'),
  'icon' => 'preview.png',
  'category' => t('Omega content layouts'),
  'theme' => 'mondrian',
  'css' => '../../../css/layouts/mondrian/mondrian.layout.css',
  'regions' => array(
    'main' => t('Main'),
    'secondary' => t('Secondary'),
    'tertiary-first' => t('Tertiary First'),
    'tertiary-second' => t('Tertiary Second'),
    'tertiary-third' => t('Tertiary Third'),
  ),
);
