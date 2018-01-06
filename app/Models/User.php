<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;//消息通知相关功能引用
use Illuminate\Foundation\Auth\User as Authenticatable; //授权相关功能引用！

class User extends Authenticatable
{
    use Notifiable;

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
}