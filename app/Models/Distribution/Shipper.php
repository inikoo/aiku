<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 12 Oct 2020 14:28:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Distribution;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Distribution\Warehouse
 *
 * @property int $id
 * @property string $created_at
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Shipper extends Model implements Auditable{
    use UsesTenantConnection,Sluggable;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    public function sluggable() {
        return [
            'slug' => [
                'source'   => 'code',
                'onUpdate' => true
            ]
        ];
    }

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];

    protected $guarded = [];

    public function delivery_notes() {
        return $this->hasMany('App\Models\Distribution\DeliveryNote');
    }


}
