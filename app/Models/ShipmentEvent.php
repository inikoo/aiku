<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 23:04:35 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Dispatch\Shipment;
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
 * @property-read Shipment $shipment
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
