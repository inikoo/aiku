<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 12 Oct 2020 23:03:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 *
 * @property int $id
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 *
 */
class OriginalImage extends Model {
    use UsesLandLordConnection;

    protected $table = 'original_images';

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}'
    ];
    protected $guarded =[];

    public function communal_image()
    {
        return $this->morphOne('App\Models\Helpers\CommunalImage', 'imageable');
    }

}
