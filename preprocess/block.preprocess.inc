<?php

/**
 * Implements hook_preprocess_block().
 */
function omega_preprocess_block(&$variables) {
  $variables['content_attributes_array']['class'][] = 'block__content';

  if (!empty($variables['block_html_id'])) {
    $variables['attributes_array']['id'] = $variables['block_html_id'];
    $variables['attributes_array']['class'][] = $variables['block_html_id'];

    // Use a template with no wrapper for the page's main content.
    if ($variables['block_html_id'] == 'block-system-main') {
      $variables['theme_hook_suggestions'][] = 'block__minimal';
    }
  }

  // Classes describing the position of the block within the region.
  if ($variables['block_id'] == 1) {
    $variables['attributes_array']['class'][] = 'first';
  }

  // The 'last_in_region' property is set in omega_page_alter().
  if (isset($variables['block']->last_in_region)) {
    $variables['attributes_array']['class'][] = 'last';
  }

  $variables['attributes_array']['class'][] = $variables['block_zebra'];
  $variables['title_attributes_array']['class'][] = 'block__title';

  // Add Aria Roles via attributes.
  switch ($variables['block']->module) {
    case 'system':
      switch ($variables['block']->delta) {
        case 'main':
          // Note: the "main" role goes in the page.tpl, not here.
          break;

        case 'help':
        case 'powered-by':
          $variables['attributes_array']['role'] = 'complementary';
          break;

        default:
          // Any other "system" block is a menu block.
          $variables['attributes_array']['role'] = 'navigation';
          break;
      }
      break;

    case 'menu':
    case 'menu_block':
    case 'blog':
    case 'book':
    case 'comment':
    case 'forum':
    case 'shortcut':
    case 'statistics':
      $variables['attributes_array']['role'] = 'navigation';
      break;

    case 'search':
      $variables['attributes_array']['role'] = 'search';
      break;

    case 'help':
    case 'aggregator':
    case 'locale':
    case 'poll':
    case 'profile':
      $variables['attributes_array']['role'] = 'complementary';
      break;

    case 'node':
      switch ($variables['block']->delta) {
        case 'syndicate':
          $variables['attributes_array']['role'] = 'complementary';
          break;

        case 'recent':
          $variables['attributes_array']['role'] = 'navigation';
          break;
      }
      break;

    case 'user':
      switch ($variables['block']->delta) {
        case 'login':
          $variables['attributes_array']['role'] = 'form';
          break;

        case 'new':
        case 'online':
          $variables['attributes_array']['role'] = 'complementary';
          break;
      }
      break;
  }
}
