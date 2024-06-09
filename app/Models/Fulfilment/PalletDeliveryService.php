<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:39:14 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Models\Catalogue\Service;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @property int $id
 * @property int $pallet_delivery_id
 * @property int $service_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\PalletDelivery|null $palletDelivery
 * @property-read Service|null $service
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryService query()
 * @mixin \Eloquent
 */
class PalletDeliveryService extends Pivot
{
    protected $table = 'pallet_delivery_services';

    public function palletDelivery(): BelongsTo
    {
        return $this->belongsTo(PalletDelivery::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
