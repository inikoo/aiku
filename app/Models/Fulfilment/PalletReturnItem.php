<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Apr 2024 14:12:20 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PalletReturnItem
 *
 * @property int $id
 * @property int $pallet_id
 * @property int $pallet_return_id
 * @property int|null $picked_from_location_id
 * @property PalletReturnItemStateEnum $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\Pallet $pallet
 * @property-read \App\Models\Fulfilment\PalletReturn $palletReturn
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturnItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturnItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturnItem query()
 * @mixin \Eloquent
 */
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
