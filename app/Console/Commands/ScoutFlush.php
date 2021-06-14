<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ScoutFlush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:custom-flush';

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
            'Prescription',
            'Region',
            'City',
            'Street',
            'House',
            'Flat',
            'Equipment'
        ];

        foreach ($models as $model) {
            Artisan::call('scout:flush', [
                'model' => 'App\\Models\\' . $model
            ]);
        }

        $this->info('ок');
    }
}
