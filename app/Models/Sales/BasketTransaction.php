<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 04 Oct 2020 10:28:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 *
 * @property int    $id

 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class BasketTransaction extends Pivot {
    use UsesTenantConnection;

    protected $table = 'basket_transactions';

    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function basketable() {
        return $this->morphTo();
    }

    public function products() {
        return $this->morphedByMany('App\Models\Store\Product', 'transaction','basket_transactions');

    }

}
