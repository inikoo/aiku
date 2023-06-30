<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class SimpleNotification extends Model
{
    use UsesTenantConnection;

    protected $casts = [
        'data'            => 'array',

    ];

    protected $attributes = [
        'data'            => '{}',
    ];

    protected $guarded = [];
}
