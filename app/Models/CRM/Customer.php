<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\CRM;

use App\Models\Sales\Basket;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\CRM\Customer
 *
 * @property int    $id
 * @property string $deleted_at
 * @property string state registered,new,active,losing,lost,deleted
 * @property array $data
 * @property array $settings
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Customer extends Model implements Auditable {
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    protected $guarded = [];

    public function sluggable() {
        return [
            'slug' => [
                'source'   => 'sluggledName',
                'onUpdate' => true
            ]
        ];
    }
    protected static function booted() {
        static::created(
            function ($customer) {
                if(!Arr::exists($customer->data, 'dropshipping')){
                    $basket=new Basket;
                    $basket->tenant_id=$customer->tenant_id;
                    $customer->basket()->save($basket);
                }


            }
        );
    }

    public function getSluggledNameAttribute() {

        $sluggableName=au_escape_slug($this->name);

        if ($sluggableName == '') {
            $sluggableName = 'empty';
        }

        return $sluggableName;
    }


    public function basket() {
        return $this->morphOne('App\Models\Sales\Basket', 'parent');
    }

    public function store() {
        return $this->belongsTo('App\Models\Stores\Store');
    }

    public function billingAddress()
    {
        return $this->belongsTo('App\Models\Helpers\Address');
    }

    public function deliveryAddress()
    {
        return $this->belongsTo('App\Models\Helpers\Address');
    }

    public function addresses() {
        return $this->morphToMany('App\Models\Helpers\Address', 'addressable')->withTimestamps();
    }



    public function basketItems() {
        return $this->belongsToMany('App\Models\Stores\Product', 'basket_transactions')->using('App\Models\Sales\BasketTransaction')->withTimestamps()->withPivot(['quantity']);
    }

    public function images() {
        return $this->morphMany('App\Models\Helpers\ImageModel', 'image_models', 'imageable_type', 'imageable_id');
    }



}
