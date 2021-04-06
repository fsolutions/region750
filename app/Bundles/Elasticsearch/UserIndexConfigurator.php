<?php

namespace App\Bundles\Elasticsearch;

use ScoutElastic\Migratable;
use ScoutElastic\IndexConfigurator;

class UserIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $name = 'userss_index';

    /**
     * @var array
     */
    protected $settings = [
        //
    ];
}
