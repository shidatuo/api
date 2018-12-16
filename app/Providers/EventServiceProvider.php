<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        //>生成 laravel 监听器
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        //>生成 后台用户登陆的监听器
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LoginStoreSession'
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
