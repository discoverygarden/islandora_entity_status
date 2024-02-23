<?php

namespace Drupal\islandora_entity_status\EventSubscriber;

use Drupal\islandora_events\Event\IslandoraMediaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Defines an event subscriber for Islandora Media.
 */
class IslandoraMediaEventSubscriber implements EventSubscriberInterface {

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
