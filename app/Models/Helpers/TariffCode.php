<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 16:28:55 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class TariffCode extends Model
{
    use UsesLandlordConnection;

    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TariffCode::class, 'parent_id');
    }
}
