<?php

namespace Drupal\smith_migrate\Plugin\migrate\source\Node;

use Drupal\Core\State\StateInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\MigrateStubInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Common source for migrating nodes.
 */
abstract class Node extends SqlBase {

  const NODE_BASE_FIELDS = [
    'nid',
    'vid',
    'title',
    'created',
    'changed',
    'status',
    'type',
    'uid',
    'promote',
    'sticky',
  ];

  /**
   * Stored node aliases.
   *
   * @var array
   */
  protected $aliases = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, MigrateLookupInterface $migrate_lookup, MigrateStubInterface $migrate_stub) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state);

    $this->migration = $migration;
    $this->migrateLookup = $migrate_lookup;
    $this->migrateStub = $migrate_stub;

    $q = $this->select('url_alias', 'ua')
      ->fields('ua', ['source', 'alias'])
      ->condition('source', 'node/%', 'LIKE');
    $this->aliases = $q->execute()
      ->fetchAllAssoc('source');

  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

  /**
   * Get a node alias from a node id.
   */
  protected function getAliasFromNid($nid) {
    return isset($this->aliases['node/' . $nid]) ? $this->aliases['node/' . $nid]['alias'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('state'),
      $container->get('migrate.lookup'),
      $container->get('migrate.stub')
    );
  }

  protected function migrationLookupSingle($migration, $ids = []) {
    $ids = array_filter($ids);
    if (empty($ids)) {
      return [];
    }
    return $this->migrateLookup->lookup([$migration], $ids);
  }

}
