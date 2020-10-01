<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\Distribution;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Distribution\DeliveryNote
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class

 */
class DeliveryNote extends Model {
    use UsesTenantConnection;

        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];

    public function store()
    {
        return $this->belongsTo('App\Models\Stores\Store');
    }
    public function order()
    {
        return $this->belongsTo('App\Models\Sales\Order');
    }
}
