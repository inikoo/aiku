<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 04 Oct 2020 00:37:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Stores;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class ProductStock extends Pivot {
    use UsesTenantConnection;

    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
    ];



}
