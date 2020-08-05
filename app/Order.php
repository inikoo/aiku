<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class Order extends Model
{
    use UsesTenantConnection;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    public function products()
    {
        return $this->hasMany('App\Product');
    }


    public function deliverynotes()
    {
        return $this->hasMany('App\DeliveryNote');
    }
    
}
