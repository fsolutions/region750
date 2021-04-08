<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class CreateIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:custom-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $models = [
            'Contract',
            'ContractTO',
            'Order',
            'User',
            'Prescription'
        ];

        foreach ($models as $model) {
            Artisan::call('elastic:create-index', [
                'index-configurator' => 'App\\Bundles\\Elasticsearch\\' . $model . 'IndexConfigurator'
            ]);
        }

        $this->info('ок');
    }
}
