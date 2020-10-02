<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 02 Oct 2020 14:25:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Sales\Order
 *
 * @property int $id
 * @property string $created_at
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Order extends Model implements Auditable{
    use UsesTenantConnection;
    use \OwenIt\Auditing\Auditable;

        protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded=[];

    public function store()
    {
        return $this->belongsTo('App\Models\Stores\Store');
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\Sales\Invoice');
    }
    public function delivery_notes()
    {
        return $this->hasMany('App\Models\Distribution\DeliveryNote');
    }
}
