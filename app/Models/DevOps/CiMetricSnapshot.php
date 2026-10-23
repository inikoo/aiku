<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 10:52:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\DevOps;

use Illuminate\Database\Eloquent\Model;

class CiMetricSnapshot extends Model
{
    public const string COVERAGE = 'coverage';
    public const string BUNDLE_SIZE = 'bundle_size';

    protected $guarded = [];

    protected $attributes = [
        'summary'   => '{}',
        'breakdown' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'summary'     => 'array',
            'breakdown'   => 'array',
            'measured_at' => 'datetime',
        ];
    }
}
