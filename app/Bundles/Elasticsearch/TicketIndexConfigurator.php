<?php

namespace App\Bundles\Elasticsearch;

use ScoutElastic\Migratable;
use ScoutElastic\IndexConfigurator;

class TicketIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $name = 'tickets_index';

    /**
     * @var array
     */
    protected $settings = [
        //
    ];
}
