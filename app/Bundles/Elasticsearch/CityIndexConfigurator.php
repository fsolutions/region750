<?php

namespace App\Bundles\Elasticsearch;

use ScoutElastic\Migratable;
use ScoutElastic\IndexConfigurator;

class CityIndexConfigurator extends IndexConfigurator
{
  use Migratable;

  /**
   * @var array
   */
  protected $settings = [
    //
  ];
}
