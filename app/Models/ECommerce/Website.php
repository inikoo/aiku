<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 08 Oct 2020 14:09:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\ECommerce;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Ecommerce\Website
 *
 * @property int $id
 * @property string $created_at
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Website extends Model implements Auditable{
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

    function sluggable() {
        return [
            'slug' => [
                'source'   => 'sluggledCode',
                'onUpdate' => true
            ]
        ];
    }

    public function getSluggledCodeAttribute() {
        return $this->store->slug;

    }

    public function store() {
        return $this->belongsTo('App\Models\Stores\Store')->withTrashed();
    }

    public function webpages() {
        return $this->hasMany('App\Models\ECommerce\Webpage');
    }


    public function web_users() {
        return $this->hasMany('App\Models\ECommerce\WebUser');
    }


}
