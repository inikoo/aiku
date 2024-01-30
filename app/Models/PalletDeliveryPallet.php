<?php

namespace App\Models;

use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
