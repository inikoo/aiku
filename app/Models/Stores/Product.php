<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 03 Oct 2020 23:09:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Stores;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Stores\Product
 *
 * @property int    $id
 * @property string $created_at
 * @property int    $legacy_id
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Product extends Model implements Auditable {
    use UsesTenantConnection, Sluggable;
    use \OwenIt\Auditing\Auditable;

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}'
    ];

    function sluggable() {
        return [
            'slug' => [
                'source'   => 'storeCode',
                'onUpdate' => true
            ]
        ];
    }

    function getStoreCodeAttribute() {
        return $this->code.'-'.$this->store->code;
    }


    protected $guarded = [];

    public function store() {
        return $this->belongsTo('App\Models\Stores\Store')->withTrashed();
    }

    public function stocks() {
        return $this->belongsToMany('App\Models\Distribution\Stock')->using('App\Models\Stores\ProductStock')->withTimestamps()->withPivot('ratio');
    }

    public function basketTransactions() {
        return $this->morphMany('App\Models\Sales\BasketTransaction', 'transaction');
    }

    public function images() {
        return $this->morphMany('App\Models\Helpers\ImageModel', 'image_models', 'imageable_type', 'imageable_id');
    }

    public function attachments() {
        return $this->morphMany('App\Models\Helpers\AttachmentModel', 'attachment_models', 'attachmentable_type', 'attachmentable_id');
    }
    public function categories() {
        return $this->morphToMany('App\Models\Utils\Category', 'categoriable');
    }

}
