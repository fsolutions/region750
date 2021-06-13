<?php

namespace App\Bundles\Elasticsearch;

use ScoutElastic\Migratable;
use ScoutElastic\IndexConfigurator;

class StreetIndexConfigurator extends IndexConfigurator
{
  use Migratable;

  /**
   * @var array
   */
  protected $settings = [
    //
  ];
}
