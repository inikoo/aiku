<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:36:40 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $casts = [
        'data'               => 'array',
        'historic_data_since'=> 'datetime'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded=[];

    public function exchanges(): HasMany
    {
        return $this->hasMany(CurrencyExchange::class);
    }
}
