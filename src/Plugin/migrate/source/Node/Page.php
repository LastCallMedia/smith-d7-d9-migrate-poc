<?php

namespace Drupal\smith_migrate\Plugin\migrate\source\Node;

use Drupal\migrate\Row;
use Drupal\smith_migrate\EmbeddedNode;

/**
 * Wysiwyg source plugin.
 *
 * @MigrateSource(
 *   id = "page",
 *   source_module = "smith_migrate",
 * )
 */
class Page extends Node {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node', 'n')->fields('n', self::NODE_BASE_FIELDS);
    $query->condition('n.type', 'panel');
    $query->orderBy('n.created');
    $query->condition('n.nid', 3851);
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
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function prepareRow(Row $row) {
    $source_nid = $row->getSourceProperty('nid');


    $q = $this->select('panels_node', 'pn');
    $q->join('panels_display', 'pd', 'pd.did=pn.did');
    $q->condition('pn.nid', $source_nid);
    $q->fields('pd', ['panel_settings']);
    $q->leftJoin('panels_pane', 'pp', 'pp.did=pn.did');
    $q->fields('pp', ['pid', 'type', 'subtype', 'configuration', 'position']);

    $results = $q->execute()->fetchAll();

    $reduced = array_filter($results, function ($i) {
      $config = unserialize($i['configuration']);
      return isset($config['nid']);
    });

    $embedded = [];
    foreach ($results as $r) {
      $config = unserialize($r['configuration']);
      if (!isset($config['nid'])) {
        continue;
      }
//      var_dump($config);
    }

    foreach ($reduced as $r) {
      $config = unserialize($r['configuration']);
      $lookup = $this->migrationLookupSingle('node_paragraph_wysiwyg', [$config['nid']]);
      if ($lookup) {
        $embedded[] = new EmbeddedNode($config['nid'], $lookup, (int) $r['position']);
      }
    }


    usort($embedded, function($a, $b) {
      return $a->weight > $b->weight;
    });

    $paragraphs = [];
    foreach ($embedded as $e) {
      $paragraphs[] = $e->destinationIds[0];
    }

    $pids = [];
    $vids = [];
    foreach ($paragraphs as $paragraph) {
      $pids[] = $paragraph['id'];
      $vids[] = $paragraph['revision_id'];
    }
    $row->setSourceProperty('pids', $pids);
    $row->setSourceProperty('vids', $vids);

    $row->setSourceProperty('components', $paragraphs);
    return parent::prepareRow($row);
  }

}
