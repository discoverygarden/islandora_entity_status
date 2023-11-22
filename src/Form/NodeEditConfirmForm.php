<?php

namespace Drupal\islandora_entity_status\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Confirmation for editing a node.
 */
class NodeEditConfirmForm extends ConfirmFormBase {

  /**
   * Temporary storage for the 'node_edit_confirm'.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStore = $private_temp_store->get('islandora_entity_status');
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreData = $this->tempStore->get('node_edit_data');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_edit_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to edit this node? ' .
      'The status of its associated collection items will be ' .
      'changed to the same.<br><br>');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    // Retrieve data from temporary storage
    $data = $this->tempStoreData;
    $currentNodeId = $data['node_id'];
    return Url::fromRoute('entity.node.edit_form', ['node' => $currentNodeId]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    // Retrieve data from temporary storage.
    $nodeEditData = $this->tempStoreData;;

    if (!$nodeEditData['node_id']) {
      $form_state->setRedirect('entity.node.edit_form', ['node' => $nodeEditData['node_id']]);
    }

    $form['#title'] = 'Confirm Edit';

    $form['question'] = [
      '#markup' => $this->getQuestion(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Perform any necessary logic before redirecting.
    // Trigger node save with data from session.
    $data = $this->tempStoreData;;

    if (!empty($data) && is_array($data)) {
      // Load the node.
      $node = \Drupal\node\Entity\Node::load($data['node_id']);

      if ($node instanceof \Drupal\node\NodeInterface) {

        // Update the node fields with the latest data.
        foreach ($data['data'] as $field_name => $field_value) {
          // Need to skip node created data, not required for update.
          if ($node->hasField($field_name) && $field_name != 'created') {
            // For other fields, set values directly.
            $node->set($field_name, $field_value);
          }
        }

        // Save the updated node.
        $node->save();
      }
    }

    // Delete the data stored.
    $this->tempStore->delete('node_edit_data');

    // Redirect to the node detail page.
    $url = Url::fromRoute('entity.node.canonical', ['node' => $data['node_id']]);
    $form_state->setRedirectUrl($url);
  }

}
