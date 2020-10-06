<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 05 Oct 2020 14:53:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
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
    public function order()
    {
        return $this->belongsTo('App\Models\Sales\Order');
    }


    public function pickings() {
        return $this->belongsToMany('App\Models\Distribution\Stock', 'pickings')->using('App\Models\Distribution\Picking')->withTimestamps()->withPivot(['quantity','legacy_id']);
    }

    public function items() {
        return $this->belongsToMany('App\Models\Distribution\Stock', 'delivery_note_items')->using('App\Models\Distribution\DeliveryNoteItem')->withTimestamps()->withPivot(['quantity','legacy_id']);
    }
}
