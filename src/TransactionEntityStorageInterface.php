<?php

namespace Drupal\transaction;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface TransactionEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Transaction revision IDs for a specific Transaction.
   *
   * @param \Drupal\transaction\Entity\TransactionEntityInterface $entity
   *   The Transaction entity.
   *
   * @return int[]
   *   Transaction revision IDs (in ascending order).
   */
  public function revisionIds(TransactionEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Transaction author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Transaction revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\transaction\Entity\TransactionEntityInterface $entity
   *   The Transaction entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(TransactionEntityInterface $entity);

  /**
   * Unsets the language for all Transaction with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
