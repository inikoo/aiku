<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 21 Oct 2024 23:50:09 Central Indonesia Time, Office, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Dispatching;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $shipper_id
 * @property int $number_delivery_notes
 * @property int $number_customers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Dispatching\Shipper $shipper
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipperStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipperStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShipperStats query()
 * @mixin \Eloquent
 */
class ShipperStats extends Model
{
    protected $guarded = [];

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(Shipper::class);
    }

}