<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 17 Oct 2020 19:18:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Sales;

use App\Models\Traits\OrderTotals;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Sales\Basket
 *
 * @property int    $id
 * @property int    $items
 * @property float  $items_discounts
 * @property float  $net
 * @property float  $tax
 * @property boolean  $status
 * @property string $created_at
 * @property string $legacy_id
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Basket extends Model {
    use UsesTenantConnection,OrderTotals;

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


    public function parent() {
        return $this->morphTo();
    }

    public function transactions() {
        return $this->hasMany('App\Models\Sales\BasketTransaction');
    }


}
