<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:36:40 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Timezone extends Model
{
    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function formatOffset(): string
    {
        $hours     = $this->offset / 3600;
        $remainder = $this->offset % 3600;
        $sign      = $hours > 0 ? '+' : '-';
        $hour      = (int) abs($hours);
        $minutes   = (int) abs($remainder / 60);

        if ($hour == 0 and $minutes == 0) {
            $sign = ' ';
        }

        return 'GMT'.$sign.str_pad($hour, 2, '0', STR_PAD_LEFT).':'.str_pad($minutes, 2, '0');
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class);
    }
}
