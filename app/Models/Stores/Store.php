<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\Stores;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Stores\Store
 *
 * @property int $id
 * @property string $created_at
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Store extends Model implements Auditable{
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

        protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}'
    ];

    protected $guarded=[];


    public function sluggable() {
        return [
            'slug' => [
                'source'   => 'slug',
                'onUpdate' => true
            ]
        ];
    }

    public function prospects()
    {
        return $this->hasMany('App\Models\CRM\Prospect');
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\Sales\Invoice');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Sales\Order');
    }

    public function customers()
    {
        return $this->hasMany('App\Models\CRM\Customer');
    }

    public function websites()
    {
        return $this->hasOne('App\Models\ECommerce\Website');
    }

    public function charges()
    {
        return $this->hasMany('App\Models\Sales\Charge');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Stores\Product');
    }

    public function store_aggregation()
    {
        return $this->hasOne('App\Models\Stores\StoreAggregation');
    }


}
