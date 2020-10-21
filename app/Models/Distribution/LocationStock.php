<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 22 Oct 2020 00:59:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Distribution;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class LocationStock extends Pivot {
    use UsesTenantConnection;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array'

    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];
}
