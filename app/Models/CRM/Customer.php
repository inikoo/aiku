<?php
/*
Copyright (c) 2020, AIku.io

Version 4
*/

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        return $this->morphToMany('App\Models\Helpers\Address', 'addressable');
    }

    public function getSluggledNameAttribute() {
        $sluggableName = preg_replace('/\'/', '', $this->name);

        $sluggableName = preg_replace('/www\./', 'www ', $sluggableName);
        $sluggableName = preg_replace('/\.com/', ' com', $sluggableName);
        $sluggableName = preg_replace('/&/', ' and ', $sluggableName);

        $sluggableName = preg_replace('/\./', '', $sluggableName);

        $sluggableName = preg_replace('/-/', ' ', $sluggableName);
        $sluggableName = trim($sluggableName);
        if ($sluggableName == '') {
            $sluggableName = 'empty';
        }

        return $sluggableName;
    }

    public function basketItems() {
        return $this->belongsToMany('App\Models\Stores\Product', 'basket_items')->using('App\Models\Sales\BasketItem')->withTimestamps()->withPivot(['quantity']);
    }

}
