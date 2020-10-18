<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 04 Oct 2020 02:53:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Stores;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Stores\ProductHistoricVariation
 *
 * @property int    $id
 * @property string $created_at
 * @property int $legacy_id
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class ProductHistoricVariation extends Model {
    use UsesTenantConnection;

    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded = [];

    public function product() {
        return $this->belongsTo('App\Models\Stores\Product');
    }

    public function orderTransactions() {
        return $this->morphMany('App\Models\Sales\OrderTransaction', 'transaction',


        );
    }


}
