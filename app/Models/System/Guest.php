<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 21 Aug 2020 21:39:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


namespace App\Models\System;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\System\Guest
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


    public function image()
    {
        return $this->morphOne('App\User', 'userable');
    }



}
