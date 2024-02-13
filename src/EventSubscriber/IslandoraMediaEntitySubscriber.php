<?php

namespace Drupal\islandora_entity_status\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
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
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new IslandoraMediaSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(AccountProxyInterface $current_user, LoggerChannelFactoryInterface $logger_factory) {
    $this->currentUser = $current_user;
    $this->loggerFactory = $logger_factory;
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
    $referenced_node = $event->getReferencedNode();

    // Set media status same as media_of node status.
    $referenced_node_status = intval($referenced_node->status->value);
    $media->set('status', $referenced_node_status);
  }

}
