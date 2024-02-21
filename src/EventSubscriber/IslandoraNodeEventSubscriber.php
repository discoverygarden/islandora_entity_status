<?php

namespace Drupal\islandora_entity_status\EventSubscriber;

use Drupal\Core\Render\RendererInterface;
use Drupal\islandora\IslandoraUtils;
use Drupal\islandora_events\Event\IslandoraCollectionStatusUpdate;
use Drupal\islandora_events\Event\IslandoraNodeEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Defines an event subscriber for Islandora Media.
 */
class IslandoraNodeEventSubscriber implements EventSubscriberInterface {
  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new IslandoraNodeEventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      IslandoraNodeEvent::UPDATE => 'onIslandoraNodeUpdated',
      IslandoraCollectionStatusUpdate::COLLECTION_STATUS_UPDATED => 'onCollectionStatusUpdated',
    ];
  }

  /**
   * Reacts to node update events.
   *
   * @param \Drupal\islandora_events\Event\IslandoraNodeEvent $event
   *   The Islandora node event.
   */
  public function onIslandoraNodeUpdated(IslandoraNodeEvent $event) {
    // Get the current node ID.
    $node = $event->getNode();
    $nid = $node->id();

    // When there is a change in status update all attached media.
    if ($node->original->isPublished() != $node->isPublished()) {
      // Query for media items that are associated with the current node.
      $query = $this->entityTypeManager->getStorage('media')->getQuery();
      $query->accessCheck(FALSE);
      $query->condition(IslandoraUtils::MEDIA_OF_FIELD, $nid);
      $media_ids = $query->execute();

      // Load the media items and set their status to the same status
      // as the node.
      $media_items = $this->entityTypeManager->getStorage('media')
        ->loadMultiple($media_ids);
      foreach ($media_items as $media_item) {
        // We just need to save the media.
        // Status is getting handled in pre_save.
        // @see IslandoraMediaEntitySubscriber:onIslandoraMediaPresave.
        $media_item->save();
      }
    }
  }

  /**
   * Reacts to collection status updates.
   *
   * @param \Drupal\islandora_events\Event\IslandoraCollectionStatusUpdate $event
   *   The collection object update event.
   */
  public function onCollectionStatusUpdated(IslandoraCollectionStatusUpdate $event) {
    // Update status of all nodes attached to the collection.
    $node = $event->getNode();
    $attached_nodes = $this->findNodesAttachedToCollection($node->id());

    if (!empty($attached_nodes)) {
      // On node or workflow status update of collection node
      // we need to update all attached object node or workflow status.
      $oldNodeStatus = $event->getOriginalNodeStatus();
      $updatedNodeStatus = $event->getUpdatedNodeStatus();
      $status['node'] = ($oldNodeStatus !== $updatedNodeStatus) ? $updatedNodeStatus : '';

      $oldWorkflowStatus = $event->getOriginalWorkflowStatus();
      $updatedWorkflowStatus = $event->getUpdatedWorkflowStatus();
      $status['workflow'] = ($oldWorkflowStatus !== $updatedWorkflowStatus) ? $updatedWorkflowStatus : '';
      $this->triggerBatchProcess($attached_nodes, $status, 10);
    }
  }

  /**
   * Find nodes attached to the Collection.
   */
  protected function findNodesAttachedToCollection($currentNodeId) {
    $relatedNodeIds = [];

    // Initial query to find nodes where the field_member_of contains
    // the current node ID.
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition(IslandoraUtils::MEMBER_OF_FIELD, $currentNodeId);
    $query->accessCheck(FALSE);

    $result = $query->execute();

    $relatedNodes = $this->entityTypeManager->getStorage('node')->loadMultiple($result);

    if (!empty($relatedNodes)) {
      foreach ($relatedNodes as $relatedNode) {
        $relatedNodeIds[] = $relatedNode->id();
      }
    }

    return $relatedNodeIds;
  }

  /**
   * Helper function to trigger the batch process.
   */
  protected function triggerBatchProcess($node_ids, $status, $batch_size = 10) {
    $operations = [];
    foreach ($node_ids as $node_id) {
      $operations[] = [
        [$this, 'islandoraEntityStatusBatchOperation'],
        [$node_id, $status],
      ];
    }

    $batch = [
      'title' => $this->t('Processing nodes'),
      'operations' => $operations,
      'finished' => [$this, 'islandoraEntityStatusBatchFinished'],
      'batch_size' => $batch_size,
    ];
    batch_set($batch);
  }

  /**
   * Batch operation callback.
   */
  public function islandoraEntityStatusBatchOperation($node_id, $status, &$context) {
    // Perform your batch processing here.
    // Load the node using the entity type manager.
    $node = $this->entityTypeManager->getStorage('node')->load($node_id);
    if ($node) {
      $message = '';

      if (isset($status['node'])) {
        // Update the status of the node.
        $node->set('status', $status['node']);
        $message .= 'Node status = ' . $status['node'] . ', ';
      }

      if (!empty($status['workflow'])) {
        // Set the moderation state.
        $node->set('moderation_state', $status['workflow']);
        $message .= 'Workflow status = ' . $status['workflow'] . ', ';
      }

      $node->save();

      // Remove trailing comma and space.
      $message = rtrim($message, ', ');

      // Update the progress.
      $context['results'][] = $this->t('Node %node processed and status set to %status.', [
        '%node' => $node_id,
        '%status' => $message,
      ]);
    }
    else {
      // Handle the case where the node cannot be loaded.
      $context['results'][] = $this->t('Failed to load node with ID %node.', ['%node' => $node_id]);
    }
  }

  /**
   * Batch finished callback.
   */
  public function islandoraEntityStatusBatchFinished($success, $results, $operations) {
    $messenger = $this->messenger;

    if ($success) {
      if (!empty($results)) {
        // Batch processing completed successfully.
        // Display a message indicating success.
        $messages = [];
        foreach ($results as $result) {
          $messages[] = $result;
        }

        $message = [
          '#theme' => 'item_list',
          '#items' => $messages,
          '#title' => $this->t('Processed Nodes'),
        ];

        $this->messenger->addMessage($this->renderer->renderRoot($message));
      }
      else {
        $messenger->addMessage($this->t('No nodes were processed.'));
      }
    }
    else {
      // Batch processing failed.
      $messenger->addError($this->t('Batch processing failed.'));
    }
  }

}
