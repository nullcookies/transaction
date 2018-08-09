<?php

namespace Drupal\transaction\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Transaction entities.
 */
class TransactionEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
