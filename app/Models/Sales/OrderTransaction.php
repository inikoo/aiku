<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 05 Oct 2020 00:52:00 Malaysia Time, Kuala Lumpur, Malaysia
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
class OrderTransaction extends Pivot {
    use UsesTenantConnection;

    protected $table = 'order_transactions';

    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}'
    ];

    public function productHistoricVariations()
    {
        return $this->morphedByMany('App\Models\Store\ProductHistoricVariation', 'orderable','order_transactions');

    }
}
