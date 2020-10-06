<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 05 Oct 2020 00:52:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Transaction extends Pivot {
    use UsesTenantConnection;

    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'settings' => '{}'
    ];
}
