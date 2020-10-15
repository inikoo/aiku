<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 21 Aug 2020 21:39:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


namespace App\Models\System;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\System\Guest
 *
 * @property int       $id
 * @property int       $user_id
 * @property string    $name
 * @property \App\User $user
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Guest extends Model implements Auditable {
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $guarded = [];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    public function sluggable() {
        return [
            'slug' => [
                'source'   => 'name',
                'onUpdate' => true
            ]
        ];
    }


    public function user() {
        return $this->belongsTo('App\User', 'user_id');

    }

    protected static function booted() {
        static::created(
            function ($guess) {


                if (!$guess->legacy_id) {
                    $guess->user()->create(
                        [
                            'handle'        => Str::slug($guess->name),
                            'tenant_id'     => $guess->tenant_id,
                            'userable_type' => 'Guess',
                            'userable_id'   => $guess->id,
                            'password'      => (config('env') == 'devel' ? Hash::make('password') : Hash::make(Str::random(40))),
                            'pin'           => (config('env') == 'devel' ? Hash::make('1234') : Hash::make(Str::random(6))),
                            'status'        => ($guess->status == 'working' ? 'active' : 'suspended'),

                            'settings' => [],
                            'data'     => []

                        ]

                    );
                }


            }
        );
    }

    public function image()
    {
        return $this->morphOne('App\Models\Helpers\ImageModel', 'image_models','imageable_type','imageable_id');
    }


}
