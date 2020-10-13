<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 13 Oct 2020 02:29:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;


/**
 *
 * @property int $id
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 *
 */
class ProcessedImage extends Pivot {
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
