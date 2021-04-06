<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\UniversalObserver;
use Illuminate\Auth\Events\Registered;
use App\Observers\OrderCascadeDeletesObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\NewComment' => [
            'App\Listeners\PushNotification',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // logging
        Order::observe(UniversalObserver::class);

        // cascade deleted
        Order::observe(OrderCascadeDeletesObserver::class);
    }
}
