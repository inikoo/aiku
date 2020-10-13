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
class CommunalImage extends Pivot {
    use UsesLandLordConnection;

    protected $table = 'communal_images';


    protected $guarded =[];

    public function imageable()
    {
        return $this->morphTo();
    }

}
