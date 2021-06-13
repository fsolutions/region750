<?php

namespace App\Bundles\Elasticsearch;

use ScoutElastic\Migratable;
use ScoutElastic\IndexConfigurator;

class RegionIndexConfigurator extends IndexConfigurator
{
  use Migratable;

  /**
   * @var array
   */
  protected $settings = [
    //
  ];
}
