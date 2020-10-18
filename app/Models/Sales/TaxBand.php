<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 19 Oct 2020 00:06:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 *
 * @property int    $id
 * @property string $name
 * @property string $created_at
 * @property int    $legacy_id
 * @property array  $data
 *
 * @mixin \Illuminate\Database\Eloquent\Model:class
 * @mixin \Illuminate\Database\Eloquent\Builder:class
 */
class TaxBand extends Model {
    use UsesTenantConnection;

    protected $casts = [
        'data' => 'array'
    ];
    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


}
