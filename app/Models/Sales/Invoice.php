<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\Sales;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;



class Invoice extends Model {
    use UsesTenantConnection,Sluggable;

        protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded=[];

    function sluggable() {
        return [
            'slug' => [
                'source'   => 'storeNumber',
                'onUpdate' => true
            ]
        ];
    }

    function getStoreNumberAttribute() {
        return $this->customer->store->code.'-'.$this->number;
    }

    public function customer() {
        return $this->belongsTo('App\Models\CRM\Customer');
    }

    public function categories() {
        return $this->morphToMany('App\Models\Utils\Category', 'categoriable');
    }

    public function addresses() {
        return $this->morphToMany('App\Models\Helpers\Address', 'addressable')->withTimestamps()->withPivot(['scope']);
    }

    public function orders() {
        return $this->belongsToMany('App\Models\Sales\Order')->withTimestamps();
    }

}
