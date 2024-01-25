<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:24:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\CRM\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PalletDelivery extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'state'  => PalletDeliveryStateEnum::class,
        'in_at'  => 'datetime',
        'out_at' => 'datetime',
        'data'   => 'array'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(Pallet::class);
    }
}
