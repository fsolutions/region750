<?php

namespace App\Bundles\Elasticsearch;

use ScoutElastic\Migratable;
use ScoutElastic\IndexConfigurator;

class HouseIndexConfigurator extends IndexConfigurator
{
  use Migratable;

  /**
   * @var array
   */
  protected $settings = [
    //
  ];
}
