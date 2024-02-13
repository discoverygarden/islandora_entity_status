<?php

namespace Drupal\islandora_entity_status\EventSubscriber;

use Drupal\islandora\IslandoraUtils;
use Drupal\islandora_events\Event\IslandoraNodeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines an event subscriber for Islandora Media.
 */
class IslandoraNodeEntitySubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new IslandoraMediaSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      IslandoraNodeEvent::UPDATE => 'onIslandoraNodeUpdated',
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

    // Query for media items that are associated with the current node.
    $query = $this->entityTypeManager->getStorage('media')->getQuery();
    $query->accessCheck(FALSE);
    $query->condition(IslandoraUtils::MEDIA_OF_FIELD, $nid);
    $media_ids = $query->execute();

    // Load the media items and set their status to the same status as the node.
    $media_items = $this->entityTypeManager->getStorage('media')->loadMultiple($media_ids);
    foreach ($media_items as $media_item) {
      // We just need to save the media.
      // Status is getting handled in pre_save.
      // @see IslandoraMediaEntitySubscriber:onIslandoraMediaPresave.
      $media_item->save();
    }
  }

}
