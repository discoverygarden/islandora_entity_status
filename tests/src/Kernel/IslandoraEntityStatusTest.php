<?php

namespace Drupal\Tests\islandora_entity_status\Kernel;

use Drupal\node\NodeInterface;
use Drupal\Tests\islandora_test_support\Kernel\AbstractIslandoraKernelTestBase;

/**
 * Test Islandora Entity status.
 *
 * @group islandora_entity_status
 */
class IslandoraEntityStatusTest extends AbstractIslandoraKernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['islandora_entity_status'];

  /**
   * Test that derivatives created on an unpublished node are unpublished.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testDerivativeUnpublishedStatus() {
    // Create node.
    $node = $this->createNode();

    // Set node status as unpublished.
    $node->setUnpublished();
    $node->save();

    // Create derivative.
    $unpublishedDerivative = $this->createMedia($this->createFile(), $node);

    // Test derivative status. It should be unpublished.
    $this->assertSame(NodeInterface::NOT_PUBLISHED, intval($unpublishedDerivative->status->value));
  }

  /**
   * Test that derivatives created on a published node are published.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testDerivativePublishedStatus() {
    // Create node.
    $node = $this->createNode();

    // Set node status as published.
    $node->setPublished();
    $node->save();

    // Create new derivative.
    $publishedDerivative = $this->createMedia($this->createFile(), $node);

    // Check derivative status. It should be published.
    $this->assertSame(NodeInterface::PUBLISHED, intval($publishedDerivative->status->value));
  }

}
