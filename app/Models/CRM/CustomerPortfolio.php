<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 15 Oct 2020 01:02:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomerPortfolio extends Pivot {
    use UsesTenantConnection;
    use SoftDeletes;

    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];


}
