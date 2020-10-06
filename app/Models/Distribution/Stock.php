<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 03 Oct 2020 00:58:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Distribution;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Distribution\Warehouse
 *
 * @property int $id
 * @property string $created_at
 * @property string $deleted_at
 * @property int $legacy_id
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Stock extends Model implements Auditable{
    use UsesTenantConnection;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];

    protected $guarded = [];



    public function locations() {
        return $this->belongsToMany('App\Models\Distribution\Location')->withTimestamps()->withPivot('quantity');
    }

    public function products() {
        return $this->belongsToMany('App\Models\Stores\Product')->using('App\Models\Stores\ProductStock')->withTimestamps()->withPivot('ratio');
    }
}
