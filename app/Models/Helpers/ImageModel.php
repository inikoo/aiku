<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 13 Oct 2020 04:36:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 *
 * @property int $id
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 *
 */
class ImageModel extends Pivot {
    use UsesTenantConnection;

    protected $table = 'image_models';

    protected $casts = [
        'data'     => 'array'
    ];


    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded =[];

    public function image()
    {
        return $this->belongsTo('App\Models\Helpers\Image');
    }

    public function model()
    {
        return $this->morphTo(__FUNCTION__, 'imageable_type', 'imageable_id');
    }

}
