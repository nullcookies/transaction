<?php

/**
 * @file
 * Contains transaction_entity.page.inc.
 *
 * Page callback for Transaction entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Transaction templates.
 *
 * Default template: transaction_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_transaction_entity(array &$variables) {
  // Fetch TransactionEntity Entity Object.
  $transaction_entity = $variables['elements']['#transaction_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
