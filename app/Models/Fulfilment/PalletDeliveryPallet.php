<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 30 Jan 2024 16:47:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Fulfilment\PalletDeliveryPallet
 *
 * @property int $id
 * @property int $pallet_delivery_id
 * @property int $pallet_id
 * @property int $items_quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\Pallet $pallet
 * @property-read \App\Models\Fulfilment\PalletDelivery $palletDelivery
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryPallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryPallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDeliveryPallet query()
 * @mixin \Eloquent
 */
class PalletDeliveryPallet extends Model
{
    protected $guarded = [];

    public function palletDelivery(): BelongsTo
    {
        return $this->belongsTo(PalletDelivery::class);
    }

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class);
    }
}
