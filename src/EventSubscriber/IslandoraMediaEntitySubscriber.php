<?php

namespace Drupal\islandora_entity_status\EventSubscriber;

use Drupal\islandora_events\Event\IslandoraMediaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Defines an event subscriber for Islandora Media.
 */
class IslandoraMediaEntitySubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new IslandoraMediaSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      IslandoraMediaEvent::PRE_SAVE => 'onIslandoraMediaPresave',
    ];
  }

  /**
   * Reacts to the Islandora Media presave event.
   *
   * @param \Drupal\islandora_events\Event\IslandoraMediaEvent $event
   *   The Islandora Media event.
   */
  public function onIslandoraMediaPresave(IslandoraMediaEvent $event) {
    $media = $event->getMedia();
    $media_of_node = $event->getReferencedNode();

    // Set media status same as media_of node status.
    $media_of_node_status = intval($media_of_node->status->value);
    $media->set('status', $media_of_node_status);
  }

}
