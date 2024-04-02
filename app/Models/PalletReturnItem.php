<?php

namespace App\Models;

use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PalletReturnItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'state'              => PalletReturnItemStateEnum::class,
        'in_process_at'      => 'datetime',
        'submitted_at'       => 'datetime',
        'confirmed_at'       => 'datetime',
        'picking_at'         => 'datetime',
        'picked_at'          => 'datetime',
        'not_picked_at'      => 'datetime',
        'dispatched_at'      => 'datetime',
        'cancel_at'          => 'datetime',
    ];

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class);
    }

    public function palletReturn(): BelongsTo
    {
        return $this->belongsTo(PalletReturn::class);
    }
}
