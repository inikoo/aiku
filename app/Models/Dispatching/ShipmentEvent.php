<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Apr 2024 14:13:46 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Dispatching;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ShipmentEvent
 *
 * @property int $id
 * @property string $date
 * @property int $shipment_id
 * @property string|null $box
 * @property string|null $code
 * @property int|null $status
 * @property int|null $state
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Dispatching\Shipment $shipment
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipmentEvent query()
 * @mixin \Eloquent
 */
class ShipmentEvent extends Model
{
    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
