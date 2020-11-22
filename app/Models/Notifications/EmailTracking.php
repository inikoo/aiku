<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Sat, 21 Nov 2020 23:27:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 *
 * @property int    $id
 * @property string $created_at
 * @property array  $data
 * @property array  $settings
 *
 */
class EmailTracking extends Model {
    use UsesTenantConnection;

    protected $casts = [
        'data'     => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
    ];


    protected $guarded = [];




}
