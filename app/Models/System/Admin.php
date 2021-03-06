<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 18 Aug 2020 13:56:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Admin extends Model {
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    public function user() {
        return $this->morphOne('App\User', 'userable');
    }

    protected static function booted() {
        static::created(
            function ($admin) {


                $admin->user()->create(
                    [
                        'handle'    => Str::slug($admin->slug),
                        'tenant_id' => $admin->tenant_id,
                        'password'  => (config('app.env') == 'devel' ? Hash::make('password') : Hash::make(Str::random(40))),
                        'pin'       => (config('app.env') == 'devel' ? Hash::make('1234') : Hash::make(Str::random(6))),
                        'legacy_id' => $admin->legacy_id,
                        'settings'  => [],
                        'data'      => []

                    ]

                );

            }
        );
    }

    public function createDirectAccessCode($device_name) {

        //$tokenData=preg_split('/\|/',$this->user->createToken($device_name)->plainTextToken);
        //return $tokenData[1];

        return $this->user->createToken($device_name)->plainTextToken;
    }


}
