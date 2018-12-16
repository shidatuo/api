<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoginStoreSession
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        //>监听登陆事件
        $sessionData = [
            "user" => [
                "id"        => $event->user->id,
                "name"      => $event->user->nickName,
                "avatar"    => NotEstr($event->user->avatarUrl) ? $event->user->avatarUrl : asset('/favicon.ico'),
                "email"     => $event->user->email,
                "is_admin"  => 1,
            ]
        ];
        //>写入session
        session($sessionData);
    }
}
