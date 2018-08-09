<?php

namespace Drupal\transaction;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\transaction\Entity\TransactionEntityInterface;

/**
 * Defines the storage handler class for Transaction entities.
 *
 * This extends the base storage class, adding required special handling for
 * Transaction entities.
 *
 * @ingroup transaction
 */
class TransactionEntityStorage extends SqlContentEntityStorage implements TransactionEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(TransactionEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {transaction_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {transaction_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(TransactionEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {transaction_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('transaction_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
