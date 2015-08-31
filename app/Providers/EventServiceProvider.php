<?php

namespace Mypleasure\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        /*'Mypleasure\Events\SomeEvent' => [
            'Mypleasure\Listeners\EventListener',
        ],*/
        'tymon.jwt.valid' => [
            'App\Events\JWTEvents@valid',
        ],
        'tymon.jwt.user_not_found' => [
            'App\Events\JWTEvents@notFound'
        ],
        'tymon.jwt.invalid' => [
            'App\Events\JWTEvents@invalid'
        ],
        'tymon.jwt.expired' => [
            'App\Events\JWTEvents@expired'
        ],
        'tymon.jwt.absent' => [
            'App\Events\JWTEvents@missing'
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
