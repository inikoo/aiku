<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 03 Oct 2020 00:58:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Distribution;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Distribution\Warehouse
 *
 * @property int    $id
 * @property string $created_at
 * @property string $deleted_at
 * @property int    $legacy_id
 * @property array  $data
 * @property array  $settings

 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Stock extends Model implements Auditable {
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

    function sluggable() {
        return [
            'slug' => [
                'source'   => 'code',
                'onUpdate' => true
            ]
        ];
    }

    protected $guarded = [];


    public function locations() {
        return $this->belongsToMany('App\Models\Distribution\Location')->using('App\Models\Distribution\LocationStockPivot')->withTimestamps()->withPivot('picking_priority', 'quantity', 'data');
    }

    public function products() {
        return $this->belongsToMany('App\Models\Stores\Product')->using('App\Models\Stores\ProductStock')->withTimestamps()->withPivot('ratio');
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
