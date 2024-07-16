<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jul 2024 20:42:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingStats extends Model
{
    protected $table = 'shipping_stats';

    protected $guarded = [];

    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

}
