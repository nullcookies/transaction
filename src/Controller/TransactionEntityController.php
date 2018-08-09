<?php

namespace Drupal\transaction\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\transaction\Entity\TransactionEntityInterface;

/**
 * Class TransactionEntityController.
 *
 *  Returns responses for Transaction routes.
 */
class TransactionEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Transaction  revision.
   *
   * @param int $transaction_entity_revision
   *   The Transaction  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($transaction_entity_revision) {
    $transaction_entity = $this->entityManager()->getStorage('transaction_entity')->loadRevision($transaction_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('transaction_entity');

    return $view_builder->view($transaction_entity);
  }

  /**
   * Page title callback for a Transaction  revision.
   *
   * @param int $transaction_entity_revision
   *   The Transaction  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($transaction_entity_revision) {
    $transaction_entity = $this->entityManager()->getStorage('transaction_entity')->loadRevision($transaction_entity_revision);
    return $this->t('Revision of %title from %date', ['%title' => $transaction_entity->label(), '%date' => format_date($transaction_entity->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Transaction .
   *
   * @param \Drupal\transaction\Entity\TransactionEntityInterface $transaction_entity
   *   A Transaction  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(TransactionEntityInterface $transaction_entity) {
    $account = $this->currentUser();
    $langcode = $transaction_entity->language()->getId();
    $langname = $transaction_entity->language()->getName();
    $languages = $transaction_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $transaction_entity_storage = $this->entityManager()->getStorage('transaction_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $transaction_entity->label()]) : $this->t('Revisions for %title', ['%title' => $transaction_entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all transaction revisions") || $account->hasPermission('administer transaction entities')));
    $delete_permission = (($account->hasPermission("delete all transaction revisions") || $account->hasPermission('administer transaction entities')));

    $rows = [];

    $vids = $transaction_entity_storage->revisionIds($transaction_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\transaction\TransactionEntityInterface $revision */
      $revision = $transaction_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $transaction_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.transaction_entity.revision', ['transaction_entity' => $transaction_entity->id(), 'transaction_entity_revision' => $vid]));
        }
        else {
          $link = $transaction_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.transaction_entity.translation_revert', ['transaction_entity' => $transaction_entity->id(), 'transaction_entity_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.transaction_entity.revision_revert', ['transaction_entity' => $transaction_entity->id(), 'transaction_entity_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.transaction_entity.revision_delete', ['transaction_entity' => $transaction_entity->id(), 'transaction_entity_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['transaction_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
