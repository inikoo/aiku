<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:36:40 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TariffCode extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TariffCode::class, 'parent_id');
    }
}
