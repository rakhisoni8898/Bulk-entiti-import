<?php

namespace Drupal\Entity_import;

use Drupal\node\Entity\Node;

/**
 * Implements class addImportContent.
 */
class AddImportContent {

  /**
   * Implements addImportContentItem() to import content.
   */
  public static function addImportContentItem($item, &$context) {
    $context['sandbox']['current_item'] = $item;
    $message = 'Creating ' . $item['title'];
    create_node($item);
    $context['message'] = $message;
    $context['results'][] = $item;
  }

  /**
   * Implements addImportContentItemCallback() to callback.
   */
  public function addImportContentItemCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One item processed.', '@count items processed.'
      );
    }
    else {
      $message = $this->t('Finished with an error.');
    }
    \Drupal::messenger()->addMessage($message);
  }

}

/**
 * This function actually creates each item as a node as type 'Page'.
 */
function create_node($item) {
  $node_data['type'] = $item['type'];
  $node_data['title'] = $item['title'];
  $node_data['field_score']['value'] = $item['field_score'];
  $node_data['field_name']['value'] = $item['field_name'];
  $node_data['field_subject']['value'] = $item['field_subject'];
  $node_data['field_class']['value'] = $item['field_class'];
  $node_data['field_roll_number']['value'] = $item['field_roll_number'];
  $node_data['field_contact_number']['value'] = $item['field_contact_number'];
  $node = Node::create($node_data);
  $node->setPublished(TRUE);
  $node->save();
}
