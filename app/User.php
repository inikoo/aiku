<?php
/*
Author: Raul Perusquía (raul@inikoo.com)
Created:  Mon Jul 27 2020 16:24:18 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\User
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $userable
 */
class User extends Authenticatable
{
    use HasApiTokens,Notifiable,UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    public function userable()
    {
        return $this->morphTo();
    }

}
