<?php

namespace Drupal\smith_migrate\Plugin\migrate\source\Node;

use Drupal\migrate\Row;

/**
 * Wysiwyg source plugin.
 *
 * @MigrateSource(
 *   id = "wysiwyg",
 *   source_module = "smith_migrate",
 * )
 */
class Wysiwyg extends Node {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node', 'n')->fields('n', self::NODE_BASE_FIELDS);
    $query->condition('n.type', 'wysiwyg_block');
//    $query->condition('n.nid', 338766);
    $query->leftJoin('field_data_field_wysiwyg_content', 'fdfwc', 'fdfwc.revision_id = n.vid');
    $query->fields('fdfwc', ['field_wysiwyg_content_value']);

    $query->orderBy('n.created');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'nid' => $this->t('nid'),
      'vid' => $this->t('vid'),
      'title' => $this->t('title'),
      'created' => $this->t('created'),
      'changed' => $this->t('changed'),
      'status' => $this->t('status'),
      'type' => $this->t('type'),
      'field_wysiwyg_content_value' => $this->t('field_wysiwyg_content_value'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function prepareRow(Row $row) {
    $source_nid = $row->getSourceProperty('nid');
//    var_dump($row->getSourceProperty('field_wysiwyg_content_value'));
    return parent::prepareRow($row);
  }

}
