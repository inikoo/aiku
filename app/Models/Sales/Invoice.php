<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;



class Invoice extends Model {
    use UsesTenantConnection;

        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];

    public function order() {
        return $this->belongsTo('App\Models\Sales\Order');
    }

    public function categories() {
        return $this->morphToMany('App\Models\Utils\Category', 'categoriable');
    }

}
