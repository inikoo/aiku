<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 01 Sep 2020 12:43:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class AccessCode extends Model {
    use UsesLandLordConnection;

    protected $casts = [
        'payload' => 'array',
    ];

    protected $attributes = [
        'payload' => '{}'
    ];

}
