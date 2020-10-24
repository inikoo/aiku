<?php
/*
Author: Raul Perusquía (raul@inikoo.com)
Created:  Mon Jul 27 2020 16:24:18 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App;

use App\Models\Helpers\AccessCode;
use Cviebrock\EloquentSluggable\Sluggable;
use Exception;
use OwenIt\Auditing\Contracts\Auditable;

use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\User
 *
 * @property int $id
 * @property string $handle
 * @property array $data
 * @property array $confidential

 * @property string $password
 * @property string $pin
 * @property string $last_login_at

 * @property \App\User $user


 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class User extends Authenticatable implements Auditable {
    use HasApiTokens, Notifiable, UsesTenantConnection, Sluggable;
    use HasRoles;
    use \OwenIt\Auditing\Auditable;


    protected $casts = [
        'settings'     => 'array',
        'data'         => 'array',
        'confidential' => 'array'

    ];


    protected $auditInclude = [
        'handle'
    ];

    protected $guarded = [];


    protected $attributes = [
        'data'         => '{}',
        'settings'     => '{}',
        'confidential' => '{}'
    ];

    protected $hidden = [
        'password',
        'pin',
        'confidential'
    ];

    public function sluggable() {
        return [
            'handle' => [
                'source' => 'userable.slug'
            ]
        ];
    }

    public function userable() {
        return $this->morphTo();
    }

    public function devices() {
        return $this->hasMany('App\Models\System\Device');
    }

    public function createAccessCode() {


        $tenant = app('currentTenant');

        $accessCode            = new AccessCode;
        $accessCode->code      = sprintf("%06d", rand(1, 999999));
        $accessCode->tenant_id = $tenant->id;
        $accessCode->scope     = 'Tenant';
        $accessCode->scope_id  = $tenant->id;

        $accessCode->expired_at = gmdate('Y-m-d H:i:s', strtotime('now +300 seconds'));

        $accessCode->payload = [
            'user_id' => $this->id
        ];

        try {
            $accessCode->save();

            return $accessCode;
        } catch (Exception $exception) {
            return $this->createAccessCode();
        }


    }

}
