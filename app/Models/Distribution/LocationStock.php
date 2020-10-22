<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 22 Oct 2020 14:36:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Distribution;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 *
 * @property int $id
 * @property string $created_at
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class LocationStock extends Model{
    use UsesTenantConnection;

    protected $table = 'location_stock';


    protected $casts = [
        'data'     => 'array',
        'settings'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}',
    ];


    protected $guarded = [];

    public function stock() {
        return $this->belongsTo('App\Models\Distribution\Stock');
    }

    public function location() {
        return $this->belongsTo('App\Models\Distribution\Location');
    }




}
