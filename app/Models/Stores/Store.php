<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\Stores;

use App\Models\Sales\Adjust;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Stores\Store
 *
 * @property int $id
 * @property string $created_at
 * @property array $data
 * @property array $settings
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


    function sluggable() {
        return [
            'slug' => [
                'source'   => 'code',
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


    public function charges() {
        return $this->hasMany('App\Models\Sales\Charge');
    }


    /**
     * @return HasMany|Collection|Adjust[]
     */
    public function adjusts() {
        return $this->hasMany('App\Models\Sales\Adjust');
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
