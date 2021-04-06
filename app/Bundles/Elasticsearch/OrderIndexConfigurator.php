<?php

namespace App\Bundles\Elasticsearch;

use ScoutElastic\Migratable;
use ScoutElastic\IndexConfigurator;

class OrderIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $name = 'orders_index';

    /**
     * @var array
     */
    protected $settings = [
        //
    ];
}
