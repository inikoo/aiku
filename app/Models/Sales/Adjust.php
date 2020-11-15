<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sun, 15 Nov 2020 15:39:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Stores\Store
 *
 * @property int $id
 * @property string $created_at
 * @property array $data
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class Adjust extends Model {
    use UsesTenantConnection;

    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


}
