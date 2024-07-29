<?php

namespace Drupal\islandora_entity_status;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Subscriber to handle deletion of related Embargoes when a node is deleted.
 */
class IslandoraNodeDeleteSubscriber {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CustomEntityDeleter object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Deletes the associated embargos.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Islandora object node being deleted.
   */
  public function deleteAssociatedCustomEntity(NodeInterface $node): void {
    $custom_entity_storage = $this->entityTypeManager->getStorage('embargo');
    $custom_entities = $custom_entity_storage->loadByProperties(['embargoed_node' => $node->id()]);

    foreach ($custom_entities as $custom_entity) {
      $custom_entity->delete();
    }
  }

}
