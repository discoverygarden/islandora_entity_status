<?php

namespace Drupal\islandora_entity_status\Commands;

use Drush\Commands\DrushCommands;

/**
 * Drush command implementation.
 */
class BulkStatusCommands extends DrushCommands implements ContainerInjectionInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(
        EntityTypeManagerInterface $entity_type_manager,
    ) {
    parent::__construct();
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Find and update related nodes.
   *
   * @param string $nodes
   *   Comma-separated node IDs.
   * @param int $status
   *   Status to be assigned (0 or 1).
   *
   * @command islandora_entity_status:find-update-related-nodes
   * @aliases furnd
   * @options nodes Comma-separated node IDs.
   * @options status to be assigned (0 or 1).
   */
  public function findUpdateRelatedNodes($nodes, $status) {
    $nodeIds = explode(',', $nodes);

    // Loop through each provided node ID.
    foreach ($nodeIds as $nodeId) {
      $this->updateRelatedNodes($nodeId, $status);
    }

    $this->logger()->success($this->t('Related nodes updated successfully.'));
  }

  /**
   * Update status for related nodes.
   *
   * @param int $currentNodeId
   *   The current node ID.
   * @param int $status
   *   Status to be assigned (0 or 1).
   */
  private function updateRelatedNodes($currentNodeId, $status) {
    // Use the provided function to find related nodes.
    $relatedNodeIds = findCollectionNodes($currentNodeId);

    // Include the provided node ID in the list of nodes to update.
    $relatedNodeIds[] = $currentNodeId;

    // Update the status for each related node.
    foreach ($relatedNodeIds as $relatedNodeId) {
      $node = $this->entityTypeManager->getStorage('node')->load($relatedNodeId);

      if ($node) {
        $node->set('status', $status);
        $node->save();
      }
    }
  }

}
