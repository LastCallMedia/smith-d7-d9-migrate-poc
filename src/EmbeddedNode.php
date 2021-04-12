<?php
namespace Drupal\smith_migrate;

class EmbeddedNode {
  public int $originalNid;
  public array $destinationIds;
  public int $weight;

  public function __construct($originalNid, $destinationIds, $weight) {
    $this->originalNid = $originalNid;
    $this->destinationIds = $destinationIds;
    $this->weight = $weight;
  }

};
