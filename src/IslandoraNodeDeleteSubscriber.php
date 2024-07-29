<?php

namespace Drupal\islandora_entity_status;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

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
   * Deletes the associated custom entity.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Islandora object node being deleted.
   */
  public function deleteAssociatedCustomEntity(NodeInterface $node): void {
    // Replace 'your_custom_entity_type' with the actual machine name of your custom entity type.
    $custom_entity_storage = $this->entityTypeManager->getStorage('embargo');
    $custom_entities = $custom_entity_storage->loadByProperties(['embargoed_node' => $node->id()]);

    foreach ($custom_entities as $custom_entity) {
      $custom_entity->delete();
    }
  }
}
