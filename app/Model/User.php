<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    public $timestamps = true;

    public $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    /**
     * @param bool $api_key
     * @return array|bool
     * @author shidatuo
     * @description
     */
    public static function api_login($api_key = false){
        if ($api_key == false)
            return false;
        if (!NotEstr($api_key))
            return false;
        $u = User::query()->where("openid",$api_key)->first();
        if(is_null($u))
            return false;
        return $u->toArray();
    }
    /**
     * @param bool $api_key
     * @return array|bool
     * @author shidatuo
     * @description
     */
    public static function getuid($api_key = false){
        if ($api_key == false)
            return false;
        if (!NotEstr($api_key))
            return false;
        $u = User::query()->where("openid",$api_key)->pluck('id');
        if(is_null($u) || !count($u))
            return false;
        return $u->toArray()[0];
    }
}
