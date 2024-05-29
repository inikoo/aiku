<?php

namespace App\Models;

use App\Models\Catalogue\Service;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
