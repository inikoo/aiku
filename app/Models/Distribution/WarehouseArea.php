<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 19:24:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Distribution;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Distribution\WarehouseArea
 *
 * @property int $id
 * @property string $created_at
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class WarehouseArea extends Model implements Auditable{
    use UsesTenantConnection,Sluggable;
    use \OwenIt\Auditing\Auditable;

        protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function sluggable() {
        return [
            'slug' => [
                'source'   => 'name',
                'onUpdate' => true
            ]
        ];
    }

    protected $guarded = [];

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Distribution\Warehouse');
    }

    public function locations()
    {
        return $this->hasMany('App\Models\Distribution\Location');
    }

}
